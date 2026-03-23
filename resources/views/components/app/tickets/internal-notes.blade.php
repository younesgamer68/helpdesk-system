@props(['notes', 'ticket'])

<div class="flex flex-col flex-1 min-h-0">
    {{-- Header --}}


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
        <form wire:submit="addInternalNote" x-data="{
            showMentions: false,
            mentionQuery: '',
            allTeammates: @js($this->availableTeammates),
            mentionChips: [],
            get filteredTeammates() {
                if (!this.mentionQuery) return this.allTeammates;
                const q = this.mentionQuery.toLowerCase();
                return this.allTeammates.filter(t => t.name.toLowerCase().includes(q));
            },
            removeMention(chipId) {
                this.mentionChips = this.mentionChips.filter(c => c.id !== chipId);
                $wire.set('mentionedUserIds', this.mentionChips.map(c => c.id));
            },
            selectMention(result) {
                $wire.addMentionedUser(result.id);
                this.mentionChips.push({ id: result.id, name: result.name });
                this.showMentions = false;
                this.mentionQuery = '';
                const textarea = this.$refs.noteInput;
                const val = textarea.value;
                const atIndex = val.lastIndexOf('@');
                if (atIndex !== -1) {
                    textarea.value = val.substring(0, atIndex) + '@' + result.name + ' ';
                    $wire.set('internalNote', textarea.value);
                }
                textarea.focus();
            }
        }">

            {{-- Mention chips --}}
            <template x-if="mentionChips.length > 0">
                <div class="flex flex-wrap gap-1.5 mb-2">
                    <template x-for="chip in mentionChips" :key="chip.id">
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50">
                            <span x-text="'@' + chip.name"></span>
                            <button type="button" @click="removeMention(chip.id)"
                                class="hover:text-red-500 transition-colors">&times;</button>
                        </span>
                    </template>
                </div>
            </template>

            {{-- Textarea with mention detection --}}
            <div class="relative">
                <textarea x-ref="noteInput" wire:model="internalNote" rows="3"
                    placeholder="Write an internal note... Use @ to mention teammates" required
                    class="w-full bg-white dark:bg-zinc-800 border border-amber-200 dark:border-amber-700/50 rounded-xl px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 resize-none disabled:opacity-50"
                    wire:loading.attr="disabled"
                    @input="
                        const val = $event.target.value;
                        const atIndex = val.lastIndexOf('@');
                        if (atIndex !== -1) {
                            const query = val.substring(atIndex + 1);
                            if (query.length >= 0 && !query.includes(' ')) {
                                showMentions = true;
                                mentionQuery = query;
                            } else {
                                showMentions = false;
                                mentionQuery = '';
                            }
                        } else {
                            showMentions = false;
                            mentionQuery = '';
                        }
                    "
                    @keydown.escape="showMentions = false"></textarea>

                {{-- Mention dropdown --}}
                <div x-show="showMentions && filteredTeammates.length > 0" x-transition
                    @click.outside="showMentions = false"
                    class="absolute bottom-full left-0 mb-1 w-72 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-lg z-50 max-h-60 overflow-y-auto">
                    <template x-for="result in filteredTeammates" :key="result.id">
                        <button type="button" @click="selectMention(result)"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-left hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors first:rounded-t-xl last:rounded-b-xl">
                            <span
                                class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xs font-semibold shrink-0"
                                x-text="result.initials"></span>
                            <span class="text-sm text-zinc-700 dark:text-zinc-200 truncate" x-text="result.name"></span>
                        </button>
                    </template>
                </div>
            </div>

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
