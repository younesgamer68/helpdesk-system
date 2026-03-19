<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class CompanyMailSettings extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'smtp_port' => 'integer',
        ];
    }

    public function setSmtpPasswordAttribute(?string $value): void
    {
        $this->attributes['smtp_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getSmtpPasswordAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return null;
        }
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
