<?php

namespace App\Livewire\Ai;

use App\Models\AiSuggestionLog;
use App\Models\GoldenResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app.sidebar')]
class SuggestedRepliesTraining extends Component
{
    use WithPagination;

    public string $tab = 'golden';

    public bool $showModal = false;

    public string $newContent = '';

    public ?int $newCategoryId = null;

    public function rules(): array
    {
        return [
            'newContent' => 'required|string|max:5000',
            'newCategoryId' => 'nullable|exists:ticket_categories,id',
        ];
    }

    #[Computed]
    public function goldenResponses(): Collection
    {
        return GoldenResponse::query()
            ->where('company_id', Auth::user()->company_id)
            ->with(['user:id,name', 'category:id,name'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function suggestionFeed(): LengthAwarePaginator
    {
        return AiSuggestionLog::query()
            ->where('company_id', Auth::user()->company_id)
            ->with(['user:id,name', 'ticket:id,subject,ticket_number'])
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function categories(): Collection
    {
        return \App\Models\TicketCategory::query()
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
    }

    public function openAdd(): void
    {
        $this->newContent = '';
        $this->newCategoryId = null;
        $this->showModal = true;
    }

    public function saveGolden(): void
    {
        $this->validate();

        GoldenResponse::create([
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::id(),
            'content' => $this->newContent,
            'category_id' => $this->newCategoryId ?: null,
        ]);

        $this->showModal = false;
        $this->newContent = '';
        $this->newCategoryId = null;
        unset($this->goldenResponses);

        $this->dispatch('show-toast', message: 'Golden response saved.', type: 'success');
    }

    public function deleteGolden(int $id): void
    {
        GoldenResponse::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->delete();

        unset($this->goldenResponses);
        $this->dispatch('show-toast', message: 'Golden response removed.', type: 'success');
    }

    public function render()
    {
        return view('livewire.ai.suggested-replies-training');
    }
}
