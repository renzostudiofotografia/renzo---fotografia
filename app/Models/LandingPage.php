<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'draft', 'published', 'revision', 'updated_by', 'published_at'])]
class LandingPage extends Model
{
    protected function casts(): array
    {
        return ['draft' => 'array', 'published' => 'array', 'revision' => 'integer', 'published_at' => 'datetime'];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(LandingLead::class);
    }
}
