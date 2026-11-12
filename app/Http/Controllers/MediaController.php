<?php

namespace App\Http\Controllers;

use App\CloudflareR2;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function __construct(public CloudflareR2 $r2) {}

    public function show(MediaAsset $asset): BinaryFileResponse|RedirectResponse
    {
        $r2Url = $this->r2->deliveryUrl($asset);
        if ($r2Url !== null) {
            return redirect()->away($r2Url, 302, ['Cache-Control' => 'private, max-age=300']);
        }

        if ($asset->provider === 'cloudflare' && str_starts_with($asset->cloudflare_url ?? '', 'https://imagedelivery.net/')) {
            return redirect()->away($asset->cloudflare_url);
        }
        abort_unless(Storage::disk('local')->exists($asset->path), 404);

        return response()->file(Storage::disk('local')->path($asset->path), [
            'Content-Type' => $asset->mime_type, 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
