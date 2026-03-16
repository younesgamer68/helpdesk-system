<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

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

    public function rules()
    {
        return $this->hasMany(SlaPolicyRule::class, 'policy_id');
    }
}
