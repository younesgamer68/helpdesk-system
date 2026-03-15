<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;

class KbPortalController extends Controller
{
    private function getCompany($slug)
    {
        return Company::where('slug', $slug)->firstOrFail();
    }

    public function home(Request $request, $companySlug)
    {
        $company = $this->getCompany($companySlug);

        $categories = $company->kbCategories()->withCount(['articles' => function ($query) {
            $query->where('status', 'published');
        }])->get();

        $popularArticles = $company->kbArticles()
            ->where('status', 'published')
            ->orderByDesc('views')
            ->take(5)
            ->get();

        return view('kb.home', compact('company', 'categories', 'popularArticles'));
    }

    public function category(Request $request, $companySlug, KbCategory $category)
    {
        $company = $this->getCompany($companySlug);

        abort_unless($category->company_id === $company->id, 404);

        $articles = $category->articles()
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

        // Increment Views (simple mechanism, could be improved with session/IP checks)
        $article->increment('views');

        $relatedArticles = $article->category ? $article->category->articles()
            ->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->take(5)
            ->get() : collect();

        return view('kb.article', compact('company', 'article', 'relatedArticles'));
    }

    public function search(Request $request, $companySlug)
    {
        $company = $this->getCompany($companySlug);
        $query = $request->input('q');

        $articles = $company->kbArticles()
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%");
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
}
