<div>
    <flux:separator class="mb-5" />
    <x-ai.layout heading="Auto-Triage Rules"
        subheading="Define rules to automatically assign category and priority to incoming tickets.">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Keyword rules match ticket subject/body. AI rules let the model analyze content and decide
                    automatically.
                </p>
                <button wire:click="openCreate" type="button"
                    class="border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors shrink-0">
                    Add Rule
                </button>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden">
                @if ($this->rules_list->isEmpty())
                    <div class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        No triage rules yet. Add your first rule to start auto-triaging tickets.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-zinc-500 dark:text-zinc-400">
                            <thead class="border-b border-zinc-100 dark:border-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Name</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Type</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Keywords / Trigger</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Category</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Priority</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Status</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                                @foreach ($this->rules_list as $rule)
                                    <tr wire:key="rule-{{ $rule->id }}"
                                        class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                        <td class="px-4 py-4 text-zinc-900 dark:text-zinc-100 font-medium">
                                            {{ $rule->name }}</td>
                                        <td class="px-4 py-4">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border
                                                {{ $rule->type === 'ai' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' }}">
                                                {{ $rule->type === 'ai' ? 'AI' : 'Keyword' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-zinc-600 dark:text-zinc-400 max-w-48 truncate">
                                            @if ($rule->type === 'keyword' && $rule->keywords)
                                                {{ implode(', ', $rule->keywords) }}
                                            @else
                                                <span class="text-zinc-400 italic">AI decides</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-zinc-600 dark:text-zinc-400">
                                            {{ $rule->category?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($rule->priority)
                                                <span class="capitalize">{{ $rule->priority }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <button wire:click="toggleActive({{ $rule->id }})" type="button"
                                                class="text-xs font-medium px-2 py-0.5 rounded border {{ $rule->is_active ? 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700' }}">
                                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td class="px-4 py-4 text-right space-x-2">
                                            <button wire:click="openEdit({{ $rule->id }})" type="button"
                                                class="px-3 py-1.5 rounded-md border border-zinc-200 dark:border-zinc-700 text-xs font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 transition-colors">
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $rule->id }})"
                                                wire:confirm="Delete this rule?" type="button"
                                                class="px-3 py-1.5 rounded-md border border-red-200 dark:border-red-900/30 text-red-600 dark:text-red-400 text-xs font-medium hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <flux:modal wire:model="showModal" class="md:w-160">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $editingId ? 'Edit Rule' : 'Add Rule' }}
                </h3>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Name</label>
                    <input type="text" wire:model="name" placeholder="e.g. Billing Keywords"
                        class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                    @error('name')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Type</label>
                    <select wire:model.live="type"
                        class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                        <option value="keyword">Keyword Match</option>
                        <option value="ai">AI Auto-Detect</option>
                    </select>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $type === 'ai' ? 'AI will analyze ticket content and decide the best category/priority.' : 'Match specific keywords in ticket subject or body.' }}
                    </p>
                </div>

                @if ($type === 'keyword')
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Keywords
                            (comma-separated)</label>
                        <input type="text" wire:model="keywordsInput" placeholder="billing, invoice, payment, charge"
                            class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                        @error('keywordsInput')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Assign
                            Category</label>
                        <select wire:model="category_id"
                            class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">— None —</option>
                            @foreach ($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Assign
                            Priority</label>
                        <select wire:model="priority"
                            class="w-full rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">— None —</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="rule-active"
                        class="w-4 h-4 rounded border-zinc-300">
                    <label for="rule-active" class="text-sm text-zinc-700 dark:text-zinc-200">Active</label>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 rounded border border-zinc-200 dark:border-zinc-700 text-sm">
                        Cancel
                    </button>
                    <button type="button" wire:click="save"
                        class="px-4 py-2 rounded text-white text-sm font-medium hover:opacity-90"
                        style="background-color: #0B4F4A;">
                        Save Rule
                    </button>
                </div>
            </div>
        </flux:modal>
    </x-ai.layout>
</div>
