<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantConfig extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'limits' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function featureEnabled(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    public function getLimit(string $key, int $default = PHP_INT_MAX): int
    {
        return (int) ($this->limits[$key] ?? $default);
    }
}
