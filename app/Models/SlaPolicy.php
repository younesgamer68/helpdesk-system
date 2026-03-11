<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'low_minutes' => 'integer',
            'medium_minutes' => 'integer',
            'high_minutes' => 'integer',
            'urgent_minutes' => 'integer',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
