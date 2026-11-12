<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['section', 'draft', 'published', 'revision', 'updated_by', 'published_at'])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return ['draft' => 'array', 'published' => 'array', 'published_at' => 'datetime'];
    }
}
