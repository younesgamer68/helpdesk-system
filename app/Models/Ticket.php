<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];
    // In app/Models/Ticket.php

    // public function getRouteKeyName()
    // {
    //     return 'ticket_number';
    // }
    public function user()
    {
        return $this->belongsTo(User::class, foreignKey:'assigned_to');
    }
    public function company(){
        return $this->belongsTo(Company::class, foreignKey:'company_id');
    }
    public function category(){
        return $this->belongsTo(TicketCategory::class, foreignKey: 'category_id');
    }

}
