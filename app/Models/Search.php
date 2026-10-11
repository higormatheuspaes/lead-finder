<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['query', 'city', 'status', 'pages_fetched', 'results_count', 'new_leads_count', 'error'])]
class Search extends Model
{
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
