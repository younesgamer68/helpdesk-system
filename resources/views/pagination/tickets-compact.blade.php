@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';

    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();

    $visiblePages = collect([1, 2, 3, $currentPage, $lastPage])
        ->filter(fn (int $page): bool => $page >= 1 && $page <= $lastPage)
        ->unique()
        ->sort()
        ->values();
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            Viewing <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $paginator->total() }}</span>
            results
        </p>

        <div class="flex items-center gap-1 text-sm">
            @if ($paginator->onFirstPage())
                <span class="px-2 py-1 text-zinc-400 dark:text-zinc-600">Prev</span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled"
                    class="cursor-pointer rounded-md px-2 py-1 text-zinc-500 transition-colors hover:text-teal-600 dark:text-zinc-400 dark:hover:text-teal-400"
                    aria-label="{{ __('pagination.previous') }}">
                    Prev
                </button>
            @endif

            @php($previousShownPage = null)
            @foreach ($visiblePages as $page)
                @if (! is_null($previousShownPage) && ($page - $previousShownPage) > 1)
                    <span class="px-1 text-zinc-400 dark:text-zinc-600">...</span>
                @endif

                @if ($page === $currentPage)
                    <span aria-current="page"
                        class="inline-flex min-w-7 items-center justify-center rounded-md bg-teal-500 px-2 py-1 text-xs font-medium text-white dark:bg-teal-500 dark:text-white">
                        {{ $page }}
                    </span>
                @else
                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        class="cursor-pointer rounded-md px-2 py-1 text-zinc-500 transition-colors hover:text-teal-600 dark:text-zinc-400 dark:hover:text-teal-400"
                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                        {{ $page }}
                    </button>
                @endif

                @php($previousShownPage = $page)
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled"
                    class="cursor-pointer rounded-md px-2 py-1 text-zinc-500 transition-colors hover:text-teal-600 dark:text-zinc-400 dark:hover:text-teal-400"
                    aria-label="{{ __('pagination.next') }}">
                    Next
                </button>
            @else
                <span class="px-2 py-1 text-zinc-400 dark:text-zinc-600">Next</span>
            @endif
        </div>
    </nav>
@endif
