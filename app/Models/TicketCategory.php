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

    public function parent()
    {
        return $this->belongsTo(TicketCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TicketCategory::class, 'parent_id');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getAncestorIdsAttribute(): array
    {
        if ($this->parent_id) {
            return [$this->parent_id, $this->id];
        }

        return [$this->id];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function kbArticles()
    {
        return $this->hasMany(KbArticle::class, 'ticket_category_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'category_user');
    }
}
