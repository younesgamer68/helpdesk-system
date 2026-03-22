@props(['notes', 'ticket'])

<div class="flex flex-col flex-1 min-h-0">
    {{-- Header --}}
    <div
        class="shrink-0 flex items-center justify-between px-6 py-3.5 bg-white dark:bg-zinc-900 border-b-2 border-zinc-300 dark:border-zinc-700">
        <div class="flex items-center gap-2">
            <flux:icon.lock-closed class="w-4 h-4 text-amber-500" />
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Internal Notes</h2>
            @if ($notes->count() > 0)
                <span
                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    {{ $notes->count() }}
                </span>
            @endif
        </div>
        <p class="text-xs text-zinc-400 dark:text-zinc-500">Visible only to your team</p>
    </div>

    {{-- Notes list --}}
    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5 bg-zinc-50/40 dark:bg-zinc-950/40">
        @forelse ($notes as $note)
            <x-app.tickets.message :reply="$note" :isInternal="true" />
        @empty
            <div class="flex flex-col items-center justify-center h-full min-h-[200px] text-center py-12">
                <div
                    class="w-14 h-14 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center mb-4">
                    <flux:icon.lock-closed class="w-7 h-7 text-amber-400 dark:text-amber-500" />
                </div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">No internal notes yet</p>
                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Notes added here are only visible to your team
                </p>
            </div>
        @endforelse
    </div>

    {{-- Add note form --}}
    <div class="shrink-0 border-t border-amber-200 dark:border-amber-800/40 bg-amber-50/40 dark:bg-amber-900/10 p-4">
        <form wire:submit="addInternalNote">
            <textarea wire:model="internalNote" rows="3" placeholder="Write an internal note... (visible only to your team)"
                required
                class="w-full bg-white dark:bg-zinc-800 border border-amber-200 dark:border-amber-700/50 rounded-xl px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 resize-none disabled:opacity-50"
                wire:loading.attr="disabled"></textarea>
            <div class="flex items-center justify-between mt-3">
                <p class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                    <flux:icon.lock-closed class="w-3 h-3" />
                    Only your team can see this
                </p>
                <flux:button type="submit" size="sm" icon="plus" wire:loading.attr="disabled"
                    class="bg-amber-600 hover:bg-amber-700 text-white">
                    <span wire:loading.remove wire:target="addInternalNote">Add note</span>
                    <span wire:loading wire:target="addInternalNote">Adding...</span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
