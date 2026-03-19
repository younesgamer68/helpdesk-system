<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')->withPivot('role')->withTimestamps();
    }

    public function leads()
    {
        return $this->members()->wherePivot('role', 'lead');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
