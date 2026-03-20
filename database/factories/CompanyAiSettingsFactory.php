<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyAiSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyAiSettings>
 */
class CompanyAiSettingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'ai_suggestions_enabled' => false,
            'ai_summary_enabled' => false,
            'ai_chatbot_enabled' => false,
            'ai_auto_triage_enabled' => false,
            'ai_model' => 'gemini-2.5-flash',
            'chatbot_greeting' => null,
            'chatbot_fallback_threshold' => 0.5,
            'escalation_url_type' => 'standalone',
            'custom_escalation_url' => null,
        ];
    }
}
