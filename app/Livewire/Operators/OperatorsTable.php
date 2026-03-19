<?php

namespace App\Livewire\Operators;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class OperatorsTable extends Component
{
    use WithPagination;

    public $search = '';

    public $roleFilter = '';

    public $statusFilter = ''; // 'active' or 'pending'

    public $sortDirection = 'asc';

    public $sortBy = 'name';

    public $selected = [];

    public $selectAll = false;

    // Modal state
    public $showCreateModal = false;

    public $showBulkInviteModal = false;

    public $showDiscardConfirmation = false;

    // Form fields
    public $inviteName = '';

    public $inviteEmail = '';

    public $inviteRole = 'operator';

    // Bulk invite fields
    public $bulkInviteEmails = '';

    public $bulkInviteRole = 'operator';

    public $customViewName = '';

    public $showSaveViewModal = false;

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
        $this->selected = [];
        $this->resetPage();
    }

    public function selectAllRows($operatorIds)
    {
        $this->selected = $operatorIds;
    }

    public function deselectAll()
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        $visibleIds = $this->operators->pluck('id')
            ->reject(fn ($id) => $id === Auth::id())
            ->toArray();

        if ($value) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $visibleIds)));
        } else {
            $this->selected = array_values(array_diff($this->selected, $visibleIds));
        }
    }

    public function updatedSelected()
    {
        $this->selected = array_map('intval', (array) $this->selected);

        $visibleIds = $this->operators->pluck('id')
            ->reject(fn ($id) => $id === Auth::id())
            ->toArray();

        $selectedInVisible = array_intersect($this->selected, $visibleIds);

        $this->selectAll = count($visibleIds) > 0 && count($selectedInVisible) === count($visibleIds);
    }

    #[Computed]
    public function hasActiveFilters()
    {
        return $this->search || $this->roleFilter || $this->statusFilter;
    }

    #[Computed]
    public function savedViews()
    {
        return \App\Models\SavedFilterView::where('user_id', Auth::id())
            ->where('filters', 'like', '%"is_operator_view":true%')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function operators()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = User::query()
            ->where('company_id', '=', $user->company_id)
            ->where('id', '!=', $user->id);

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
                $query->whereNull('password')->whereNull('google_id');
            } elseif ($this->statusFilter === 'active') {
                $query->where(function ($q) {
                    $q->whereNotNull('password')->orWhereNotNull('google_id');
                });
            }
        }

        return $query->with(['categories', 'assignedTickets', 'specialty', 'teams'])
            ->withCount(['assignedTickets as open_tickets_count' => function ($query) {
                $query->whereIn('status', ['open', 'in_progress', 'pending']);
            }])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function removeUser($userId)
    {
        $user = User::where('company_id', '=', Auth::user()->company_id)->findOrFail($userId);

        // Don't let them remove themselves
        if ($user->id === Auth::user()->id) {
            abort(403, 'You cannot remove yourself.');
        }

        $isStatusPending = $user->isPendingInvite();

        if (! $isStatusPending) {
            $user->assignedTickets()->update(['assigned_to' => null]);
            $this->dispatch('show-toast', message: 'Team member removed and tickets unassigned.', type: 'success');
            $user->delete();
        } else {
            $user->forceDelete();
            $this->dispatch('show-toast', message: 'Invitation revoked successfully.', type: 'success');
        }
    }

    public function resendInvite($userId)
    {
        $user = User::where('company_id', Auth::user()->company_id)->findOrFail($userId);

        if (! $user->isPendingInvite()) {
            abort(400, 'User has already accepted their invitation.');
        }

        $expiresAt = now()->addHours((int) config('auth.invitation_expire_hours', 72));
        $user->update([
            'invite_sent_at' => now(),
            'invite_expires_at' => $expiresAt,
            'invite_expired_notified_at' => null,
        ]);

        $signedUrl = URL::temporarySignedRoute('invitations.accept', $expiresAt, ['user' => $user->id]);
        Mail::to($user->email)->send(new \App\Mail\UserInvitationMail($user, $signedUrl));

        $this->dispatch('show-toast', message: 'Invitation resent successfully.', type: 'success');
    }

    public function bulkResendInvites()
    {
        $users = User::where('company_id', '=', Auth::user()->company_id)
            ->whereIn('id', $this->selected)
            ->get(['id', 'email', 'company_id', 'password', 'google_id']);

        $count = 0;
        foreach ($users as $user) {
            if ($user->isPendingInvite()) {
                $expiresAt = now()->addHours((int) config('auth.invitation_expire_hours', 72));
                $user->update([
                    'invite_sent_at' => now(),
                    'invite_expires_at' => $expiresAt,
                    'invite_expired_notified_at' => null,
                ]);

                $signedUrl = URL::temporarySignedRoute('invitations.accept', $expiresAt, ['user' => $user->id]);
                Mail::to($user->email)->send(new \App\Mail\UserInvitationMail($user, $signedUrl));
                $count++;
            }
        }

        $this->selected = [];
        $this->dispatch('show-toast', message: "{$count} invitations resent successfully.", type: 'success');
    }

    public function bulkRevokeInvites()
    {
        $users = User::where('company_id', '=', Auth::user()->company_id)
            ->whereIn('id', $this->selected)
            ->get(['id', 'company_id', 'email', 'password', 'google_id']);

        $count = 0;
        foreach ($users as $user) {
            if ($user->isPendingInvite() && $user->id !== Auth::id()) {
                $user->forceDelete();
                $count++;
            }
        }

        $this->selected = [];
        $this->dispatch('show-toast', message: "{$count} invitations revoked successfully.", type: 'success');
    }

    public function bulkRemoveMembers()
    {
        $users = User::where('company_id', '=', Auth::user()->company_id)
            ->whereIn('id', $this->selected)
            ->get(['id', 'company_id', 'password', 'google_id']);

        $count = 0;
        foreach ($users as $user) {
            if ($user->isActive() && $user->id !== Auth::id()) {
                // Reassign tickets to null
                $user->assignedTickets()->update(['assigned_to' => null]);
                $user->delete();
                $count++;
            }
        }

        $this->selected = [];
        $this->dispatch('show-toast', message: "{$count} members removed successfully.", type: 'success');
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

        $expiresAt = now()->addHours((int) config('auth.invitation_expire_hours', 72));

        /** @var \App\Models\User $user */
        $user = $company->user()->create([
            'name' => $this->inviteName,
            'email' => $this->inviteEmail,
            'role' => $this->inviteRole,
            'password' => null, // Pending status
            'invite_sent_at' => now(),
            'invite_expires_at' => $expiresAt,
            'invite_expired_notified_at' => null,
        ]);

        $signedUrl = URL::temporarySignedRoute('invitations.accept', $expiresAt, ['user' => $user->id]);
        Mail::to($user->email)->send(new \App\Mail\UserInvitationMail($user, $signedUrl));

        $this->dispatch('show-toast', message: 'Agent invited successfully.', type: 'success');

        $this->closeCreateModal();
        $this->clearForm();

        $this->resetPage();
    }

    #[\Livewire\Attributes\On('open-bulk-invite-modal')]
    public function openBulkInviteModal()
    {
        $this->bulkInviteRole = 'operator';
        $this->bulkInviteEmails = '';
        $this->showBulkInviteModal = true;
        $this->resetValidation();
    }

    public function closeBulkInviteModal()
    {
        $this->showBulkInviteModal = false;
    }

    public function processBulkInvite()
    {
        $this->validate([
            'bulkInviteEmails' => 'required|string',
            'bulkInviteRole' => 'required|in:admin,operator',
        ]);

        $company = Auth::user()->company;
        $emails = preg_split('/[,\n\r]+/', $this->bulkInviteEmails);
        $emails = array_map('trim', $emails);
        $emails = array_filter($emails, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));
        $emails = array_unique($emails);

        $sent = 0;
        $skipped = 0;

        foreach ($emails as $email) {
            $existing = User::withoutGlobalScopes()
                ->withTrashed()
                ->where('email', '=', $email)
                ->exists();

            if ($existing) {
                $skipped++;

                continue;
            }

            $expiresAt = now()->addHours((int) config('auth.invitation_expire_hours', 72));

            $user = $company->user()->create([
                'name' => explode('@', $email)[0],
                'email' => $email,
                'role' => $this->bulkInviteRole,
                'password' => null,
                'invite_sent_at' => now(),
                'invite_expires_at' => $expiresAt,
                'invite_expired_notified_at' => null,
            ]);

            $signedUrl = URL::temporarySignedRoute('invitations.accept', $expiresAt, ['user' => $user->id]);
            Mail::to($user->email)->send(new \App\Mail\UserInvitationMail($user, $signedUrl));
            $sent++;
        }

        $this->bulkInviteEmails = '';
        $this->showBulkInviteModal = false;

        $message = "Successfully invited {$sent} members.";
        if ($skipped > 0) {
            $message .= " ({$skipped} already exist)";
        }

        $this->dispatch('show-toast', message: $message, type: 'success');
    }

    public function updateRole($userId, $newRole)
    {
        $user = User::where('company_id', '=', Auth::user()->company_id)->findOrFail($userId);

        if ($user->id === Auth::id()) {
            $this->dispatch('show-toast', message: 'You cannot change your own role.', type: 'error');

            return;
        }

        if (! in_array($newRole, ['admin', 'operator'])) {
            return;
        }

        $user->update(['role' => $newRole]);
        $this->dispatch('show-toast', message: 'Role updated successfully.', type: 'success');
    }

    public function applyPreset(string $preset): void
    {
        $this->clearFilters();

        if ($savedView = \App\Models\SavedFilterView::where('user_id', Auth::id())->where('id', (int) $preset)->first()) {
            foreach ($savedView->filters as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }

        $this->resetPage();
    }

    public function saveCustomView()
    {
        $this->validate([
            'customViewName' => 'required|string|max:255',
        ]);

        \App\Models\SavedFilterView::create([
            'user_id' => Auth::id(),
            'name' => $this->customViewName,
            'filters' => [
                'search' => $this->search,
                'roleFilter' => $this->roleFilter,
                'statusFilter' => $this->statusFilter,
                'is_operator_view' => true,
            ],
        ]);

        $this->dispatch('show-toast', message: 'View saved successfully!', type: 'success');
        $this->customViewName = '';
        $this->showSaveViewModal = false;
    }

    public function deleteSavedView($id)
    {
        \App\Models\SavedFilterView::where('user_id', Auth::id())->where('id', $id)->delete();
        $this->dispatch('show-toast', message: 'View removed successfully!', type: 'success');
    }

    #[Computed]
    public function categories()
    {
        return \App\Models\TicketCategory::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.operators.operators-table');
    }
}
