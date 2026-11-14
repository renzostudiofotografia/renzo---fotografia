<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'draft', 'published', 'revision', 'updated_by', 'published_at', 'previous_slugs'])]
class LandingPage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['draft' => 'array', 'published' => 'array', 'revision' => 'integer', 'published_at' => 'datetime', 'previous_slugs' => 'array'];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(LandingLead::class);
    }
}
