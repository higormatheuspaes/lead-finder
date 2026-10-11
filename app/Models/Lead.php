<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\WebsiteType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'search_id', 'google_place_id', 'name', 'greeting_name', 'niche', 'phone_raw', 'phone_e164',
    'is_mobile', 'website', 'website_type', 'rating', 'ratings_count', 'address', 'primary_type',
    'business_status', 'maps_url', 'score', 'problem', 'status', 'discard_reason',
    'contacted_at', 'replied_at', 'notes',
])]
class Lead extends Model
{
    public function search(): BelongsTo
    {
        return $this->belongsTo(Search::class);
    }

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'website_type' => WebsiteType::class,
            'is_mobile' => 'boolean',
            'rating' => 'decimal:1',
            'contacted_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }
}
