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

<div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Conversation</h2>
            <span class="text-sm text-zinc-500 dark:text-zinc-400">Visible to customer</span>
        </div>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
            {{ $replies->count() }} messages
        </p>
    </div>

    <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
        @forelse ($replies as $reply)
            <x-app.tickets.message :reply="$reply" />
        @empty
            <div class="text-center py-8">
                <p class="text-zinc-500 dark:text-zinc-400">No messages yet.</p>
            </div>
        @endforelse
    </div>

    @if ($isTeammate)
        {{-- Teammate: internal note box only --}}
        <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 bg-amber-50/50 dark:bg-amber-900/10">
            <form wire:submit="addInternalNote">
                <textarea wire:model="internalNote" rows="3"
                    placeholder="Add a note for {{ $ticket->assignedTo?->name ?? 'the assignee' }}..." required
                    class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-3 text-zinc-600 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 resize-none disabled:opacity-50"
                    wire:loading.attr="disabled"></textarea>
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
                        <flux:icon.lock-closed class="w-3.5 h-3.5" />
                        Only visible to your team
                    </div>
                    <flux:button type="submit" variant="primary" icon="plus" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="addInternalNote">Add Note</span>
                        <span wire:loading wire:target="addInternalNote">Adding...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    @else
        <x-app.tickets.reply-form :ticket="$ticket" :senderId="$senderId" :showAiSuggestion="$showAiSuggestion" :aiTone="$aiTone"
            :attachments="$attachments" :kbSearch="$kbSearch" :kbResults="$kbResults" :aiSuggestionsEnabled="$aiSuggestionsEnabled" />
    @endif
</div>
