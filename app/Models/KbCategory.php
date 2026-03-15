<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KbCategory extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function articles()
    {
        return $this->hasMany(KbArticle::class);
    }

    public function parent()
    {
        return $this->belongsTo(KbCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(KbCategory::class, 'parent_id')->orderBy('order');
    }
}
