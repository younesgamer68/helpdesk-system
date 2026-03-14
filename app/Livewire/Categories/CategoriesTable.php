<?php

namespace App\Livewire\Categories;

use App\Models\TicketCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriesTable extends Component
{
    use WithPagination;

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

    public $color = '#0F766E';

    public $default_priority = 'medium';

    protected function rules(): array
    {
        $uniqueRule = 'unique:ticket_categories,name,NULL,id,company_id,'.Auth::user()->company_id;

        if ($this->editingCategoryId) {
            $uniqueRule = 'unique:ticket_categories,name,'.$this->editingCategoryId.',id,company_id,'.Auth::user()->company_id;
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'default_priority' => 'required|in:low,medium,high,urgent',
        ];
    }

    protected $validationAttributes = [
        'name' => 'category name',
        'default_priority' => 'default priority',
    ];

    public function updatingSearch(): void
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
    public function categories()
    {
        $query = TicketCategory::where('company_id', Auth::user()->company_id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }

    #[On('open-create-category-modal')]
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

    public function createCategory(): void
    {
        $this->validate();

        TicketCategory::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'default_priority' => $this->default_priority,
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
        $this->color = $category->color ?? '#0F766E';
        $this->default_priority = $category->default_priority;
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
            'color' => $this->color,
            'default_priority' => $this->default_priority,
        ]);

        $this->dispatch('show-toast', message: "Category '{$this->name}' updated successfully!", type: 'success');
        $this->closeEditModal();
        unset($this->categories);
    }

    public function confirmDelete(int $categoryId): void
    {
        $this->deletingCategoryId = $categoryId;
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
            ->findOrFail($this->deletingCategoryId);

        $categoryName = $category->name;
        $category->delete();

        $this->dispatch('show-toast', message: "Category '{$categoryName}' deleted successfully!", type: 'success');
        $this->cancelDelete();
        unset($this->categories);
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->color = '#0F766E';
        $this->default_priority = 'medium';
        $this->editingCategoryId = null;
    }

    public function render()
    {
        return view('livewire.categories.categories-table');
    }
}
