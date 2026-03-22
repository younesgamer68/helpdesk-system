<?php

namespace App\Livewire\App;

use App\Models\KbArticle;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Me')]
class MePage extends Component
{
    public string $name = '';

    public bool $editingName = false;

    public string $kbSearch = '';

    public array $selectedCategories = [];

    public bool $editingSpecialties = false;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->selectedCategories = Auth::user()->categories()->pluck('ticket_category_id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function saveName(): void
    {
        $this->validate(['name' => 'required|string|max:255']);
        Auth::user()->update(['name' => $this->name]);
        $this->editingName = false;
        $this->dispatch('show-toast', message: 'Name updated.', type: 'success');
    }

    public function cancelEditName(): void
    {
        $this->name = Auth::user()->name;
        $this->editingName = false;
    }

    public function toggleAvailability(): void
    {
        $user = Auth::user();
        $user->is_available = ! $user->is_available;
        $user->save();
    }

    public function saveSpecialties(): void
    {
        $user = Auth::user();
        $user->specialty_id = ! empty($this->selectedCategories) ? (int) $this->selectedCategories[0] : null;
        $user->save();
        $user->categories()->sync($this->selectedCategories);
        $this->editingSpecialties = false;
        $this->dispatch('show-toast', message: 'Specialties updated.', type: 'success');
    }

    #[Computed]
    public function resolvedThisWeek(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('status', 'resolved')
            ->where('updated_at', '>=', now()->startOfWeek())
            ->count();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return Ticket::query()
            ->where('assigned_to', Auth::id())
            ->where('status', 'pending')
            ->count();
    }

    #[Computed]
    public function unreadNotifications(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    #[Computed]
    public function categories()
    {
        return TicketCategory::where('company_id', Auth::user()->company_id)->get();
    }

    #[Computed]
    public function kbResults()
    {
        if (strlen($this->kbSearch) < 2) {
            return collect();
        }

        return KbArticle::query()
            ->where('status', 'published')
            ->where(function ($q) {
                $q->where('title', 'like', '%'.$this->kbSearch.'%')
                    ->orWhere('content', 'like', '%'.$this->kbSearch.'%');
            })
            ->select('id', 'title', 'slug', 'updated_at')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function userTeams()
    {
        return Auth::user()->teams()->select('teams.id', 'teams.name', 'teams.color')->get();
    }

    public function render()
    {
        return view('livewire.app.me-page');
    }
}
