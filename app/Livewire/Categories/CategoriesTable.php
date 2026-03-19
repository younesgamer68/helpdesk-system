<?php

namespace App\Livewire\Categories;

use App\Models\TicketCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoriesTable extends Component
{
    public $search = '';

    public $sortBy = 'name';

    public $sortDirection = 'asc';

    // Modal state
    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteConfirmation = false;

    public $editingCategoryId = null;

    public $deletingCategoryId = null;

    // Form fields
    public $name = '';

    public $description = '';

    public $default_priority = 'medium';

    public ?int $parent_id = null;

    public bool $lockParentSelection = false;

    public string $deleteConfirmationMessage = 'Are you sure you want to delete this category? This action cannot be undone. Tickets using this category will have their category set to none.';

    protected function rules(): array
    {
        $uniqueRule = Rule::unique('ticket_categories', 'name')
            ->where(function ($query) {
                $query->where('company_id', Auth::user()->company_id);

                if ($this->parent_id) {
                    $query->where('parent_id', $this->parent_id);
                } else {
                    $query->whereNull('parent_id');
                }
            });

        if ($this->editingCategoryId) {
            $uniqueRule->ignore($this->editingCategoryId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => 'nullable|string|max:1000',
            'default_priority' => 'required|in:low,medium,high,urgent',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('ticket_categories', 'id')->where(fn ($query) => $query->where('company_id', Auth::user()->company_id)),
                function (string $attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $parentCategory = TicketCategory::query()->find($value);

                    if (! $parentCategory) {
                        $fail('The selected parent category is invalid.');

                        return;
                    }

                    if ($parentCategory->parent_id) {
                        $fail('Subcategories cannot have children. Choose a top-level category as the parent.');

                        return;
                    }

                    if ($this->editingCategoryId && (int) $value === (int) $this->editingCategoryId) {
                        $fail('A category cannot be its own parent.');

                        return;
                    }

                    if ($this->editingCategoryId) {
                        $category = TicketCategory::query()->withCount('children')->find($this->editingCategoryId);

                        if ($category && $category->children_count > 0) {
                            $fail('A category with subcategories cannot become a child category.');
                        }
                    }
                },
            ],
        ];
    }

    protected $validationAttributes = [
        'name' => 'category name',
        'default_priority' => 'default priority',
        'parent_id' => 'parent category',
    ];

    public function updatingSearch(): void {}

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
    public function categories()
    {
        if ($this->search) {
            return TicketCategory::query()
                ->where('company_id', Auth::user()->company_id)
                ->with('parent')
                ->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->sortBy, $this->sortDirection)
                ->get();
        }

        return TicketCategory::query()
            ->where('company_id', Auth::user()->company_id)
            ->parents()
            ->with(['children' => fn ($query) => $query->orderBy($this->sortBy, $this->sortDirection)])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();
    }

    #[Computed]
    public function parentCategoriesForSelect()
    {
        return TicketCategory::query()
            ->where('company_id', Auth::user()->company_id)
            ->parents()
            ->when($this->editingCategoryId, fn ($query) => $query->where('id', '!=', $this->editingCategoryId))
            ->orderBy('name')
            ->get();
    }

    #[On('open-create-category-modal')]
    public function openCreateModal(?int $parentId = null): void
    {
        $this->resetForm();
        $this->parent_id = $parentId;
        $this->lockParentSelection = $parentId !== null;
        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function createCategory(): void
    {
        $this->validate();

        TicketCategory::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'default_priority' => $this->default_priority,
            'parent_id' => $this->parent_id,
        ]);

        $this->dispatch('show-toast', message: "Category '{$this->name}' created successfully!", type: 'success');
        $this->closeCreateModal();
        unset($this->categories);
    }

    public function editCategory(int $categoryId): void
    {
        $category = TicketCategory::where('company_id', Auth::user()->company_id)
            ->findOrFail($categoryId);

        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->default_priority = $category->default_priority;
        $this->parent_id = $category->parent_id;
        $this->lockParentSelection = false;
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingCategoryId = null;
        $this->resetForm();
    }

    public function updateCategory(): void
    {
        $this->validate();

        $category = TicketCategory::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->editingCategoryId);

        $category->update([
            'name' => $this->name,
            'description' => $this->description,
            'default_priority' => $this->default_priority,
            'parent_id' => $this->parent_id,
        ]);

        $this->dispatch('show-toast', message: "Category '{$this->name}' updated successfully!", type: 'success');
        $this->closeEditModal();
        unset($this->categories);
    }

    public function confirmDelete(int $categoryId): void
    {
        $category = TicketCategory::query()
            ->where('company_id', Auth::user()->company_id)
            ->withCount('children')
            ->findOrFail($categoryId);

        $this->deletingCategoryId = $categoryId;
        $this->deleteConfirmationMessage = $category->children_count > 0
            ? "This category has {$category->children_count} subcategories. Deleting it will also delete all subcategories and unassign them from any tickets."
            : 'Are you sure you want to delete this category? This action cannot be undone. Tickets using this category will have their category set to none.';
        $this->showDeleteConfirmation = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingCategoryId = null;
        $this->showDeleteConfirmation = false;
    }

    public function deleteCategory(): void
    {
        $category = TicketCategory::where('company_id', Auth::user()->company_id)
            ->with('children')
            ->findOrFail($this->deletingCategoryId);

        $categoryName = $category->name;

        foreach ($category->children as $childCategory) {
            $childCategory->delete();
        }

        $category->delete();

        $this->dispatch('show-toast', message: "Category '{$categoryName}' deleted successfully!", type: 'success');
        $this->cancelDelete();
        unset($this->categories);
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->default_priority = 'medium';
        $this->parent_id = null;
        $this->lockParentSelection = false;
        $this->editingCategoryId = null;
    }

    public function render()
    {
        return view('livewire.categories.categories-table');
    }
}
