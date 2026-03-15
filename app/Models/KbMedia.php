<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

class KbMedia extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }
}
