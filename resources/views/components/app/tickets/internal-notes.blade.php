@props(['notes', 'ticket'])

<div>
    <div class="p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Internal Notes</h2>
            <span class="text-sm text-zinc-500 dark:text-zinc-400">Visible only to your team</span>
        </div>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
            {{ $notes->count() }} notes
        </p>
    </div>

    <div class="p-6 space-y-6 max-h-[600px] overflow-y-auto">
        @forelse ($notes as $note)
            <x-app.tickets.message :reply="$note" :isInternal="true" />
        @empty
            <div class="text-center py-8">
                <p class="text-zinc-500 dark:text-zinc-400">No internal notes yet.</p>
            </div>
        @endforelse
    </div>

    <div class="p-6 border-t border-black/10 dark:border-white/10 bg-indigo-50/50 dark:bg-indigo-900/10">
        <form wire:submit="addInternalNote">
            <div class="relative">
                <textarea wire:model="internalNote" rows="3" placeholder="Add a new internal note..." required
                    class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-3 text-zinc-600 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none disabled:opacity-50"
                    wire:loading.attr="disabled"></textarea>
            </div>
            <div class="flex justify-end mt-3">
                <flux:button type="submit" variant="primary" icon="plus" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="addInternalNote">Add note</span>
                    <span wire:loading wire:target="addInternalNote">Adding...</span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
