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
        $selectedModel = $settings->ai_model ?? 'gemini-2.5-flash';

        if (! $this->isModelEnabled($selectedModel)) {
            $selectedModel = $this->firstEnabledModel() ?? 'gemini-2.5-flash';
        }

        $this->ai_model = $selectedModel;
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
        return collect($this->modelOptions())
            ->mapWithKeys(fn (array $option, string $model) => [$model => $option['label']])
            ->all();
    }

    public function modelOptions(): array
    {
        return [
            'gemini-2.5-flash' => [
                'label' => 'Gemini 2.5 Flash (Fast & cheap)',
                'provider' => 'gemini',
                'enabled' => $this->providerConfigured('gemini'),
            ],
            'gemini-2.5-pro' => [
                'label' => 'Gemini 2.5 Pro (Balanced)',
                'provider' => 'gemini',
                'enabled' => $this->providerConfigured('gemini'),
            ],
            'gpt-4o-mini' => [
                'label' => 'GPT-4o Mini (Fast & cheap)',
                'provider' => 'openai',
                'enabled' => $this->providerConfigured('openai'),
            ],
            'gpt-4o' => [
                'label' => 'GPT-4o (Powerful)',
                'provider' => 'openai',
                'enabled' => $this->providerConfigured('openai'),
            ],
            'claude-sonnet-4-20250514' => [
                'label' => 'Claude Sonnet 4 (Powerful)',
                'provider' => 'anthropic',
                'enabled' => $this->providerConfigured('anthropic'),
            ],
        ];
    }

    private function providerConfigured(string $provider): bool
    {
        $apiKey = (string) config("ai.providers.{$provider}.key", '');

        return trim($apiKey) !== '';
    }

    private function isModelEnabled(string $model): bool
    {
        $option = $this->modelOptions()[$model] ?? null;

        if (! $option) {
            return false;
        }

        return (bool) $option['enabled'];
    }

    private function firstEnabledModel(): ?string
    {
        foreach ($this->modelOptions() as $model => $option) {
            if ($option['enabled']) {
                return $model;
            }
        }

        return null;
    }

    private function getSettings(): CompanyAiSettings
    {
        return CompanyAiSettings::query()->firstOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'ai_suggestions_enabled' => false,
                'ai_summary_enabled' => false,
                'ai_chatbot_enabled' => false,
                'ai_model' => 'gemini-2.5-flash',
            ]
        );
    }

    public function render()
    {
        return view('livewire.settings.ai-copilot');
    }
}
