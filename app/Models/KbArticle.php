<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KbArticle extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $slug = Str::slug($article->title);
                // Ensure uniqueness per company
                $originalSlug = $slug;
                $count = 1;

                while (static::where('company_id', $article->company_id)->where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }

                $article->slug = $slug;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function versions()
    {
        return $this->hasMany(KbArticleVersion::class)->latest('version_number');
    }
}
