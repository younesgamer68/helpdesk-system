<?php

namespace App\Livewire\Tickets\Kb;

use App\Models\TicketCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('KB Categories')]
class Categories extends Component
{
    /**
     * KB categories are now managed through the company's ticket categories.
     * This component displays the available categories for KB articles.
     * To manage KB categories, go to Settings > Categories.
     */
    public function render()
    {
        $categories = TicketCategory::where('company_id', Auth::user()->company_id)
            ->whereNull('parent_id')
            ->withCount(['kbArticles' => function ($query) {
                $query->where('status', 'published');
            }])
            ->with(['children' => function ($query) {
                $query->withCount(['kbArticles' => function ($q) {
                    $q->where('status', 'published');
                }])->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('livewire.tickets.kb.categories', [
            'categories' => $categories,
        ]);
    }
}
