@extends('kb.layout')

@section('hero')
<div class="bg-teal-600 dark:bg-teal-900 border-b border-teal-700/50 dark:border-teal-800/50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mb-4 text-center">How can we help you today?</h1>
        <p class="text-teal-100 sm:text-xl text-center max-w-2xl mx-auto mb-8">Search our knowledge base or browse categories below to find answers to your questions.</p>
        
        <form action="{{ route('kb.public.search', $company->slug) }}" method="GET" class="relative max-w-2xl mx-auto">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="q" placeholder="Search for articles..." class="block w-full pl-12 pr-24 py-4 rounded-xl border-0 focus:ring-4 focus:ring-teal-500/30 text-zinc-900 bg-white shadow-lg text-lg outline-none">
            <div class="absolute inset-y-0 right-2 flex items-center">
                <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-2 rounded-lg font-medium transition shadow-sm">Search</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-16">

    <!-- Categories -->
    <section>
        <h2 class="text-2xl font-bold mb-8 text-zinc-900 dark:text-zinc-100">Browse by Topic</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($categories as $category)
                <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $category->id]) }}" class="group bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 hover:border-teal-500 dark:hover:border-teal-500 shadow-sm hover:shadow-md transition">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                            @if($category->icon)
                                <span class="text-2xl">{{ $category->icon }}</span>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">{{ $category->name }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-2">{{ $category->description }}</p>
                            <p class="text-xs font-medium text-teal-600 dark:text-teal-500 mt-3">{{ $category->articles_count }} articles &rarr;</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-12 text-center text-zinc-500 dark:text-zinc-400">
                    <p>No topics have been created yet.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Popular Articles -->
    @if($popularArticles->count() > 0)
    <section>
        <h2 class="text-2xl font-bold mb-8 text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
            <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            Popular Articles
        </h2>
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm">
            <ul class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @foreach($popularArticles as $article)
                    <li>
                        <a href="{{ route('kb.public.article', ['company' => $company->slug, 'article' => $article->slug]) }}" class="block hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition p-4 sm:p-6 group">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0 pr-4">
                                    <h4 class="text-lg font-medium text-teal-600 dark:text-teal-400 group-hover:underline truncate">{{ $article->title }}</h4>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 flex items-center gap-2">
                                        <span class="inline-block flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> {{ number_format($article->views) }} views</span>
                                        &bull;
                                        <span>{{ strip_tags(Str::limit($article->body, 120)) }}</span>
                                    </p>
                                </div>
                                <div class="shrink-0 flex items-center text-zinc-400 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
    @endif

</div>
@endsection
