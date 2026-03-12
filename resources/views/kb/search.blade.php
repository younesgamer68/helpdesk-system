@extends('kb.layout')

@section('title', 'Search Results for "' . $query . '"')

@section('hero')
<div class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 pt-8 pb-12 shadow-sm">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-zinc-500 dark:text-zinc-400 mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('kb.public.home', $company->slug) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition">Home</a>
                </li>
                <li>
                    <svg class="h-4 w-4 text-zinc-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </li>
                <li class="text-zinc-900 dark:text-zinc-100 font-medium" aria-current="page">Search Results</li>
            </ol>
        </nav>

        <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 mb-6">Search Results for "{{ $query }}"</h1>
        
        <!-- Search Bar Again -->
        <form action="{{ route('kb.public.search', $company->slug) }}" method="GET" class="relative max-w-2xl">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="q" value="{{ $query }}" placeholder="Search for articles..." class="block w-full pl-11 pr-4 py-3 rounded-xl border border-zinc-300 dark:border-zinc-700 focus:ring-2 focus:ring-teal-500 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 outline-none transition shadow-sm">
        </form>

    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 pb-4">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $articles->total() }} results found</h2>
    </div>

    @if($articles->count() > 0)
        <div class="space-y-6">
            @foreach($articles as $article)
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 sm:p-8 border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition group relative">
                    <a href="{{ route('kb.public.article', ['company' => $company->slug, 'article' => $article->slug]) }}" class="absolute inset-0 z-10"><span class="sr-only">View Article</span></a>
                    
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center text-xs font-medium tracking-wider text-teal-600 dark:text-teal-400 mb-3 uppercase">
                        @if($article->category)
                            <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $article->category->id]) }}" class="relative z-20 hover:text-teal-700 dark:hover:text-teal-300 transition">{{ $article->category->name }}</a>
                        @else
                            <span>Uncategorized</span>
                        @endif
                    </div>
                    
                    <h3 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition mb-3 line-clamp-2">
                        {{ $article->title }}
                    </h3>
                    
                    <p class="text-zinc-600 dark:text-zinc-400 line-clamp-3 leading-relaxed mb-4">
                        {{ $article->meta_description ?? strip_tags($article->body) }}
                    </p>
                    
                    <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-500">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Updated {{ $article->updated_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-10">
            {{ $articles->appends(['q' => $query])->links() }}
        </div>
    @else
        <div class="text-center py-24 px-6 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-3xl bg-zinc-50 dark:bg-zinc-900/50">
            <div class="w-20 h-20 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">No results found for "{{ $query }}"</h3>
            <p class="mt-3 text-zinc-500 max-w-md mx-auto leading-relaxed">We couldn't find any articles matching your search. Please try checking your spelling or use different keywords.</p>
            
            <div class="mt-8">
                <a href="{{ route('kb.public.home', $company->slug) }}" class="inline-flex justify-center items-center gap-2 py-3 px-6 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition shadow-sm">
                    Return to Home
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
