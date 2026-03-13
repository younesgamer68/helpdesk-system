<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'email_verified_at',
        'google_id',
        'avatar',
        'role',
        'specialty_id',
        'is_available',
        'assigned_tickets_count',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_available' => 'boolean',
        'assigned_tickets_count' => 'integer',
    ];

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isPendingInvite(): bool
    {
        return is_null($this->password) && is_null($this->google_id);
    }

    public function isActive(): bool
    {
        return ! $this->isPendingInvite();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function specialty()
    {
        return $this->belongsTo(TicketCategory::class, 'specialty_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Scope a query to only include available operators.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to only include operators with a specific specialty.
     */
    public function scopeWithSpecialty($query, int $categoryId)
    {
        return $query->where('specialty_id', $categoryId);
    }

    /**
     * Scope a query to only include operators.
     */
    public function scopeOperators($query)
    {
        return $query->where('role', 'operator');
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            // Clear company cache when user is updated
            cache()->forget("company.{$user->company_id}.agents");
            cache()->forget("company.{$user->company_id}.categories");
        });

        static::deleted(function ($user) {
            // Clear company cache when user is deleted
            cache()->forget("company.{$user->company_id}.agents");
        });
    }
}
