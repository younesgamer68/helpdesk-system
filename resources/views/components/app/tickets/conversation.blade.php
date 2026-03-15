@props([
    'replies',
    'ticket',
    'senderId',
    'showAiSuggestion',
    'aiTone',
    'attachments'
])

<div
    class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
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

    <x-app.tickets.reply-form 
        :ticket="$ticket" 
        :senderId="$senderId" 
        :showAiSuggestion="$showAiSuggestion" 
        :aiTone="$aiTone" 
        :attachments="$attachments" 
    />
</div>
