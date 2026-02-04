<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function ticket(){
        return $this->hasMany(Ticket::class);
    }
    public function user(){
        return $this->hasMany(User::class, foreignKey:'company_id');
    }
}
