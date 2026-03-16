<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicyRule extends Model
{
    protected $guarded = [];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class);
    }
}
