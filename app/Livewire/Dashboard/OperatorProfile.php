<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OperatorProfile extends Component
{
    public User $operator;

    public $role;

    public $selectedCategories = [];

    public $showSpecialtiesModal = false;

    public function mount(User $operator)
    {
        // Scope to current company
        if ($operator->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $this->operator = $operator;
        $this->role = $operator->role;

        $categories = $operator->categories()->pluck('ticket_category_id')->map(fn ($id) => (string) $id)->toArray();
        if ($operator->specialty_id && ! in_array((string) $operator->specialty_id, $categories)) {
            $categories[] = (string) $operator->specialty_id;
        }
        $this->selectedCategories = $categories;
    }

    #[Computed]
    public function categories()
    {
        return TicketCategory::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function openTickets()
    {
        return $this->operator->assignedTickets()
            ->open()
            ->latest()
            ->get();
    }

    #[Computed]
    public function recentActivity()
    {
        return TicketLog::where('user_id', $this->operator->id)
            ->with('ticket')
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function stats()
    {
        return [
            'resolved_this_month' => Ticket::where('assigned_to', $this->operator->id)
                ->where('status', 'resolved')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count(),
            'avg_response_time' => '2h 15m', // Placeholder as per schema limitation
            'satisfaction_rate' => '98%',   // Placeholder as per schema limitation
        ];
    }

    public function updateRole()
    {
        if ($this->operator->id === Auth::id()) {
            $this->dispatch('show-toast', message: 'You cannot change your own role.', type: 'error');
            $this->role = $this->operator->role;

            return;
        }

        $this->operator->update(['role' => $this->role]);
        $this->dispatch('show-toast', message: 'Role updated successfully.', type: 'success');
    }

    public function updateSpecialties()
    {
        $this->operator->specialty_id = ! empty($this->selectedCategories) ? (int) $this->selectedCategories[0] : null;
        $this->operator->save();

        $this->operator->categories()->sync($this->selectedCategories);
        $this->showSpecialtiesModal = false;
        $this->dispatch('show-toast', message: 'Specialties updated successfully.', type: 'success');
        $this->operator->load(['categories', 'specialty']);
    }

    public function removeOperator()
    {
        if ($this->operator->id === Auth::id()) {
            $this->dispatch('show-toast', message: 'You cannot remove yourself.', type: 'error');

            return;
        }

        // Reassign tickets to unassigned
        $this->operator->assignedTickets()->update(['assigned_to' => null]);

        $this->operator->delete();

        $this->dispatch('show-toast', message: 'Operator removed successfully.', type: 'success');

        return redirect()->route('operators', ['company' => $this->operator->company->slug]);
    }

    public function render()
    {
        return view('livewire.dashboard.operator-profile');
    }
}
