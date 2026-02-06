<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
    public function isAdmin(){
        return $this->role === 'admin';
    }
    public function isTech(){
        return $this->role === 'technician';
    }
    public function company(){
        return $this->belongsTo(Company::class);
    }
    public function tickets(){
        return $this->hasMany(Ticket::class , 'ticket_number');
    }
    // In App\Models\User.php

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