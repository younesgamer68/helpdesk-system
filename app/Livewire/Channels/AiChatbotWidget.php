<?php

namespace App\Livewire\Channels;

use App\Models\ChatbotFaq;
use App\Models\CompanyAiSettings;
use App\Models\WidgetSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AiChatbotWidget extends Component
{
    public bool $ai_chatbot_enabled = false;

    public string $chatbot_greeting = 'Hi! How can I help you today?';

    public int $chatbot_fallback_threshold = 2;

    public string $faqQuestion = '';

    public string $faqAnswer = '';

    public bool $showFaqModal = false;

    public ?int $editingFaqId = null;

    public ?string $copiedKey = null;

    protected CompanyAiSettings $settings;

    public function mount(): void
    {
        $this->settings = CompanyAiSettings::query()->firstOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'ai_chatbot_enabled' => false,
                'chatbot_greeting' => 'Hi! How can I help you today?',
                'chatbot_fallback_threshold' => 2,
            ]
        );

        $this->ai_chatbot_enabled = (bool) $this->settings->ai_chatbot_enabled;
        $this->chatbot_greeting = (string) ($this->settings->chatbot_greeting ?: 'Hi! How can I help you today?');
        $this->chatbot_fallback_threshold = (int) ($this->settings->chatbot_fallback_threshold ?: 2);
    }

    #[Computed]
    public function faqs(): Collection
    {
        return ChatbotFaq::query()
            ->where('company_id', Auth::user()->company_id)
            ->latest()
            ->get();
    }

    #[Computed]
    public function widgetSetting(): WidgetSetting
    {
        return WidgetSetting::query()->firstOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'theme_mode' => 'dark',
                'form_title' => 'Submit a Support Ticket',
                'welcome_message' => '',
                'success_message' => 'Thank you! Please check your email to verify your ticket.',
                'require_phone' => false,
                'show_category' => true,
                'default_status' => 'pending',
                'default_priority' => 'medium',
                'is_active' => true,
            ]
        );
    }

    #[Computed]
    public function chatbotUrl(): string
    {
        $protocol = config('app.env') === 'local' ? 'http' : 'https';
        $slug = Auth::user()->company->slug;
        $domain = config('app.domain');

        return "{$protocol}://{$slug}.{$domain}/chatbot-widget/{$this->widgetSetting->widget_key}";
    }

    public function saveSettings(): void
    {
        $validated = $this->validate([
            'ai_chatbot_enabled' => ['required', 'boolean'],
            'chatbot_greeting' => ['required', 'string', 'max:500'],
            'chatbot_fallback_threshold' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        CompanyAiSettings::query()
            ->where('company_id', Auth::user()->company_id)
            ->first()
            ?->update($validated);

        $this->dispatch('show-toast', message: 'Chatbot settings saved successfully!', type: 'success');
    }

    public function openAddFaq(): void
    {
        $this->editingFaqId = null;
        $this->faqQuestion = '';
        $this->faqAnswer = '';
        $this->showFaqModal = true;
    }

    public function openEditFaq(int $id): void
    {
        $faq = ChatbotFaq::query()
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $this->editingFaqId = $faq->id;
        $this->faqQuestion = $faq->question;
        $this->faqAnswer = $faq->answer;
        $this->showFaqModal = true;
    }

    public function saveFaq(): void
    {
        $validated = $this->validate([
            'faqQuestion' => ['required', 'string', 'max:255'],
            'faqAnswer' => ['required', 'string', 'max:5000'],
        ]);

        if ($this->editingFaqId) {
            ChatbotFaq::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->editingFaqId)
                ->update([
                    'question' => $validated['faqQuestion'],
                    'answer' => $validated['faqAnswer'],
                ]);

            $message = 'FAQ updated successfully!';
        } else {
            ChatbotFaq::query()->create([
                'company_id' => Auth::user()->company_id,
                'question' => $validated['faqQuestion'],
                'answer' => $validated['faqAnswer'],
            ]);

            $message = 'FAQ added successfully!';
        }

        $this->showFaqModal = false;
        $this->editingFaqId = null;
        $this->faqQuestion = '';
        $this->faqAnswer = '';

        $this->dispatch('show-toast', message: $message, type: 'success');
    }

    public function deleteFaq(int $id): void
    {
        ChatbotFaq::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->delete();

        $this->dispatch('show-toast', message: 'FAQ deleted successfully!', type: 'success');
    }

    public function copyToClipboard(string $text, string $key): void
    {
        $this->copiedKey = $key;

        $this->dispatch('copy-to-clipboard', text: $text);
        $this->dispatch('show-toast', message: 'Copied to clipboard!', type: 'success');
        $this->js('setTimeout(() => $wire.set("copiedKey", null), 2000)');
    }

    public function render()
    {
        return view('livewire.channels.ai-chatbot-widget');
    }
}
