<?php

namespace App\Livewire\Operators;

use App\Models\Team;
use App\Models\User;
use App\Notifications\TeamAssigned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TeamsTable extends Component
{
    public string $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    // Modal states
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showDeleteConfirmation = false;

    public bool $showMembersModal = false;

    public ?int $editingTeamId = null;

    public ?int $deletingTeamId = null;

    public ?int $managingTeamId = null;

    // Form fields
    public string $name = '';

    public string $description = '';

    public string $color = '#14b8a6';

    // Member management
    public ?int $addMemberId = null;

    public string $addMemberRole = 'member';

    public function setSortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    protected function rules(): array
    {
        $uniqueRule = Rule::unique('teams', 'name')
            ->where('company_id', Auth::user()->company_id);

        if ($this->editingTeamId) {
            $uniqueRule->ignore($this->editingTeamId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|max:7',
        ];
    }

    #[Computed]
    public function teams()
    {
        $query = Team::query()
            ->where('company_id', Auth::user()->company_id)
            ->withCount('members');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->get();
    }

    #[Computed]
    public function availableOperators()
    {
        if (! $this->managingTeamId) {
            return collect();
        }

        $team = Team::query()->find($this->managingTeamId);

        if (! $team) {
            return collect();
        }

        $existingMemberIds = $team->members()->pluck('users.id');

        return User::query()
            ->where('company_id', Auth::user()->company_id)
            ->operators()
            ->whereNotIn('id', $existingMemberIds)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function managingTeam(): ?Team
    {
        if (! $this->managingTeamId) {
            return null;
        }

        return Team::query()
            ->where('company_id', Auth::user()->company_id)
            ->with(['members' => fn ($q) => $q->orderBy('name')])
            ->find($this->managingTeamId);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function createTeam(): void
    {
        $this->validate();

        Team::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
        ]);

        $this->dispatch('show-toast', message: "Team '{$this->name}' created successfully!", type: 'success');
        $this->closeCreateModal();
        unset($this->teams);
    }

    public function editTeam(int $teamId): void
    {
        $team = Team::where('company_id', Auth::user()->company_id)
            ->findOrFail($teamId);

        $this->editingTeamId = $team->id;
        $this->name = $team->name;
        $this->description = $team->description ?? '';
        $this->color = $team->color ?? '#14b8a6';
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingTeamId = null;
        $this->resetForm();
    }

    public function updateTeam(): void
    {
        $this->validate();

        $team = Team::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->editingTeamId);

        $team->update([
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
        ]);

        $this->dispatch('show-toast', message: "Team '{$this->name}' updated successfully!", type: 'success');
        $this->closeEditModal();
        unset($this->teams);
    }

    public function confirmDelete(int $teamId): void
    {
        $this->deletingTeamId = $teamId;
        $this->showDeleteConfirmation = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingTeamId = null;
        $this->showDeleteConfirmation = false;
    }

    public function deleteTeam(): void
    {
        $team = Team::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->deletingTeamId);

        $teamName = $team->name;
        $team->members()->detach();
        $team->delete();

        $this->dispatch('show-toast', message: "Team '{$teamName}' deleted successfully!", type: 'success');
        $this->cancelDelete();
        unset($this->teams);
    }

    public function manageMembers(int $teamId): void
    {
        $this->managingTeamId = $teamId;
        $this->addMemberId = null;
        $this->addMemberRole = 'member';
        $this->showMembersModal = true;
        unset($this->availableOperators, $this->managingTeam);
    }

    public function closeMembersModal(): void
    {
        $this->showMembersModal = false;
        $this->managingTeamId = null;
        $this->addMemberId = null;
        unset($this->availableOperators, $this->managingTeam);
    }

    public function addMember(): void
    {
        if (! $this->addMemberId || ! $this->managingTeamId) {
            return;
        }

        $team = Team::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->managingTeamId);

        $operator = User::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->addMemberId);

        $team->members()->attach($operator->id, ['role' => $this->addMemberRole]);

        $operator->notify(new TeamAssigned($team, $this->addMemberRole));

        $this->addMemberId = null;
        $this->addMemberRole = 'member';
        $this->dispatch('show-toast', message: "{$operator->name} added to the team.", type: 'success');
        unset($this->availableOperators, $this->managingTeam, $this->teams);
    }

    public function removeMember(int $userId): void
    {
        if (! $this->managingTeamId) {
            return;
        }

        $team = Team::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->managingTeamId);

        $member = User::findOrFail($userId);
        $team->members()->detach($userId);

        $this->dispatch('show-toast', message: "{$member->name} removed from the team.", type: 'success');
        unset($this->availableOperators, $this->managingTeam, $this->teams);
    }

    public function toggleMemberRole(int $userId): void
    {
        if (! $this->managingTeamId) {
            return;
        }

        $team = Team::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->managingTeamId);

        $pivot = $team->members()->where('users.id', $userId)->first()?->pivot;

        if ($pivot) {
            $newRole = $pivot->role === 'lead' ? 'member' : 'lead';
            $team->members()->updateExistingPivot($userId, ['role' => $newRole]);
            $this->dispatch('show-toast', message: "Role updated to {$newRole}.", type: 'success');
            unset($this->managingTeam);
        }
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->color = '#14b8a6';
        $this->editingTeamId = null;
    }

    public function render()
    {
        return view('livewire.operators.teams-table');
    }
}
