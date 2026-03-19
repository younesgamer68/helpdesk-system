<?php

namespace App\Livewire\Ai;

use App\Models\AutoTriageRule;
use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app.sidebar')]
class AutoTriageRules extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'keyword';

    public string $keywordsInput = '';

    public ?int $category_id = null;

    public string $priority = '';

    public bool $is_active = true;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:keyword,ai',
            'keywordsInput' => 'required_if:type,keyword|string|max:1000',
            'category_id' => 'nullable|exists:ticket_categories,id',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'is_active' => 'boolean',
        ];
    }

    #[Computed]
    public function rules_list(): Collection
    {
        return AutoTriageRule::query()
            ->where('company_id', Auth::user()->company_id)
            ->with('category')
            ->orderBy('order')
            ->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        return TicketCategory::query()
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $rule = AutoTriageRule::query()
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $this->editingId = $rule->id;
        $this->name = $rule->name;
        $this->type = $rule->type;
        $this->keywordsInput = is_array($rule->keywords) ? implode(', ', $rule->keywords) : '';
        $this->category_id = $rule->category_id;
        $this->priority = $rule->priority ?? '';
        $this->is_active = $rule->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $keywords = $this->type === 'keyword'
            ? array_map('trim', explode(',', $this->keywordsInput))
            : null;

        $data = [
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'keywords' => $keywords,
            'category_id' => $this->category_id ?: null,
            'priority' => $this->priority ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            AutoTriageRule::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->editingId)
                ->update($data);
        } else {
            $data['order'] = AutoTriageRule::query()
                ->where('company_id', Auth::user()->company_id)
                ->max('order') + 1;
            AutoTriageRule::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        unset($this->rules_list);

        $this->dispatch('show-toast', message: 'Rule saved.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $rule = AutoTriageRule::query()
            ->where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $rule->update(['is_active' => ! $rule->is_active]);
        unset($this->rules_list);
    }

    public function delete(int $id): void
    {
        AutoTriageRule::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->delete();

        unset($this->rules_list);
        $this->dispatch('show-toast', message: 'Rule deleted.', type: 'success');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->type = 'keyword';
        $this->keywordsInput = '';
        $this->category_id = null;
        $this->priority = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.ai.auto-triage-rules');
    }
}
