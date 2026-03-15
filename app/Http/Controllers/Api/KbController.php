<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

class KbController extends Controller
{
    private function getCompany($slug)
    {
        return Company::where('slug', $slug)->firstOrFail();
    }

    public function articles(Request $request, $companySlug)
    {
        $company = $this->getCompany($companySlug);
        
        $articles = $company->kbArticles()
            ->where('status', 'published')
            ->with('category')
            ->latest()
            ->paginate(request('per_page', 10));

        return response()->json($articles);
    }

    public function article(Request $request, $companySlug, $slug)
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

    public function search(Request $request, $companySlug)
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
