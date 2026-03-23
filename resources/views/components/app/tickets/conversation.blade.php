@props([
    'replies',
    'ticket',
    'senderId',
    'showAiSuggestion',
    'aiTone',
    'attachments',
    'kbSearch' => '',
    'kbResults' => collect(),
    'aiSuggestionsEnabled' => false,
    'isTeammate' => false,
])

<div class="flex flex-col flex-1 min-h-0">
    {{-- Scrollable messages --}}
    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5 bg-zinc-50/40 dark:bg-zinc-950/40">
        {{-- Initial ticket description as first message --}}
        @if ($ticket->description)
            <div class="flex gap-3">
                <div
                    class="shrink-0 w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                    {{ strtoupper(substr($ticket->customer->name ?? ($ticket->customer_name ?? '?'), 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2 mb-1.5">
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $ticket->customer->name ?? $ticket->customer_name }}
                        </span>
                        <span class="text-xs text-zinc-400 dark:text-zinc-500">Customer</span>
                        <span
                            class="ml-auto text-xs text-zinc-400 dark:text-zinc-500">{{ $ticket->created_at->format('M d, g:i A') }}</span>
                    </div>
                    <div
                        class="bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-600 rounded-xl px-5 py-4">
                        <div
                            class="prose prose-sm prose-zinc dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                            {!! \Mews\Purifier\Facades\Purifier::clean($ticket->description) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @forelse ($replies as $reply)
            <x-app.tickets.message :reply="$reply" />
        @empty
            @if (!$ticket->description)
                <div class="flex flex-col items-center justify-center h-full min-h-[200px] text-center py-12">
                    <div
                        class="w-14 h-14 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
                        <flux:icon.chat-bubble-left-right class="w-7 h-7 text-zinc-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">No messages yet</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">The conversation will appear here once a
                        reply is sent</p>
                </div>
            @endif
        @endforelse
    </div>

    {{-- Reply / note area --}}
    @if ($isTeammate)
        <div
            class="shrink-0 border-t-2 border-amber-300 dark:border-amber-700/50 bg-amber-50/60 dark:bg-amber-900/10 p-4">
            <form wire:submit="addInternalNote">
                <div class="flex items-start gap-3">
                    <flux:icon.lock-closed class="w-4 h-4 text-amber-500 mt-3 shrink-0" />
                    <div class="flex-1">
                        <textarea wire:model="internalNote" rows="3"
                            placeholder="Add an internal note for {{ $ticket->assignedTo?->name ?? 'the assignee' }}..." required
                            class="w-full bg-white dark:bg-zinc-800 border border-amber-200 dark:border-amber-700/50 rounded-lg px-4 py-2.5 text-sm text-zinc-700 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 resize-none disabled:opacity-50"
                            wire:loading.attr="disabled"></textarea>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                <flux:icon.lock-closed class="w-3 h-3" />
                                Only visible to your team
                            </p>
                            <flux:button type="submit" variant="primary" size="sm" icon="plus"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="addInternalNote">Add Note</span>
                                <span wire:loading wire:target="addInternalNote">Adding...</span>
                            </flux:button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="shrink-0 border-t-2 border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <x-app.tickets.reply-form :ticket="$ticket" :senderId="$senderId" :showAiSuggestion="$showAiSuggestion" :aiTone="$aiTone"
                :attachments="$attachments" :kbSearch="$kbSearch" :kbResults="$kbResults" :aiSuggestionsEnabled="$aiSuggestionsEnabled" />
        </div>
    @endif
</div>
