<?php

namespace App\Livewire\Settings;

use App\Models\CompanyAiSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AiCopilot extends Component
{
    public bool $ai_suggestions_enabled = false;

    public bool $ai_summary_enabled = false;

    public string $ai_model = 'gemini-2.5-flash';

    public function mount(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $settings = $this->getSettings();

        $this->ai_suggestions_enabled = $settings->ai_suggestions_enabled;
        $this->ai_summary_enabled = $settings->ai_summary_enabled;
        $this->ai_model = $settings->ai_model ?? 'gemini-2.5-flash';
    }

    public function save(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $this->validate([
            'ai_suggestions_enabled' => 'required|boolean',
            'ai_summary_enabled' => 'required|boolean',
            'ai_model' => 'required|string|in:gemini-2.5-flash,gemini-2.5-pro,gpt-4o-mini,gpt-4o,claude-sonnet-4-20250514',
        ]);

        $this->getSettings()->update([
            'ai_suggestions_enabled' => $this->ai_suggestions_enabled,
            'ai_summary_enabled' => $this->ai_summary_enabled,
            'ai_model' => $this->ai_model,
        ]);

        $this->dispatch('ai-copilot-updated');
    }

    public function availableModels(): array
    {
        return [
            'gemini-2.5-flash' => 'Gemini 2.5 Flash (Fast & cheap)',
            'gemini-2.5-pro' => 'Gemini 2.5 Pro (Balanced)',
            'gpt-4o-mini' => 'GPT-4o Mini (Fast & cheap)',
            'gpt-4o' => 'GPT-4o (Powerful)',
            'claude-sonnet-4-20250514' => 'Claude Sonnet 4 (Powerful)',
        ];
    }

    private function getSettings(): CompanyAiSettings
    {
        return CompanyAiSettings::query()->firstOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'ai_suggestions_enabled' => false,
                'ai_summary_enabled' => false,
                'ai_chatbot_enabled' => false,
                'ai_auto_triage_enabled' => false,
                'ai_model' => 'gemini-2.5-flash',
            ]
        );
    }

    public function render()
    {
        return view('livewire.settings.ai-copilot');
    }
}
