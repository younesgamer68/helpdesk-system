<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAiSettings extends Model
{
    protected $table = 'company_ai_settings';

    protected $fillable = [
        'company_id',
        'ai_suggestions_enabled',
        'ai_summary_enabled',
        'ai_chatbot_enabled',
        'chatbot_greeting',
        'chatbot_fallback_threshold',
    ];

    protected function casts(): array
    {
        return [
            'ai_suggestions_enabled' => 'boolean',
            'ai_summary_enabled' => 'boolean',
            'ai_chatbot_enabled' => 'boolean',
            'chatbot_fallback_threshold' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
