<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class OperatorsTable extends Component
{
    use WithPagination;

    public $search = '';

    public $roleFilter = '';

    public $statusFilter = ''; // 'active' or 'pending'

    public $sortBy = 'name';

    public $sortDirection = 'asc';

    // Modal state
    public $showCreateModal = false;

    public $showDiscardConfirmation = false;

    // Form fields
    public $inviteName = '';

    public $inviteEmail = '';

    public $inviteRole = 'operator';

    protected function rules()
    {
        return [
            'inviteName' => 'required|string|max:255',
            'inviteEmail' => 'required|email|max:255|unique:users,email',
            'inviteRole' => 'required|in:admin,operator',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function setSortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    #[Computed]
    public function hasActiveFilters()
    {
        return $this->search || $this->roleFilter || $this->statusFilter;
    }

    #[Computed]
    public function operators()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = User::where('company_id', $user->company_id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->roleFilter) {
            $query->where('role', '=', $this->roleFilter);
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'pending') {
                $query->whereNull('password');
            } elseif ($this->statusFilter === 'active') {
                $query->whereNotNull('password');
            }
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function removeUser($userId)
    {
        $user = User::where('company_id', Auth::user()->company_id)->findOrFail($userId);

        // Don't let them remove themselves
        if ($user->id === Auth::user()->id) {
            abort(403, 'You cannot remove yourself.');
        }

        $isPending = is_null($user->password);
        $user->delete();

        if ($isPending) {
            $this->dispatch('show-toast', message: 'Invitation revoked successfully.', type: 'success');
        } else {
            $this->dispatch('show-toast', message: 'Team member removed successfully.', type: 'error');
        }
    }

    public function resendInvite($userId)
    {
        $user = User::where('company_id', Auth::user()->company_id)->findOrFail($userId);

        if ($user->password !== null) {
            abort(400, 'User has already accepted their invitation.');
        }

        $signedUrl = \Illuminate\Support\Facades\URL::signedRoute('invitations.accept', ['user' => $user->id]);
        \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\UserInvitationMail($user, $signedUrl));

        $this->dispatch('show-toast', message: 'Invitation resent successfully.', type: 'success');
    }

    public function hasFormData()
    {
        return $this->inviteName || $this->inviteEmail;
    }

    #[\Livewire\Attributes\On('open-create-operator-modal')]
    public function openCreateModal()
    {
        if (! $this->hasFormData()) {
            $this->inviteRole = 'operator';
        }

        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function attemptCloseCreateModal()
    {
        if ($this->hasFormData()) {
            $this->showDiscardConfirmation = true;
        } else {
            $this->closeCreateModal();
        }
    }

    public function cancelDiscard()
    {
        $this->showDiscardConfirmation = false;
    }

    public function confirmDiscard()
    {
        $this->showDiscardConfirmation = false;
        $this->closeCreateModal();
        $this->clearForm();
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->showDiscardConfirmation = false;
    }

    public function clearForm()
    {
        $this->reset(['inviteName', 'inviteEmail']);
        $this->inviteRole = 'operator';
        $this->resetValidation();
    }

    public function createAgent()
    {
        $this->validate();

        $company = Auth::user()->company;

        /** @var \App\Models\User $user */
        $user = $company->user()->create([
            'name' => $this->inviteName,
            'email' => $this->inviteEmail,
            'role' => $this->inviteRole,
            'password' => null, // Pending status
        ]);

        $signedUrl = \Illuminate\Support\Facades\URL::signedRoute('invitations.accept', ['user' => $user->id]);
        \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\UserInvitationMail($user, $signedUrl));

        $this->dispatch('show-toast', message: 'Agent invited successfully.', type: 'success');

        $this->closeCreateModal();
        $this->clearForm();

        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.dashboard.operators-table');
    }
}
