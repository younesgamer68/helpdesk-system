<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Ai\Enums\Lab;

class CompanyAiSettings extends Model
{
    protected $table = 'company_ai_settings';

    protected $fillable = [
        'company_id',
        'ai_suggestions_enabled',
        'ai_summary_enabled',
        'ai_chatbot_enabled',
        'ai_auto_triage_enabled',
        'ai_model',
        'chatbot_greeting',
        'chatbot_fallback_threshold',
        'escalation_url_type',
        'custom_escalation_url',
    ];

    protected function casts(): array
    {
        return [
            'ai_suggestions_enabled' => 'boolean',
            'ai_summary_enabled' => 'boolean',
            'ai_chatbot_enabled' => 'boolean',
            'ai_auto_triage_enabled' => 'boolean',
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

    /**
     * Resolve the ai_model string to a Lab provider enum.
     */
    public function resolveProvider(): Lab
    {
        return match (true) {
            str_starts_with($this->ai_model ?? '', 'gpt-') => Lab::OpenAI,
            str_starts_with($this->ai_model ?? '', 'claude-') => Lab::Anthropic,
            default => Lab::Gemini,
        };
    }
}
