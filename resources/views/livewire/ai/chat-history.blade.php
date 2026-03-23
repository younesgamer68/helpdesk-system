<div>
    <flux:separator class="mb-5" />
    <x-ai.layout heading="Chat History"
        subheading="Review past chatbot conversations and whether they deflected or converted to tickets.">
        <div class="space-y-6">
            {{-- Filters --}}
            <div class="flex items-center gap-4">
                <select wire:model.live="outcomeFilter"
                    class="rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 text-sm">
                    <option value="">All outcomes</option>
                    <option value="active">Active</option>
                    <option value="resolved">Resolved (deflected)</option>
                    <option value="escalated">Escalated to ticket</option>
                    <option value="abandoned">Abandoned</option>
                </select>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden">
                @if ($this->conversations->isEmpty())
                    <div class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        No chatbot conversations recorded yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-zinc-500 dark:text-zinc-400">
                            <thead class="border-b border-zinc-100 dark:border-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Session</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Messages</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Outcome</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400">Date</th>
                                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider text-zinc-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                                @foreach ($this->conversations as $convo)
                                    <tr wire:key="convo-{{ $convo->id }}"
                                        class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                        <td class="px-4 py-4 text-zinc-900 dark:text-zinc-100 font-mono text-xs">
                                            {{ Str::limit($convo->session_id, 12) }}
                                        </td>
                                        <td class="px-4 py-4 text-zinc-600 dark:text-zinc-400">
                                            {{ is_array($convo->messages) ? count($convo->messages) : 0 }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($convo->outcome)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border
                                                    {{ match ($convo->outcome) {
                                                        'active' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                                        'resolved' => 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20',
                                                        'escalated' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                                        'abandoned' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700',
                                                        default => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700',
                                                    } }}">
                                                    {{ ucfirst($convo->outcome) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-400 text-xs italic">In progress</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400 text-xs">
                                            {{ $convo->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <button wire:click="viewConversation({{ $convo->id }})" type="button"
                                                class="px-3 py-1.5 rounded-md border border-zinc-200 dark:border-zinc-700 text-xs font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-300 transition-colors">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if (!$this->conversations->isEmpty())
                <div class="mt-4">
                    {{ $this->conversations->links() }}
                </div>
            @endif
        </div>

        {{-- Detail modal --}}
        <flux:modal wire:model="showDetail" class="md:w-160">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Conversation Detail</h3>

                @if ($this->viewingConversation && is_array($this->viewingConversation->messages))
                    <div class="max-h-96 overflow-y-auto space-y-3">
                        @foreach ($this->viewingConversation->messages as $msg)
                            <div
                                class="flex {{ ($msg['role'] ?? 'user') === 'bot' ? 'justify-start' : 'justify-end' }}">
                                <div
                                    class="max-w-[80%] rounded-lg px-3 py-2 text-sm
                                    {{ ($msg['role'] ?? 'user') === 'bot'
                                        ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100'
                                        : 'bg-[#0B4F4A] text-white' }}">
                                    {{ $msg['text'] ?? ($msg['content'] ?? '') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                        <span>Outcome:
                            <strong>{{ ucfirst($this->viewingConversation->outcome ?? 'unknown') }}</strong></span>
                        <span>{{ $this->viewingConversation->created_at->format('M d, Y H:i') }}</span>
                        @if ($this->viewingConversation->ticket_id)
                            <span>Ticket #{{ $this->viewingConversation->ticket_id }}</span>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No messages found.</p>
                @endif
            </div>
        </flux:modal>
    </x-ai.layout>
</div>
