<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }

    public function user()
    {
        return $this->hasMany(User::class, foreignKey: 'company_id');
    }

    public function categories()
    {
        return $this->hasMany(TicketCategory::class, foreignKey: 'company_id');
    }

    public function widgetSettings()
    {
        return $this->hasOne(WidgetSetting::class, foreignKey: 'company_id');
    }

    protected function casts(): array
    {
        return [
            'onboarding_completed_at' => 'datetime',
            'require_client_verification' => 'boolean',
        ];
    }
}
