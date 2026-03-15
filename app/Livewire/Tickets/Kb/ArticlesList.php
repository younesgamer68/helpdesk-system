<?php

namespace App\Livewire\Tickets\Kb;

use App\Models\KbArticle;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('KB Articles')]
class ArticlesList extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function togglePublish($id)
    {
        $article = KbArticle::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $article->update([
            'status' => $article->status === 'published' ? 'draft' : 'published',
        ]);

        $this->dispatch('show-toast', ['message' => 'Article status updated.', 'type' => 'success']);
    }

    public function archive($id)
    {
        $article = KbArticle::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $article->update(['status' => 'archived']);
        $this->dispatch('show-toast', ['message' => 'Article archived.', 'type' => 'success']);
    }

    public function delete($id)
    {
        $article = KbArticle::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $article->delete();
        $this->dispatch('show-toast', ['message' => 'Article deleted.', 'type' => 'success']);
    }

    public function render()
    {
        $query = KbArticle::where('company_id', Auth::user()->company_id)
            ->with('category');

        if (! empty($this->search)) {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if (! empty($this->status)) {
            $query->where('status', $this->status);
        }

        return view('livewire.dashboard.kb.articles-list', [
            'articles' => $query->latest()->paginate(15),
        ]);
    }
}
