<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class KbController extends Controller
{
    private function getCompany(string $slug): Company
    {
        return Company::where('slug', $slug)->firstOrFail();
    }

    public function index(Request $request, string $companySlug)
    {
        $company = $this->getCompany($companySlug);
        $articlesEndpoint = route('api.kb.articles', ['company_slug' => $company->slug]);

        return response()->json([
            'company' => [
                'name' => $company->name,
                'slug' => $company->slug,
            ],
            'endpoints' => [
                'articles' => $articlesEndpoint,
                'article' => $articlesEndpoint.'/{slug}',
                'search' => route('api.kb.search', ['company_slug' => $company->slug]).'?q={query}',
            ],
        ]);
    }

    public function articles(Request $request, string $companySlug)
    {
        $company = $this->getCompany($companySlug);

        $articles = $company->kbArticles()
            ->where('status', 'published')
            ->with('category')
            ->latest()
            ->paginate(request('per_page', 10));

        return response()->json($articles);
    }

    public function article(Request $request, string $companySlug, string $slug)
    {
        $company = $this->getCompany($companySlug);

        $article = $company->kbArticles()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with('category')
            ->firstOrFail();

        // Optional: increment views here or not (maybe track "widget" views specifically)
        // $article->increment('views');

        return response()->json($article);
    }

    public function search(Request $request, string $companySlug)
    {
        $company = $this->getCompany($companySlug);
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $articles = $company->kbArticles()
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%")
                    ->orWhere('tags', 'like', "%{$query}%");
            })
            ->with('category')
            ->limit(10)
            ->get();

        return response()->json(['data' => $articles]);
    }
}
