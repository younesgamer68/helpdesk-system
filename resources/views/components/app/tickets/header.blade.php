@props(['ticket', 'state'])

@php
    $statusConfig = match ($ticket->status) {
        'open' => ['bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20', 'Open'],
        'on-hold', 'on_hold' => ['bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20', 'On Hold'],
        'resolved' => ['bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', 'Resolved'],
        'closed' => ['bg-zinc-500/10 text-zinc-500 dark:text-zinc-400 border-zinc-500/20', 'Closed'],
        'in_progress', 'in progress' => [
            'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
            'In Progress',
        ],
        'pending' => ['bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border-yellow-500/20', 'Pending'],
        default => ['bg-zinc-500/10 text-zinc-500 border-zinc-500/20', ucfirst($ticket->status)],
    };
    $priorityConfig = match ($ticket->priority) {
        'low' => ['bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', 'Low'],
        'medium' => ['bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20', 'Medium'],
        'high' => ['bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20', 'High'],
        'urgent' => ['bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20', 'Urgent'],
        default => ['bg-zinc-500/10 text-zinc-500 border-zinc-500/20', ucfirst($ticket->priority)],
    };
@endphp

<div
    class="shrink-0 flex items-center justify-between gap-4 px-6 py-3 bg-white dark:bg-zinc-900 border-b-2 border-zinc-300 dark:border-zinc-700">
    {{-- Left: back + breadcrumb + ticket identity --}}
    <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('tickets', ['company' => $ticket->company->slug]) }}"
            class="shrink-0 flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
            aria-label="Back to tickets">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700 shrink-0"></div>

        <div class="min-w-0">
            <div class="flex items-center gap-2 mb-0.5">
                <span class="text-[11px] font-semibold text-zinc-400 dark:text-zinc-500 font-mono tracking-wide">
                    #{{ $ticket->ticket_number }}
                </span>
                <span class="text-zinc-300 dark:text-zinc-700">&middot;</span>
                <span
                    class="text-[11px] text-zinc-400 dark:text-zinc-500">{{ $ticket->updated_at->diffForHumans() }}</span>
                <span class="text-zinc-300 dark:text-zinc-700">&middot;</span>
                <span class="text-[11px] text-zinc-400 dark:text-zinc-500">
                    via
                    {{ match ($ticket->source) {'widget' => 'Widget','agent' => 'Agent',default => 'Web form'} }}
                </span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 truncate max-w-[360px] xl:max-w-[520px]">
                    {{ $ticket->subject }}
                </h1>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $statusConfig[0] }}">
                    {{ $statusConfig[1] }}
                </span>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $priorityConfig[0] }}">
                    {{ $priorityConfig[1] }}
                </span>
                @if ($ticket->category)
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                        <flux:icon.tag variant="micro" class="w-3 h-3" />
                        {{ $ticket->category->name }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: primary actions --}}
    <div class="shrink-0 flex items-center gap-2">
        @if ($state !== 'resolved')
            <button wire:click="promptActionConfirmation('resolve')" wire:loading.attr="disabled" wire:target="resolve"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-75 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition shadow-sm">
                <svg wire:loading.remove wire:target="resolve" class="w-4 h-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <svg wire:loading wire:target="resolve" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span wire:loading.remove wire:target="resolve">Mark as Resolved</span>
                <span wire:loading wire:target="resolve">Resolving...</span>
            </button>
        @else
            <button wire:click="promptActionConfirmation('unresolve')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14L4 9m0 0l5-5M4 9h10a6 6 0 010 12h-1" />
                </svg>
                Unresolve
            </button>
        @endif
    </div>
</div>
