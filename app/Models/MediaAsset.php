<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'alt', 'provider', 'path', 'cloudflare_id', 'cloudflare_url', 'mime_type', 'size'])]
class MediaAsset extends Model
{
    protected function casts(): array
    {
        return ['size' => 'integer'];
    }
}
