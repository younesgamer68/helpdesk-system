<?php

namespace App\Livewire\Ai;

use App\Models\ChatbotConversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app.sidebar')]
class ChatHistory extends Component
{
    use WithPagination;

    public string $outcomeFilter = '';

    public ?int $viewingId = null;

    public bool $showDetail = false;

    public function mount(): void
    {
        $this->markInactiveConversationsAsAbandoned();
    }

    private function markInactiveConversationsAsAbandoned(): void
    {
        ChatbotConversation::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('outcome', 'active')
            ->where('updated_at', '<=', now()->subMinutes(30))
            ->update(['outcome' => 'abandoned']);
    }

    #[Computed]
    public function conversations(): LengthAwarePaginator
    {
        return ChatbotConversation::query()
            ->where('company_id', Auth::user()->company_id)
            ->when($this->outcomeFilter, fn ($q) => $q->where('outcome', $this->outcomeFilter))
            ->latest()
            ->paginate(20);
    }

    public function updatedOutcomeFilter(): void
    {
        $this->markInactiveConversationsAsAbandoned();
        $this->resetPage();
        unset($this->conversations);
    }

    public function viewConversation(int $id): void
    {
        $this->viewingId = $id;
        $this->showDetail = true;
    }

    #[Computed]
    public function viewingConversation(): ?ChatbotConversation
    {
        if (! $this->viewingId) {
            return null;
        }

        return ChatbotConversation::query()
            ->where('company_id', Auth::user()->company_id)
            ->find($this->viewingId);
    }

    public function render()
    {
        return view('livewire.ai.chat-history');
    }
}
