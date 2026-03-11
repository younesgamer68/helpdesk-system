<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, foreignKey: 'assigned_to');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, foreignKey: 'company_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, foreignKey: 'category_id');
    }

    public function getRouteKeyName()
    {
        return 'ticket_number';
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }
}
