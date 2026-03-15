@extends('kb.layout')

@section('title', $category->name)
@section('meta_description', $category->description)

@section('hero')
<div class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 pt-8 pb-12 shadow-sm">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-zinc-500 dark:text-zinc-400 mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('kb.public.home', $company->slug) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition">Home</a>
                </li>
                <li>
                    <svg class="h-4 w-4 text-zinc-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </li>
                @if($category->parent)
                <li>
                    <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $category->parent->id]) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition">{{ $category->parent->name }}</a>
                </li>
                <li>
                    <svg class="h-4 w-4 text-zinc-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </li>
                @endif
                <li class="text-zinc-900 dark:text-zinc-100 font-medium" aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>

        <div class="flex flex-col sm:flex-row gap-6 items-start mt-4">
            <div class="w-16 h-16 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 border border-teal-100 dark:border-teal-800 shadow-sm">
                @if($category->icon)
                    <span class="text-3xl">{{ $category->icon }}</span>
                @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                @endif
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 mb-2">{{ $category->name }}</h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400 max-w-3xl leading-relaxed">{{ $category->description }}</p>
            </div>
        </div>

    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
    
    <!-- Sidebar / Subcategories -->
    <div class="lg:col-span-1 space-y-8">
        @if($category->children->count() > 0)
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-4 px-2">Subcategories</h3>
            <ul class="space-y-2">
                @foreach($category->children as $child)
                    <li>
                        <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $child->id]) }}" class="block px-4 py-3 rounded-xl border border-transparent hover:border-zinc-200 dark:hover:border-zinc-800 hover:bg-white dark:hover:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:text-teal-600 dark:hover:text-teal-400 font-medium transition shadow-sm hover:shadow group">
                            <div class="flex items-center justify-between">
                                <span class="truncate">{{ $child->name }}</span>
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs text-zinc-500 bg-zinc-100 dark:bg-zinc-800 rounded-full group-hover:bg-teal-50 group-hover:text-teal-600 transition">{{ $child->articles()->count() }}</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm">
            <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/50 text-teal-600 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-lg mb-2">Need more help?</h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Can't find the answers you're looking for? Our support team is here to help.</p>
            <a href="https{{ $company->website ? '://' . str_replace(['http://', 'https://'], '', $company->website) : '#' }}" class="inline-flex w-full justify-center items-center gap-2 py-3 px-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 rounded-xl text-sm font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-100 transition shadow">
                Contact Support
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </div>

    <!-- Articles List -->
    <div class="lg:col-span-3">
        
        @if($articles->count() > 0)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm">
                <ul class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($articles as $article)
                        <li>
                            <a href="{{ route('kb.public.article', ['company' => $company->slug, 'article' => $article->slug]) }}" class="block hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition p-6 sm:p-8 group relative">
                                <div class="flex items-start gap-5">
                                    <div class="mt-1 text-zinc-300 dark:text-zinc-700 group-hover:text-teal-500 dark:group-hover:text-teal-400 transition shrink-0">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0 pr-8">
                                        <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition mb-2">{{ $article->title }}</h3>
                                        <p class="text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed">{{ $article->meta_description ?? strip_tags(Str::limit($article->body, 200)) }}</p>
                                        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-medium tracking-wide text-zinc-500">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $article->updated_at->diffForHumans() }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                {{ number_format($article->views) }} views
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition text-teal-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @else
            <div class="text-center py-20 px-6 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-3xl bg-zinc-50 dark:bg-zinc-900/50">
                <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">No articles found</h3>
                <p class="mt-2 text-zinc-500 max-w-sm mx-auto">It looks like we haven't published any articles in this category yet. Check back soon!</p>
            </div>
        @endif

    </div>

</div>
@endsection
