@extends('kb.layout')

@section('title', $article->title)
@section('meta_description', $article->meta_description ?? strip_tags(Str::limit($article->body, 150)))

@section('hero')
<div class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 pt-8 pb-12 shadow-sm">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap text-sm text-zinc-500 dark:text-zinc-400 mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('kb.public.home', $company->slug) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition">Home</a>
                </li>
                <li>
                    <svg class="h-4 w-4 text-zinc-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </li>
                @if($article->category)
                    @if($article->category->parent)
                    <li>
                        <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $article->category->parent->id]) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition">{{ $article->category->parent->name }}</a>
                    </li>
                    <li>
                        <svg class="h-4 w-4 text-zinc-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $article->category->id]) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition">{{ $article->category->name }}</a>
                    </li>
                    <li>
                        <svg class="h-4 w-4 text-zinc-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                    </li>
                @endif
                <li class="text-zinc-900 dark:text-zinc-100 font-medium truncate max-w-xs" aria-current="page">{{ $article->title }}</li>
            </ol>
        </nav>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-zinc-900 dark:text-zinc-100 mb-6 leading-tight">{{ $article->title }}</h1>
        
        <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Updated {{ $article->updated_at->diffForHumans() }}
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                {{ number_format($article->views) }} views
            </span>
        </div>

    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="articleVote('{{ $article->slug }}', '{{ $company->slug }}')">
    
    <!-- Article Body -->
    <article class="prose prose-zinc dark:prose-invert max-w-none prose-img:rounded-xl prose-img:shadow-sm prose-headings:font-bold prose-a:text-teal-600 dark:prose-a:text-teal-400 hover:prose-a:text-teal-500 mb-12 bg-white dark:bg-zinc-900 p-8 sm:p-12 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800">
        {!! $article->body !!}
    </article>

    <!-- Was this helpful? Section -->
    <div class="border-t border-zinc-200 dark:border-zinc-800 pt-8 pb-16 flex flex-col items-center justify-center text-center">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-6 tracking-tight">Was this article helpful?</h3>
        
        <!-- Buttons container -->
        <div x-show="!voted && !error" x-transition.opacity class="flex items-center gap-4">
            <button @click="vote('yes')" class="flex items-center gap-2 px-6 py-2.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-medium hover:bg-zinc-50 dark:hover:bg-zinc-700 transition shadow-sm hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 group">
                <svg class="w-5 h-5 text-zinc-400 group-hover:text-teal-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                Yes
            </button>
            <button @click="vote('no')" class="flex items-center gap-2 px-6 py-2.5 rounded-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-medium hover:bg-zinc-50 dark:hover:bg-zinc-700 transition shadow-sm hover:border-red-500 hover:text-red-600 dark:hover:text-red-400 group">
                <svg class="w-5 h-5 text-zinc-400 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path></svg>
                No
            </button>
        </div>

        <!-- Success Message -->
        <div x-show="voted" x-transition.opacity class="flex items-center gap-2 text-teal-600 dark:text-teal-400 font-medium bg-teal-50 dark:bg-teal-900/30 px-6 py-3 rounded-full" style="display: none;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Thank you for your feedback! It helps us improve.
        </div>
        
        <!-- Error Message -->
        <div x-show="error" x-transition.opacity class="text-zinc-500 dark:text-zinc-400 text-sm mt-4 bg-zinc-100 dark:bg-zinc-800 px-4 py-2 rounded-lg" style="display: none;" x-text="error"></div>
    </div>


    <!-- Related Articles -->
    @if($relatedArticles->count() > 0)
    <div class="mt-8">
        <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mb-6">Related Articles</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($relatedArticles as $related)
                <a href="{{ route('kb.public.article', ['company' => $company->slug, 'article' => $related->slug]) }}" class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:border-teal-500 dark:hover:border-teal-500 shadow-sm hover:shadow transition group">
                    <h4 class="font-medium text-zinc-900 dark:text-zinc-100 mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">{{ $related->title }}</h4>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">{{ $related->meta_description ?? strip_tags(Str::limit($related->body, 100)) }}</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('articleVote', (articleSlug, companySlug) => ({
            voted: false,
            error: null,
            vote(type) {
                fetch(`/kb/${companySlug}/article/${articleSlug}/vote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ vote: type })
                })
                .then(response => {
                    if (response.ok) {
                        this.voted = true;
                    } else if (response.status === 400) {
                        this.error = "You have already voted on this article.";
                    } else {
                        throw new Error('Something went wrong');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.error = "Could not record your vote. Please try again later.";
                });
            }
        }))
    })
</script>
@endsection
