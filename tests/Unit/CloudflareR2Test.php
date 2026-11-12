<?php

namespace Tests\Unit;

use App\CloudflareR2;
use App\Models\MediaAsset;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class CloudflareR2Test extends TestCase
{
    public function test_upload_signs_the_encoded_r2_path_and_original_binary_body(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08 12:34:56', 'UTC'));
        config(['services.cloudflare_r2' => [
            'account_id' => '0123456789abcdef0123456789abcdef',
            'bucket' => 'site-images',
            'access_key_id' => 'exampleKey',
            'secret_access_key' => 'exampleSecret',
            'public_url' => 'https://fotos.example.com/',
        ]]);

        $path = 'site-media/boda & café.jpg';
        $encodedPath = 'site-media/boda%20%26%20caf%C3%A9.jpg';
        $contents = 'test-photo-content';
        $host = '0123456789abcdef0123456789abcdef.r2.cloudflarestorage.com';
        $endpoint = 'https://'.$host.'/site-images/'.$encodedPath;
        Storage::fake('local')->put($path, $contents);
        Http::preventStrayRequests()->fake([$endpoint => Http::response('', 200)]);

        $asset = Mockery::mock(MediaAsset::class)->makePartial();
        $asset->forceFill(['provider' => 'local', 'path' => $path, 'mime_type' => 'image/jpeg']);
        $asset->shouldReceive('update')->once()->with([
            'provider' => 'r2',
            'cloudflare_id' => $path,
            'cloudflare_url' => 'https://fotos.example.com/'.$encodedPath,
        ])->andReturnTrue();

        app(CloudflareR2::class)->upload($asset);

        Http::assertSent(function (Request $request) use ($endpoint, $host, $contents): bool {
            $this->assertSame('PUT', $request->method());
            $this->assertSame($endpoint, $request->url());
            $this->assertSame($contents, $request->body());
            $this->assertSame([$host], $request->header('Host'));
            $this->assertSame(['image/jpeg'], $request->header('Content-Type'));
            $this->assertSame(['public, max-age=86400'], $request->header('Cache-Control'));
            $this->assertSame(['20261108T123456Z'], $request->header('X-Amz-Date'));
            $this->assertSame(['360e5b5e5d44e08ae664a9850e58f8c666357a7d5c27d247af770f68fa2b1424'], $request->header('X-Amz-Content-Sha256'));
            $this->assertSame([
                'AWS4-HMAC-SHA256 Credential=exampleKey/20261108/auto/s3/aws4_request, '
                .'SignedHeaders=cache-control;content-type;host;x-amz-content-sha256;x-amz-date, '
                .'Signature=94d9a8bb83188d04cec7476a8a443f192d58e8c1f3c797440c4dd0053a29aa96',
            ], $request->header('Authorization'));

            return true;
        });
        Http::assertSentCount(1);
        Storage::disk('local')->assertExists($path);
    }
}
