<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $guarded = [];
}
