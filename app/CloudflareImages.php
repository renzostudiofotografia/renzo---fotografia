<?php

namespace App;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CloudflareImages
{
    public function configured(): bool
    {
        return filled(config('services.cloudflare.account_id')) && filled(config('services.cloudflare.token'));
    }

    public function upload(MediaAsset $asset): void
    {
        if ($asset->provider === 'cloudflare') {
            return;
        }
        if (! $this->configured()) {
            throw ValidationException::withMessages(['image' => 'Configura Cloudflare antes de enviar fotografías.']);
        }

        $response = Http::withToken(config('services.cloudflare.token'))
            ->connectTimeout(10)->timeout(60)
            ->attach('file', Storage::disk('local')->get($asset->path), $asset->name)
            ->post('https://api.cloudflare.com/client/v4/accounts/'.config('services.cloudflare.account_id').'/images/v1');

        $url = $response->json('result.variants.0');
        if (! $response->successful() || ! $response->json('success') || ! is_string($url) || ! str_starts_with($url, 'https://imagedelivery.net/')) {
            throw ValidationException::withMessages(['image' => 'Cloudflare no pudo recibir la fotografía. La copia local sigue disponible; revisa tu conexión y los permisos del token.']);
        }

        $asset->update([
            'provider' => 'cloudflare',
            'cloudflare_id' => $response->json('result.id'),
            'cloudflare_url' => $url,
        ]);
    }
}
