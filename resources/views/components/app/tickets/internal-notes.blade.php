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
    <div class="shrink-0 border-t border-amber-200 dark:border-amber-800/40 bg-amber-50/40 dark:bg-amber-900/10 p-4"
        x-data="{
            showMentions: false,
            mentionQuery: '',
            mentionResults: [],
            selectedIndex: 0,
            mentionedUsers: [],
            mentionStartPos: null,
        
            async triggerSearch(query) {
                this.mentionResults = await $wire.searchTeammates(query);
                this.selectedIndex = 0;
            },
        
            handleKeyup(e) {
                const textarea = e.target;
                const val = textarea.value;
                const pos = textarea.selectionStart;
        
                if (this.showMentions) {
                    const textFromAt = val.substring(this.mentionStartPos + 1, pos);
                    if (textFromAt.includes(' ') && textFromAt.length > 1) {
                        this.closeMentions();
                        return;
                    }
                    this.mentionQuery = textFromAt;
                    this.triggerSearch(this.mentionQuery);
                }
            },
        
            handleKeydown(e) {
                if (!this.showMentions) return;
        
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.mentionResults.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                } else if (e.key === 'Enter' && this.mentionResults.length > 0) {
                    e.preventDefault();
                    this.selectUser(this.mentionResults[this.selectedIndex]);
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    this.closeMentions();
                }
            },
        
            handleInput(e) {
                const textarea = e.target;
                const val = textarea.value;
                const pos = textarea.selectionStart;
        
                const charBefore = pos > 0 ? val[pos - 1] : '';
                const charBeforeAt = pos > 1 ? val[pos - 2] : ' ';
        
                if (charBefore === '@' && (charBeforeAt === ' ' || charBeforeAt === '\n' || pos === 1)) {
                    this.showMentions = true;
                    this.mentionStartPos = pos - 1;
                    this.mentionQuery = '';
                    this.triggerSearch('');
                }
            },
        
            selectUser(user) {
                if (!user) return;
                const textarea = this.$refs.noteTextarea;
                const val = textarea.value;
                const before = val.substring(0, this.mentionStartPos);
                const after = val.substring(textarea.selectionStart);
                const newVal = before + '@' + user.name + ' ' + after;
                textarea.value = newVal;
                $wire.set('internalNote', newVal);
        
                if (!this.mentionedUsers.find(u => u.id === user.id)) {
                    this.mentionedUsers.push(user);
                    $wire.set('mentionedUserIds', this.mentionedUsers.map(u => u.id));
                }
        
                this.closeMentions();
                textarea.focus();
                const cursorPos = before.length + user.name.length + 2;
                textarea.setSelectionRange(cursorPos, cursorPos);
            },
        
            closeMentions() {
                this.showMentions = false;
                this.mentionQuery = '';
                this.mentionResults = [];
                this.selectedIndex = 0;
                this.mentionStartPos = null;
            },
        
            resetAfterSubmit() {
                this.mentionedUsers = [];
                this.closeMentions();
            },
        }" x-on:internal-note-added.window="resetAfterSubmit()">
        <form wire:submit="addInternalNote">
            <div class="relative">
                <textarea x-ref="noteTextarea" wire:model="internalNote" rows="3"
                    placeholder="Write an internal note... Use @ to mention teammates" required x-on:input="handleInput($event)"
                    x-on:keyup="handleKeyup($event)" x-on:keydown="handleKeydown($event)"
                    class="w-full bg-white dark:bg-zinc-800 border border-amber-200 dark:border-amber-700/50 rounded-xl px-4 py-3 text-sm text-zinc-700 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 resize-none disabled:opacity-50"
                    wire:loading.attr="disabled"></textarea>

                {{-- Mention dropdown --}}
                <div x-show="showMentions && mentionResults.length > 0" x-cloak x-transition.opacity
                    class="absolute bottom-full left-0 mb-1 w-64 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg overflow-hidden z-50">
                    <template x-for="(user, index) in mentionResults" :key="user.id">
                        <button type="button" x-on:click="selectUser(user)"
                            :class="index === selectedIndex ? 'bg-amber-50 dark:bg-amber-900/30' :
                                'hover:bg-zinc-50 dark:hover:bg-zinc-800'"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-left transition-colors">
                            <span
                                class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xs font-semibold"
                                x-text="user.initials"></span>
                            <span class="text-sm text-zinc-700 dark:text-zinc-200" x-text="user.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Mentioned user chips --}}
            <div x-show="mentionedUsers.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                <template x-for="user in mentionedUsers" :key="user.id">
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                        <span x-text="'@' + user.name"></span>
                    </span>
                </template>
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
