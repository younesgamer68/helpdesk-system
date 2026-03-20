@extends('kb.layout')

@section('title', $article->title)
@section('meta_description', $article->meta_description ?? strip_tags(Str::limit($article->body, 150)))

@section('hero')
    <div class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-10">

            <!-- Breadcrumbs -->
            <nav class="flex flex-wrap text-sm text-zinc-500 dark:text-zinc-400 mb-6" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-1.5">
                    <li>
                        <a href="{{ route('kb.public.home', $company->slug) }}"
                            class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                        </a>
                    </li>
                    @if ($article->category)
                        @if ($article->category->parent)
                            <li class="flex items-center">
                                <svg class="h-4 w-4 text-zinc-300 dark:text-zinc-600" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $article->category->parent->id]) }}"
                                    class="ml-1.5 hover:text-emerald-600 dark:hover:text-emerald-400 transition">{{ $article->category->parent->name }}</a>
                            </li>
                        @endif
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-zinc-300 dark:text-zinc-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('kb.public.category', ['company' => $company->slug, 'category' => $article->category->id]) }}"
                                class="ml-1.5 hover:text-emerald-600 dark:hover:text-emerald-400 transition">{{ $article->category->name }}</a>
                        </li>
                    @endif
                </ol>
            </nav>

            @if ($article->category)
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 mb-4">
                    {{ $article->category->name }}
                </span>
            @endif

            <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-zinc-100 leading-tight mb-4">
                {{ $article->title }}</h1>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Updated {{ $article->updated_at->format('M j, Y') }}
                </span>
                <span class="hidden sm:inline text-zinc-300 dark:text-zinc-600">&bull;</span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                    {{ number_format($article->views) }} views
                </span>
                @php
                    $wordCount = str_word_count(strip_tags($article->body));
                    $readTime = max(1, (int) ceil($wordCount / 200));
                @endphp
                <span class="hidden sm:inline text-zinc-300 dark:text-zinc-600">&bull;</span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    {{ $readTime }} min read
                </span>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto" x-data="articleVote('{{ $article->slug }}', '{{ $company->slug }}')">

        <!-- Article Body -->
        <article
            class="prose prose-lg prose-zinc dark:prose-invert max-w-none
        prose-headings:font-bold prose-headings:tracking-tight
        prose-h2:text-2xl prose-h2:mt-10 prose-h2:mb-4 prose-h2:pb-2 prose-h2:border-b prose-h2:border-zinc-200 dark:prose-h2:border-zinc-800
        prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-3
        prose-p:leading-relaxed prose-p:text-zinc-700 dark:prose-p:text-zinc-300
        prose-a:text-emerald-600 dark:prose-a:text-emerald-400 prose-a:no-underline hover:prose-a:underline
        prose-img:rounded-xl prose-img:shadow-md prose-img:border prose-img:border-zinc-200 dark:prose-img:border-zinc-700
        prose-blockquote:border-emerald-500 prose-blockquote:bg-zinc-50 dark:prose-blockquote:bg-zinc-800/50 prose-blockquote:rounded-r-lg prose-blockquote:py-1 prose-blockquote:not-italic
        prose-code:before:content-[''] prose-code:after:content-[''] prose-code:bg-zinc-100 dark:prose-code:bg-zinc-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm prose-code:font-medium
        prose-pre:bg-zinc-900 dark:prose-pre:bg-zinc-950 prose-pre:border prose-pre:border-zinc-200 dark:prose-pre:border-zinc-700 prose-pre:rounded-xl
        prose-li:marker:text-emerald-500
        prose-hr:border-zinc-200 dark:prose-hr:border-zinc-800
        bg-white dark:bg-zinc-900 p-8 sm:p-10 lg:p-12 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800">
            {!! $article->body !!}
        </article>

        <!-- Was this helpful? Section -->
        <div class="mt-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-8 shadow-sm">
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Was this article helpful?</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6">Let us know if this answered your question</p>

                <!-- Buttons container -->
                <div x-show="!voted && !error" x-transition.opacity class="flex items-center gap-3">
                    <button @click="vote('yes')"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-medium hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all group">
                        <svg class="w-5 h-5 text-zinc-400 group-hover:text-emerald-500 transition" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5">
                            </path>
                        </svg>
                        Yes, helpful
                    </button>
                    <button @click="vote('no')"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-medium hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-all group">
                        <svg class="w-5 h-5 text-zinc-400 group-hover:text-red-500 transition" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5">
                            </path>
                        </svg>
                        Not helpful
                    </button>
                </div>

                <!-- Success Message -->
                <div x-show="voted" x-transition.opacity
                    class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-medium bg-emerald-50 dark:bg-emerald-900/30 px-6 py-3 rounded-xl"
                    style="display: none;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Thank you for your feedback!
                </div>

                <!-- Error Message -->
                <div x-show="error" x-transition.opacity
                    class="text-zinc-500 dark:text-zinc-400 text-sm mt-4 bg-zinc-100 dark:bg-zinc-800 px-4 py-2 rounded-lg"
                    style="display: none;" x-text="error"></div>
            </div>
        </div>

        <!-- Related Articles -->
        @if ($relatedArticles->count() > 0)
            <div class="mt-8">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                        </path>
                    </svg>
                    Related Articles
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($relatedArticles as $related)
                        <a href="{{ route('kb.public.article', ['company' => $company->slug, 'article' => $related->slug]) }}"
                            class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:border-emerald-400 dark:hover:border-emerald-600 shadow-sm hover:shadow transition-all group">
                            <h4
                                class="font-medium text-zinc-900 dark:text-zinc-100 mb-1.5 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition line-clamp-2">
                                {{ $related->title }}</h4>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                {{ $related->meta_description ?? strip_tags(Str::limit($related->body, 100)) }}</p>
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
                    fetch(`/kb/article/${articleSlug}/vote`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                vote: type
                            })
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
