<?php

namespace App\Livewire\Tickets\Kb;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Article Editor')]
class ArticleEditor extends Component
{
    public ?KbArticle $article = null;

    public $title = '';

    public $slug = '';

    public $body = '';

    public $status = 'draft';

    public $kb_category_id = '';

    public $tags = '';

    public $meta_description = '';

    public $schedule_publish_date = null;

    public $published_at = null;

    public function mount(?KbArticle $article = null)
    {
        if ($article && $article->exists) {
            // Ensure scoping
            if ($article->company_id !== Auth::user()->company_id) {
                abort(403);
            }

            $article->loadMissing('versions.creator');

            $this->article = $article;
            $this->title = $article->title;
            $this->slug = $article->slug;
            $this->body = $article->body;
            $this->status = $article->status;
            $this->kb_category_id = $article->kb_category_id;
            $this->meta_description = $article->meta_description;

            // Format dates for datetime-local input
            $this->schedule_publish_date = $article->schedule_publish_date ? \Carbon\Carbon::parse($article->schedule_publish_date)->format('Y-m-d\TH:i') : null;
            $this->published_at = $article->published_at;

            // Decode tags array to string
            $this->tags = $article->tags ? implode(', ', json_decode($article->tags, true)) : '';
        }
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'kb_category_id' => 'required|exists:kb_categories,id',
            'tags' => 'nullable|string',
            'meta_description' => 'nullable|string|max:500',
            'schedule_publish_date' => 'nullable|date',
        ];
    }

    public function saveDraft()
    {
        $this->status = 'draft';
        $this->save();
    }

    public function publish()
    {
        $this->status = 'published';
        $this->save();
    }

    public function save()
    {
        $this->validate();

        $tagsArray = $this->tags ? array_map('trim', explode(',', $this->tags)) : [];
        $tagsJson = json_encode(array_filter($tagsArray));

        $cleanBody = \Mews\Purifier\Facades\Purifier::clean($this->body); // Purifier

        // If publishing right now, un-schedule and set published_at
        if ($this->status === 'published' && ! $this->published_at) {
            $this->published_at = now();
            $this->schedule_publish_date = null;
        }

        $articleData = [
            'title' => $this->title,
            'slug' => $this->slug ?: Str::slug($this->title),
            'body' => $cleanBody,
            'status' => $this->status,
            'kb_category_id' => $this->kb_category_id,
            'tags' => $tagsJson,
            'meta_description' => $this->meta_description,
            'schedule_publish_date' => $this->schedule_publish_date,
            'published_at' => $this->published_at,
        ];

        if ($this->article && $this->article->exists) {
            $this->article->update($articleData);

            // Save version
            \App\Models\KbArticleVersion::create([
                'kb_article_id' => $this->article->id,
                'title' => $this->title,
                'body' => $cleanBody,
                'created_by' => Auth::id(),
            ]);

            $this->dispatch('show-toast', ['message' => 'Article updated successfully.', 'type' => 'success']);
        } else {
            $articleData['company_id'] = Auth::user()->company_id;
            $this->article = KbArticle::create($articleData);

            // Save initial version
            \App\Models\KbArticleVersion::create([
                'kb_article_id' => $this->article->id,
                'title' => $this->title,
                'body' => $cleanBody,
                'created_by' => Auth::id(),
            ]);

            $this->dispatch('show-toast', ['message' => 'Article created successfully.', 'type' => 'success']);
        }

        return redirect()->route('kb.articles', Auth::user()->company->slug);
    }

    public function revertToVersion($versionId)
    {
        $version = \App\Models\KbArticleVersion::findOrFail($versionId);

        // Ensure this version belongs to the current article
        if (! $this->article || $version->kb_article_id !== $this->article->id) {
            abort(403);
        }

        $this->title = $version->title;
        $this->body = $version->body;

        // Save the reverted version as a new version explicitly or just update current state?
        // Usually, reverting creates a new state that the user can optionally save.
        // For simplicity now, we just update the livewire properties.
        $this->dispatch('show-toast', ['message' => 'Reverted to selected version. Don\'t forget to save.', 'type' => 'info']);
    }

    public function render()
    {
        if ($this->article && $this->article->exists) {
            $this->article->loadMissing('versions.creator');
        }

        $categories = KbCategory::where('company_id', Auth::user()->company_id)->get();

        return view('livewire.tickets.kb.article-editor', [
            'categories' => $categories,
        ]);
    }
}
