<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['landing_page_id', 'submission_id', 'name', 'phone', 'email', 'event_date', 'message', 'consent_at', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'inquiry_details'])]
class LandingLead extends Model
{
    protected function casts(): array
    {
        return ['landing_page_id' => 'integer', 'event_date' => 'date', 'consent_at' => 'datetime', 'inquiry_details' => 'array'];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }
}
