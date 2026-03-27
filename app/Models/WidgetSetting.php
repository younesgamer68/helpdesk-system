<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WidgetSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'require_phone' => 'boolean',
        'show_category' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($widget) {
            if (! $widget->widget_key) {
                $widget->widget_key = self::generateUniqueKey();
            }
        });
    }

    public static function generateUniqueKey(): string
    {
        do {
            $key = Str::random(32);
        } while (self::where('widget_key', $key)->exists());

        return $key;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function defaultAssignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_assigned_to');
    }

    public function getWidgetUrlAttribute(): string
    {
        $domain = config('app.domain');
        $protocol = config('app.env') === 'local' ? 'http' : 'https';

        return "{$protocol}://{$this->company->slug}.{$domain}/widget/{$this->widget_key}";
    }

    public function getIframeCodeAttribute(): string
    {
        $url = $this->widget_url;

        return <<<HTML
            <iframe 
                src="{$url}" 
                width="100%" 
                height="700" 
                frameborder="0"
                style="border: none; border-radius: 8px;"
            >
            </iframe>
            HTML;
    }
}
