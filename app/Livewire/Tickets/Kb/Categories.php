<?php

namespace App\Livewire\Tickets\Kb;

use App\Models\KbCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('KB Categories')]
class Categories extends Component
{
    public $showModal = false;

    public $isEditing = false;

    public $categoryId = null;

    public $name = '';

    public $description = '';

    public $parentId = null;

    public $icon = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'parentId' => 'nullable|exists:kb_categories,id',
            'icon' => 'nullable|string|max:255',
        ];
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'categoryId', 'isEditing', 'parentId', 'icon']);
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $category = KbCategory::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->parentId = $category->parent_id;
        $this->icon = $category->icon;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $category = KbCategory::where('company_id', Auth::user()->company_id)->findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
                'parent_id' => $this->parentId ?: null,
                'icon' => $this->icon,
            ]);
            $this->dispatch('show-toast', message: 'Category updated successfully.', type: 'success');
        } else {
            $maxOrder = KbCategory::where('company_id', Auth::user()->company_id)
                ->where('parent_id', $this->parentId ?: null)
                ->max('order');

            KbCategory::create([
                'company_id' => Auth::user()->company_id,
                'name' => $this->name,
                'description' => $this->description,
                'parent_id' => $this->parentId ?: null,
                'icon' => $this->icon,
                'order' => $maxOrder !== null ? $maxOrder + 1 : 0,
            ]);
            $this->dispatch('show-toast', message: 'Category created successfully.', type: 'success');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $category = KbCategory::where('company_id', Auth::user()->company_id)->findOrFail($id);

        if ($category->articles()->exists()) {
            $this->dispatch('show-toast', message: 'Cannot delete category because it has articles attached.', type: 'error');

            return;
        }

        $category->delete();
        $this->dispatch('show-toast', message: 'Category deleted successfully.', type: 'success');
    }

    public function render()
    {
        $categories = KbCategory::where('company_id', Auth::user()->company_id)
            ->whereNull('parent_id')
            ->withCount('articles')
            ->with([
                'children' => function ($query) {
                    $query->withCount('articles');
                },
            ])
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        $allCategories = KbCategory::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return view('livewire.tickets.kb.categories', [
            'categories' => $categories,
            'allCategories' => $allCategories,
        ]);
    }
}
