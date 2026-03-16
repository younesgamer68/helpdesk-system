@props(['ticket', 'state'])

<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('tickets', ['company' => $ticket->company->slug]) }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:text-zinc-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Ticket
                        #{{ $ticket->ticket_number }}</span>
                    <span class="text-xs text-zinc-600 dark:text-zinc-400">Last updated
                        {{ $ticket->updated_at->diffForHumans() }}</span>
                </div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $ticket->subject }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if ($state !== 'resolved')
                <button wire:click="resolve" wire:confirm="Are you sure you want to mark this ticket as resolved?"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Mark as Resolved
                </button>
            @else
                <button wire:click="unresolve" wire:confirm="Are you sure you want to unresolve this ticket?"
                    class="px-4 py-2 bg-zinc-200 dark:bg-zinc-600 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-900 dark:text-white rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h10a8 8 0 018 8v2M3 10l3-3m0 0l3 3m-3-3v9" />
                    </svg>
                    Unresolve
                </button>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2">
        @php
            $statusBg = match ($ticket->status) {
                'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                'on-hold', 'on_hold' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                'resolved' => 'bg-green-500/10 text-green-400 border-green-500/20',
                'closed' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                'in_progress', 'in progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
            };

            $priorityBg = match ($ticket->priority) {
                'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
            };
        @endphp

        <span class="px-2.5 py-1 {{ $statusBg }} text-xs font-medium rounded-full border">
            {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
        </span>
        <span class="px-2.5 py-1 {{ $priorityBg }} text-xs font-medium rounded-full border">
            {{ ucfirst($ticket->priority) }}
        </span>

        @if ($ticket->category)
            <span
                class="px-2.5 py-1 text-zinc-700 dark:text-zinc-300 text-xs font-medium rounded-full border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800">
                <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ $ticket->category->name }}
            </span>
        @endif
    </div>
</div>
