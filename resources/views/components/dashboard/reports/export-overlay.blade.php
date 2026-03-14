@props([])

<div x-show="exportingPdf" x-cloak x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="export-overlay fixed bottom-6 right-6 z-50 bg-zinc-900 dark:bg-zinc-800 text-white rounded-xl px-5 py-3.5 flex items-center gap-3 shadow-xl border border-zinc-700">
    <svg class="animate-spin size-4 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
    </svg>
    <span class="text-sm font-medium">Generating PDF…</span>
</div>
