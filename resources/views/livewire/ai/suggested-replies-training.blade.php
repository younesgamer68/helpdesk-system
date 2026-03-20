<div>
    <flux:separator class="mb-5" />
    <x-ai.layout heading="Reply Training" subheading="Manage golden responses and review AI suggestion feedback.">
        <div class="space-y-6">
            {{-- Tabs --}}
            <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-800">
                <button wire:click="$set('tab', 'golden')" type="button"
                    class="pb-2 text-sm font-medium border-b-2 transition-colors {{ $tab === 'golden' ? 'border-[#0B4F4A] text-[#0B4F4A] dark:text-emerald-300' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                    Golden Responses
                </button>
                <button wire:click="$set('tab', 'feed')" type="button"
                    class="pb-2 text-sm font-medium border-b-2 transition-colors {{ $tab === 'feed' ? 'border-[#0B4F4A] text-[#0B4F4A] dark:text-emerald-300' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                    Suggestion Feed
                </button>
            </div>

            @if ($tab === 'golden')
                {{-- Golden Responses --}}
                <div class="flex items-center justify-between">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Mark responses as "golden" to prioritize them in
                        AI suggestions.</p>
                    <button wire:click="openAdd" type="button"
                        class="border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors shrink-0">
                        Add Golden Response
                    </button>
                </div>

                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    @if ($this->goldenResponses->isEmpty())
                        <div class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No golden responses yet. Add one to start training the AI.
                        </div>
                    @else
                        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($this->goldenResponses as $golden)
                                <div wire:key="golden-{{ $golden->id }}" class="p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ Str::limit($golden->content, 200) }}</p>
                                            <div
                                                class="mt-2 flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                                                <span>By {{ $golden->user?->name ?? 'Unknown' }}</span>
                                                @if ($golden->category)
                                                    <span
                                                        class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800">{{ $golden->category->name }}</span>
                                                @endif
                                                <span>{{ $golden->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        <button wire:click="deleteGolden({{ $golden->id }})"
                                            wire:confirm="Remove this golden response?" type="button"
                                            class="px-3 py-1.5 rounded border border-red-300 text-red-500 text-xs hover:bg-red-50 dark:hover:bg-red-900/20 shrink-0">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                {{-- Suggestion Feed --}}
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    @if ($this->suggestionFeed->isEmpty())
                        <div class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No AI suggestions logged yet.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-500">
                                        <th class="px-4 py-3 font-medium">Ticket</th>
                                        <th class="px-4 py-3 font-medium">Agent</th>
                                        <th class="px-4 py-3 font-medium">Action</th>
                                        <th class="px-4 py-3 font-medium">When</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->suggestionFeed as $log)
                                        <tr wire:key="log-{{ $log->id }}"
                                            class="border-b border-zinc-100 dark:border-zinc-800/70">
                                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">
                                                @if ($log->ticket)
                                                    #{{ $log->ticket->ticket_number }}
                                                    <span
                                                        class="text-zinc-500 dark:text-zinc-400 ml-1">{{ Str::limit($log->ticket->subject, 30) }}</span>
                                                @else
                                                    <span class="text-zinc-400">Deleted</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                                {{ $log->user?->name ?? '—' }}</td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ match ($log->action) {
                                                        'use' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                        'dismiss' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                                        'regenerate' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                        default => 'bg-[#0B4F4A]/10 text-[#0B4F4A] dark:bg-[#0B4F4A]/20 dark:text-emerald-300',
                                                    } }}">
                                                    {{ ucfirst($log->action) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                                                {{ $log->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-800">
                            {{ $this->suggestionFeed->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <flux:modal wire:model="showModal" class="md:w-160">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Add Golden Response</h3>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Response Content</label>
                    <textarea wire:model="newContent" rows="6"
                        placeholder="Type a high-quality response that agents frequently use..."
                        class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100"></textarea>
                    @error('newContent')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Category
                        (optional)</label>
                    <select wire:model="newCategoryId"
                        class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                        <option value="">— All categories —</option>
                        @foreach ($this->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 rounded border border-zinc-200 dark:border-zinc-700 text-sm">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveGolden"
                        class="px-4 py-2 rounded text-white text-sm font-medium hover:opacity-90"
                        style="background-color: #0B4F4A;">
                        Save
                    </button>
                </div>
            </div>
        </flux:modal>
    </x-ai.layout>
</div>
