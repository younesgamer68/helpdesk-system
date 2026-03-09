<?php

namespace App\Livewire\Dashboard;

use App\Models\AutomationRule;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AutomationRulesTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterStatus = '';

    public string $sortBy = 'priority';

    public string $sortDirection = 'asc';

    // Modal states
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showDeleteConfirmation = false;

    public ?int $editingRuleId = null;

    public ?int $deletingRuleId = null;

    // Form fields
    public string $name = '';

    public string $description = '';

    public string $type = 'assignment';

    public bool $is_active = true;

    public int $priority = 0;

    // Condition fields
    public ?int $category_id = null;

    public array $conditionPriorities = [];

    public array $keywords = [];

    public string $newKeyword = '';

    public int $idle_hours = 24;

    public array $conditionStatuses = ['pending', 'open'];

    public bool $on_create = true;

    // Action fields
    public bool $assign_to_specialist = true;

    public bool $fallback_to_generalist = true;

    public ?int $assign_to_operator_id = null;

    public string $set_priority = 'high';

    public bool $send_email = true;

    public string $email_subject = '';

    public string $email_message = 'Thank you for your ticket. Our team will respond shortly.';

    public bool $escalate_priority = true;

    public bool $notify_admin = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:assignment,priority,auto_reply,escalation',
            'is_active' => 'boolean',
            'priority' => 'required|integer|min:0|max:1000',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function setSortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function automationRules()
    {
        $query = AutomationRule::query()
            ->where('company_id', Auth::user()->company_id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus === '1');
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }

    #[Computed]
    public function categories()
    {
        return TicketCategory::where('company_id', Auth::user()->company_id)->get();
    }

    #[Computed]
    public function operators()
    {
        return User::query()
            ->where('company_id', Auth::user()->company_id)
            ->operators()
            ->get();
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

    public function createRule(): void
    {
        $this->validate();

        AutomationRule::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'conditions' => $this->buildConditions(),
            'actions' => $this->buildActions(),
            'is_active' => $this->is_active,
            'priority' => $this->priority,
        ]);

        $this->dispatch('show-toast', message: "Rule '{$this->name}' created successfully!", type: 'success');
        $this->closeCreateModal();
        unset($this->automationRules);
    }

    public function editRule(int $ruleId): void
    {
        $rule = AutomationRule::where('company_id', Auth::user()->company_id)
            ->findOrFail($ruleId);

        $this->editingRuleId = $rule->id;
        $this->name = $rule->name;
        $this->description = $rule->description ?? '';
        $this->type = $rule->type;
        $this->is_active = $rule->is_active;
        $this->priority = $rule->priority;

        $this->loadConditions($rule->conditions);
        $this->loadActions($rule->actions);

        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingRuleId = null;
        $this->resetForm();
    }

    public function updateRule(): void
    {
        $this->validate();

        $rule = AutomationRule::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->editingRuleId);

        $rule->update([
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'conditions' => $this->buildConditions(),
            'actions' => $this->buildActions(),
            'is_active' => $this->is_active,
            'priority' => $this->priority,
        ]);

        $this->dispatch('show-toast', message: "Rule '{$this->name}' updated successfully!", type: 'success');
        $this->closeEditModal();
        unset($this->automationRules);
    }

    public function toggleRuleStatus(int $ruleId): void
    {
        $rule = AutomationRule::where('company_id', Auth::user()->company_id)
            ->findOrFail($ruleId);

        $rule->update(['is_active' => ! $rule->is_active]);

        $status = $rule->is_active ? 'enabled' : 'disabled';
        $this->dispatch('show-toast', message: "Rule '{$rule->name}' {$status}.", type: 'success');
        unset($this->automationRules);
    }

    public function confirmDelete(int $ruleId): void
    {
        $this->deletingRuleId = $ruleId;
        $this->showDeleteConfirmation = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingRuleId = null;
        $this->showDeleteConfirmation = false;
    }

    public function deleteRule(): void
    {
        $rule = AutomationRule::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->deletingRuleId);

        $ruleName = $rule->name;
        $rule->delete();

        $this->dispatch('show-toast', message: "Rule '{$ruleName}' deleted successfully!", type: 'success');
        $this->cancelDelete();
        unset($this->automationRules);
    }

    public function addKeyword(): void
    {
        if ($this->newKeyword && ! in_array($this->newKeyword, $this->keywords)) {
            $this->keywords[] = $this->newKeyword;
            $this->newKeyword = '';
        }
    }

    public function removeKeyword(int $index): void
    {
        unset($this->keywords[$index]);
        $this->keywords = array_values($this->keywords);
    }

    protected function buildConditions(): array
    {
        return match ($this->type) {
            'assignment' => [
                'category_id' => $this->category_id,
                'priority' => $this->conditionPriorities,
            ],
            'priority' => [
                'keywords' => $this->keywords,
                'category_id' => $this->category_id,
                'current_priority' => $this->conditionPriorities,
            ],
            'auto_reply' => [
                'on_create' => $this->on_create,
                'category_id' => $this->category_id,
                'priority' => $this->conditionPriorities,
            ],
            'escalation' => [
                'idle_hours' => $this->idle_hours,
                'status' => $this->conditionStatuses,
                'category_id' => $this->category_id,
            ],
            default => [],
        };
    }

    protected function buildActions(): array
    {
        return match ($this->type) {
            'assignment' => [
                'assign_to_specialist' => $this->assign_to_specialist,
                'fallback_to_generalist' => $this->fallback_to_generalist,
                'assign_to_operator_id' => $this->assign_to_operator_id,
            ],
            'priority' => [
                'set_priority' => $this->set_priority,
            ],
            'auto_reply' => [
                'send_email' => $this->send_email,
                'subject' => $this->email_subject,
                'message' => $this->email_message,
            ],
            'escalation' => [
                'escalate_priority' => $this->escalate_priority,
                'set_priority' => $this->escalate_priority ? null : $this->set_priority,
                'notify_admin' => $this->notify_admin,
            ],
            default => [],
        };
    }

    protected function loadConditions(array $conditions): void
    {
        $this->category_id = $conditions['category_id'] ?? null;
        $this->conditionPriorities = $conditions['priority'] ?? $conditions['current_priority'] ?? [];
        $this->keywords = $conditions['keywords'] ?? [];
        $this->idle_hours = $conditions['idle_hours'] ?? 24;
        $this->conditionStatuses = $conditions['status'] ?? ['pending', 'open'];
        $this->on_create = $conditions['on_create'] ?? true;
    }

    protected function loadActions(array $actions): void
    {
        $this->assign_to_specialist = $actions['assign_to_specialist'] ?? true;
        $this->fallback_to_generalist = $actions['fallback_to_generalist'] ?? true;
        $this->assign_to_operator_id = $actions['assign_to_operator_id'] ?? null;
        $this->set_priority = $actions['set_priority'] ?? 'high';
        $this->send_email = $actions['send_email'] ?? true;
        $this->email_subject = $actions['subject'] ?? '';
        $this->email_message = $actions['message'] ?? 'Thank you for your ticket. Our team will respond shortly.';
        $this->escalate_priority = $actions['escalate_priority'] ?? true;
        $this->notify_admin = $actions['notify_admin'] ?? true;
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->type = 'assignment';
        $this->is_active = true;
        $this->priority = 0;
        $this->category_id = null;
        $this->conditionPriorities = [];
        $this->keywords = [];
        $this->newKeyword = '';
        $this->idle_hours = 24;
        $this->conditionStatuses = ['pending', 'open'];
        $this->on_create = true;
        $this->assign_to_specialist = true;
        $this->fallback_to_generalist = true;
        $this->assign_to_operator_id = null;
        $this->set_priority = 'high';
        $this->send_email = true;
        $this->email_subject = '';
        $this->email_message = 'Thank you for your ticket. Our team will respond shortly.';
        $this->escalate_priority = true;
        $this->notify_admin = true;
        $this->editingRuleId = null;
    }

    public function render()
    {
        return view('livewire.dashboard.automation-rules-table');
    }
}
