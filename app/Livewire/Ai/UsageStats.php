<?php

namespace App\Livewire\Ai;

use App\Models\AiSuggestionLog;
use App\Models\ChatbotConversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app.sidebar')]
class UsageStats extends Component
{
    #[Computed]
    public function suggestionsGenerated(): int
    {
        return AiSuggestionLog::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('action', 'generate')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    #[Computed]
    public function suggestionsAccepted(): int
    {
        return AiSuggestionLog::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('action', 'use')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    #[Computed]
    public function suggestionsDismissed(): int
    {
        return AiSuggestionLog::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('action', 'dismiss')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    #[Computed]
    public function acceptanceRate(): float
    {
        $total = $this->suggestionsAccepted + $this->suggestionsDismissed;

        return $total > 0 ? round(($this->suggestionsAccepted / $total) * 100, 1) : 0;
    }

    #[Computed]
    public function chatbotConversationsThisMonth(): int
    {
        return ChatbotConversation::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    #[Computed]
    public function ticketsDeflected(): int
    {
        return ChatbotConversation::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('outcome', 'resolved')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    #[Computed]
    public function ticketsEscalated(): int
    {
        return ChatbotConversation::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('outcome', 'escalated')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    #[Computed]
    public function deflectionRate(): float
    {
        $total = $this->chatbotConversationsThisMonth;

        return $total > 0 ? round(($this->ticketsDeflected / $total) * 100, 1) : 0;
    }

    #[Computed]
    public function timeSavedMinutes(): int
    {
        // Estimate: each accepted suggestion saves ~3 min, each deflected ticket saves ~8 min
        return ($this->suggestionsAccepted * 3) + ($this->ticketsDeflected * 8);
    }

    public function render()
    {
        return view('livewire.ai.usage-stats');
    }
}
