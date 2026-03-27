<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\KbArticle;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbPortalController extends Controller
{
    private function getCompany($slug)
    {
        return Company::where('slug', $slug)->firstOrFail();
    }

    public function home(Request $request, $companySlug)
    {
        $company = $this->getCompany($companySlug);

        $categories = $company->categories()
            ->whereNull('parent_id')
            ->withCount(['kbArticles' => function ($query) {
                $query->where('status', 'published');
            }])
            ->with(['children' => function ($query) {
                $query->withCount(['kbArticles' => function ($q) {
                    $q->where('status', 'published');
                }])->orderBy('name');
            }])
            ->get();

        $popularArticles = $company->kbArticles()
            ->where('status', 'published')
            ->orderByDesc('views')
            ->take(5)
            ->get();

        return view('kb.home', compact('company', 'categories', 'popularArticles'));
    }

    public function category(Request $request, $companySlug, TicketCategory $category)
    {
        $company = $this->getCompany($companySlug);

        abort_unless($category->company_id === $company->id, 404);

        $category->load(['parent', 'children']);

        $articles = $category->kbArticles()
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        return view('kb.category', compact('company', 'category', 'articles'));
    }

    public function article(Request $request, $companySlug, KbArticle $article)
    {
        $company = $this->getCompany($companySlug);

        abort_unless($article->company_id === $company->id, 404);
        abort_unless($article->status === 'published', 404);

        $article->loadMissing('category.parent');

        // Increment Views (simple mechanism, could be improved with session/IP checks)
        $article->increment('views');

        $relatedArticles = $article->category ? $article->category->kbArticles()
            ->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->with('category')
            ->take(5)
            ->get() : collect();

        return view('kb.article', compact('company', 'article', 'relatedArticles'));
    }

    public function search(Request $request, $companySlug)
    {
        $company = $this->getCompany($companySlug);
        $query = trim((string) $request->input('q', ''));
        $searchTerms = collect(preg_split('/\s+/', Str::lower($query)))->filter()->unique()->values();

        $articles = $company->kbArticles()
            ->with('category')
            ->where('status', 'published')
            ->where(function ($q) use ($query, $searchTerms) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%");

                if ($searchTerms->isNotEmpty()) {
                    foreach ($searchTerms as $term) {
                        $q->orWhereJsonContains('tags', $term);
                    }
                }
            })
            ->latest()
            ->paginate(15);

        return view('kb.search', compact('company', 'articles', 'query'));
    }

    public function vote(Request $request, $companySlug, KbArticle $article)
    {
        $company = $this->getCompany($companySlug);

        abort_unless($article->company_id === $company->id, 404);

        $voteType = $request->input('vote'); // 'yes' or 'no'
        $cookieName = 'kb_vote_'.$article->id;

        if ($request->hasCookie($cookieName)) {
            return response()->json(['message' => 'Already voted'], 400);
        }

        if ($voteType === 'yes') {
            $article->increment('helpful_yes');
        } elseif ($voteType === 'no') {
            $article->increment('helpful_no');
        }

        return response()->json(['message' => 'Vote recorded'])
            ->withCookie(cookie()->forever($cookieName, $voteType));
    }

    public function widgetDemo(Request $request, $companySlug): \Illuminate\View\View
    {
        $company = $this->getCompany($companySlug);

        $widgetVersion = filemtime(resource_path('views/kb/widget-js.blade.php')) ?: time();
        $widgetScriptUrl = route('kb.public.widget', ['company' => $company->slug]).'?v='.$widgetVersion;
        $widgetDefaultLinkMode = $company->kb_widget_link_mode === 'custom' ? 'custom' : 'portal';
        $widgetArticleBaseUrl = filled($company->kb_widget_article_base_url)
            ? rtrim((string) $company->kb_widget_article_base_url, '/')
            : null;

        return view('kb.widget-demo', compact('company', 'widgetScriptUrl', 'widgetDefaultLinkMode', 'widgetArticleBaseUrl'));
    }
}
