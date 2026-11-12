<?php

namespace App;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CloudflareR2
{
    public function configured(): bool
    {
        $accountId = config('services.cloudflare_r2.account_id');
        $bucket = config('services.cloudflare_r2.bucket');
        $accessKeyId = config('services.cloudflare_r2.access_key_id');
        $secretAccessKey = config('services.cloudflare_r2.secret_access_key');

        return is_string($accountId) && preg_match('/\A[a-f0-9]{32}\z/i', $accountId) === 1
            && is_string($bucket) && preg_match('/\A[a-z0-9][a-z0-9-]{1,61}[a-z0-9]\z/', $bucket) === 1
            && is_string($accessKeyId) && ctype_alnum($accessKeyId)
            && is_string($secretAccessKey) && trim($secretAccessKey) !== ''
            && $this->publicOrigin() !== null;
    }

    public function upload(MediaAsset $asset): void
    {
        if (in_array($asset->provider, ['r2', 'cloudflare'], true)) {
            return;
        }
        if (! $this->configured()) {
            throw ValidationException::withMessages(['image' => 'Configura Cloudflare R2 antes de enviar fotografías.']);
        }

        $segments = explode('/', $asset->path);
        if ($segments[0] !== 'site-media' || count($segments) < 2 || array_intersect(['', '.', '..'], $segments) !== []) {
            throw ValidationException::withMessages(['image' => 'La fotografía no tiene una ubicación válida en la biblioteca.']);
        }

        $contents = Storage::disk('local')->get($asset->path);
        if ($contents === null) {
            throw ValidationException::withMessages(['image' => 'No se encontró la fotografía guardada en el servidor.']);
        }

        $objectPath = '/'.implode('/', array_map('rawurlencode', $segments));
        $host = strtolower(config('services.cloudflare_r2.account_id')).'.r2.cloudflarestorage.com';
        $requestPath = '/'.config('services.cloudflare_r2.bucket').$objectPath;
        $headers = $this->signedHeaders($host, $requestPath, $asset->mime_type, $contents);

        $response = Http::connectTimeout(10)->timeout(60)->withoutRedirecting()
            ->withBody($contents, $asset->mime_type)->replaceHeaders($headers)
            ->put('https://'.$host.$requestPath);

        if (! $response->successful()) {
            throw ValidationException::withMessages(['image' => 'Cloudflare R2 no pudo recibir la fotografía. La copia local sigue disponible. Revisa la configuración y los permisos del bucket.']);
        }

        $asset->update([
            'provider' => 'r2',
            'cloudflare_id' => $asset->path,
            'cloudflare_url' => $this->publicOrigin().$objectPath,
        ]);
    }

    public function deliveryUrl(MediaAsset $asset): ?string
    {
        $url = $asset->cloudflare_url;
        if ($asset->provider !== 'r2' || ! $this->isHttpsUrl($url)) {
            return null;
        }

        $origin = $this->publicOrigin();
        if (! config('services.cloudflare_r2.optimize') || $origin === null || ! str_starts_with($url, $origin.'/')) {
            return $url;
        }

        $host = parse_url($origin, PHP_URL_HOST);
        if ($host === 'r2.dev' || str_ends_with($host, '.r2.dev')) {
            return $url;
        }

        return $origin.'/cdn-cgi/image/width=1920,fit=scale-down,format=auto,onerror=redirect'.substr($url, strlen($origin));
    }

    private function publicOrigin(): ?string
    {
        $url = config('services.cloudflare_r2.public_url');
        if (! $this->isHttpsUrl($url)) {
            return null;
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST));
        if (! in_array(parse_url($url, PHP_URL_PATH), [null, '/'], true) || parse_url($url, PHP_URL_PORT) !== null
            || $host === 'r2.cloudflarestorage.com' || str_ends_with($host, '.r2.cloudflarestorage.com')) {
            return null;
        }

        return 'https://'.$host;
    }

    private function isHttpsUrl(mixed $url): bool
    {
        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false
            && parse_url($url, PHP_URL_SCHEME) === 'https' && parse_url($url, PHP_URL_USER) === null
            && parse_url($url, PHP_URL_QUERY) === null && parse_url($url, PHP_URL_FRAGMENT) === null;
    }

    /** @return array<string, string> */
    private function signedHeaders(string $host, string $path, string $contentType, string $contents): array
    {
        $timestamp = now('UTC')->format('Ymd\THis\Z');
        $date = substr($timestamp, 0, 8);
        $payloadHash = hash('sha256', $contents);
        $headers = [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Type' => $contentType,
            'Host' => $host,
            'X-Amz-Content-Sha256' => $payloadHash,
            'X-Amz-Date' => $timestamp,
        ];
        ksort($headers, SORT_STRING);

        $canonicalHeaders = '';
        foreach ($headers as $name => $value) {
            $canonicalHeaders .= strtolower($name).':'.$value."\n";
        }
        $signedHeaders = strtolower(implode(';', array_keys($headers)));
        $canonicalRequest = implode("\n", ['PUT', $path, '', $canonicalHeaders, $signedHeaders, $payloadHash]);

        $scope = $date.'/auto/s3/aws4_request';
        $stringToSign = implode("\n", ['AWS4-HMAC-SHA256', $timestamp, $scope, hash('sha256', $canonicalRequest)]);
        $signingKey = 'AWS4'.config('services.cloudflare_r2.secret_access_key');
        foreach ([$date, 'auto', 's3', 'aws4_request'] as $part) {
            $signingKey = hash_hmac('sha256', $part, $signingKey, true);
        }
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $credential = config('services.cloudflare_r2.access_key_id').'/'.$scope;
        $headers['Authorization'] = "AWS4-HMAC-SHA256 Credential={$credential}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        return $headers;
    }
}
