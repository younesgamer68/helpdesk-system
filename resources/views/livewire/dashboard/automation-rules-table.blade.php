<div>
    <x-flash-message />

    <!-- Filters Section -->
    <div class="mb-3 p-4 rounded-lg border border-zinc-800 bg-zinc-900 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-white">Filters</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- Search Input -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.500ms="search" type="text"
                    placeholder="Search rules..."
                    class="w-full pl-10 pr-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
            </div>

            <!-- Type Filter -->
            <select wire:model.live="filterType"
                class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                <option value="">All Types</option>
                <option value="assignment">Auto Assignment</option>
                <option value="priority">Priority Change</option>
                <option value="auto_reply">Auto Reply</option>
                <option value="escalation">Escalation</option>
            </select>

            <!-- Status Filter -->
            <select wire:model.live="filterStatus"
                class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-zinc-800 bg-zinc-900 shadow-sm">
        <table class="w-full">
            <thead>
                <tr class="bg-zinc-900/50 border-b border-zinc-800">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                        wire:click="setSortBy('priority')">
                        <div class="flex items-center gap-1">
                            Priority
                            @if ($sortBy === 'priority')
                                <span class="text-teal-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Name
                            @if ($sortBy === 'name')
                                <span class="text-teal-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                        wire:click="setSortBy('executions_count')">
                        <div class="flex items-center gap-1">
                            Executions
                            @if ($sortBy === 'executions_count')
                                <span class="text-teal-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse ($this->automationRules as $rule)
                    <tr class="hover:bg-zinc-900/30 transition-colors" wire:key="rule-{{ $rule->id }}">
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-zinc-800 text-zinc-300 font-medium text-xs">
                                {{ $rule->priority }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div>
                                <span class="font-medium text-white">{{ $rule->name }}</span>
                                @if($rule->description)
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ Str::limit($rule->description, 50) }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $typeColors = [
                                    'assignment' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'priority' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                    'auto_reply' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    'escalation' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                ];
                                $typeLabels = [
                                    'assignment' => 'Auto Assignment',
                                    'priority' => 'Priority Change',
                                    'auto_reply' => 'Auto Reply',
                                    'escalation' => 'Escalation',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full border {{ $typeColors[$rule->type] }}">
                                {{ $typeLabels[$rule->type] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <button wire:click="toggleRuleStatus({{ $rule->id }})"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $rule->is_active ? 'bg-teal-500' : 'bg-zinc-700' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $rule->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-400">
                            <div>
                                <span class="font-medium text-white">{{ number_format($rule->executions_count) }}</span>
                                @if($rule->last_executed_at)
                                    <p class="text-xs text-zinc-500">Last: {{ $rule->last_executed_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="editRule({{ $rule->id }})"
                                    class="p-1.5 text-zinc-400 hover:text-teal-400 hover:bg-zinc-800 rounded-lg transition-colors"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $rule->id }})"
                                    class="p-1.5 text-zinc-400 hover:text-red-400 hover:bg-zinc-800 rounded-lg transition-colors"
                                    title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-zinc-400">No automation rules found</p>
                                <button wire:click="openCreateModal"
                                    class="mt-2 px-4 py-2 bg-teal-500 text-white text-sm font-medium rounded-lg hover:bg-teal-600 transition-colors">
                                    Create your first rule
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->automationRules->hasPages())
            <div class="px-4 py-3 border-t border-zinc-800">
                {{ $this->automationRules->links() }}
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    <flux:modal wire:model="showCreateModal" class="md:w-[600px]">
        <div class="space-y-6">
            <flux:heading size="lg">Create Automation Rule</flux:heading>

            <form wire:submit="createRule" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field class="col-span-2">
                        <flux:label>Rule Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g., Auto-assign Technical Issues" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field class="col-span-2">
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" placeholder="Describe what this rule does (optional)" rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Rule Type</flux:label>
                        <flux:select wire:model.live="type">
                            <flux:select.option value="assignment">Auto Assignment</flux:select.option>
                            <flux:select.option value="priority">Priority Change</flux:select.option>
                            <flux:select.option value="auto_reply">Auto Reply</flux:select.option>
                            <flux:select.option value="escalation">Escalation</flux:select.option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Execution Priority</flux:label>
                        <flux:input type="number" wire:model="priority" min="0" max="1000" placeholder="0" />
                        <p class="text-xs text-zinc-500 mt-1">Lower numbers execute first</p>
                        <flux:error name="priority" />
                    </flux:field>
                </div>

                <!-- Conditions Section -->
                <div class="border border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-white">Conditions</h4>

                    @if(in_array($type, ['assignment', 'priority', 'auto_reply', 'escalation']))
                        <flux:field>
                            <flux:label>Category (optional)</flux:label>
                            <flux:select wire:model="category_id">
                                <flux:select.option value="">Any Category</flux:select.option>
                                @foreach($this->categories as $category)
                                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    @endif

                    @if($type === 'priority')
                        <flux:field>
                            <flux:label>Keywords (in subject or description)</flux:label>
                            <div class="flex gap-2">
                                <flux:input wire:model="newKeyword" placeholder="Add keyword" wire:keydown.enter.prevent="addKeyword" />
                                <flux:button type="button" wire:click="addKeyword" variant="ghost">Add</flux:button>
                            </div>
                            @if(count($keywords) > 0)
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($keywords as $index => $keyword)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-zinc-800 text-zinc-300 text-xs rounded-full">
                                            {{ $keyword }}
                                            <button type="button" wire:click="removeKeyword({{ $index }})" class="hover:text-red-400">×</button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </flux:field>
                    @endif

                    @if($type === 'escalation')
                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Idle Hours</flux:label>
                                <flux:input type="number" wire:model="idle_hours" min="1" max="720" />
                                <p class="text-xs text-zinc-500 mt-1">Hours without activity before escalation</p>
                            </flux:field>

                            <flux:field>
                                <flux:label>Ticket Statuses</flux:label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="pending" class="rounded bg-zinc-800 border-zinc-700 text-teal-500 focus:ring-teal-500">
                                        <span class="text-sm text-zinc-300">Pending</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="open" class="rounded bg-zinc-800 border-zinc-700 text-teal-500 focus:ring-teal-500">
                                        <span class="text-sm text-zinc-300">Open</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="in_progress" class="rounded bg-zinc-800 border-zinc-700 text-teal-500 focus:ring-teal-500">
                                        <span class="text-sm text-zinc-300">In Progress</span>
                                    </label>
                                </div>
                            </flux:field>
                        </div>
                    @endif

                    @if($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Trigger on ticket creation</flux:label>
                            <flux:switch wire:model="on_create" />
                        </flux:field>
                    @endif
                </div>

                <!-- Actions Section -->
                <div class="border border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-white">Actions</h4>

                    @if($type === 'assignment')
                        <flux:field variant="inline">
                            <flux:label>Assign to specialist matching category</flux:label>
                            <flux:switch wire:model.live="assign_to_specialist" />
                        </flux:field>

                        @if($assign_to_specialist)
                            <flux:field variant="inline">
                                <flux:label>Fallback to generalist if no specialist</flux:label>
                                <flux:switch wire:model="fallback_to_generalist" />
                            </flux:field>
                        @else
                            <flux:field>
                                <flux:label>Assign to specific operator</flux:label>
                                <flux:select wire:model="assign_to_operator_id">
                                    <flux:select.option value="">Select Operator</flux:select.option>
                                    @foreach($this->operators as $operator)
                                        <flux:select.option value="{{ $operator->id }}">{{ $operator->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                        @endif
                    @endif

                    @if($type === 'priority')
                        <flux:field>
                            <flux:label>Set Priority To</flux:label>
                            <flux:select wire:model="set_priority">
                                <flux:select.option value="low">Low</flux:select.option>
                                <flux:select.option value="medium">Medium</flux:select.option>
                                <flux:select.option value="high">High</flux:select.option>
                                <flux:select.option value="urgent">Urgent</flux:select.option>
                            </flux:select>
                        </flux:field>
                    @endif

                    @if($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Send email to customer</flux:label>
                            <flux:switch wire:model.live="send_email" />
                        </flux:field>

                        @if($send_email)
                            <flux:field>
                                <flux:label>Email Subject (optional)</flux:label>
                                <flux:input wire:model="email_subject" placeholder="Leave empty for 'Re: [Ticket Subject]'" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Message</flux:label>
                                <flux:textarea wire:model="email_message" rows="3" />
                            </flux:field>
                        @endif
                    @endif

                    @if($type === 'escalation')
                        <flux:field variant="inline">
                            <flux:label>Escalate priority to next level</flux:label>
                            <flux:switch wire:model.live="escalate_priority" />
                        </flux:field>

                        @if(!$escalate_priority)
                            <flux:field>
                                <flux:label>Set Priority To</flux:label>
                                <flux:select wire:model="set_priority">
                                    <flux:select.option value="low">Low</flux:select.option>
                                    <flux:select.option value="medium">Medium</flux:select.option>
                                    <flux:select.option value="high">High</flux:select.option>
                                    <flux:select.option value="urgent">Urgent</flux:select.option>
                                </flux:select>
                            </flux:field>
                        @endif

                        <flux:field variant="inline">
                            <flux:label>Notify administrators</flux:label>
                            <flux:switch wire:model="notify_admin" />
                        </flux:field>
                    @endif
                </div>

                <flux:field variant="inline">
                    <flux:label>Rule Active</flux:label>
                    <flux:switch wire:model="is_active" />
                </flux:field>

                <div class="flex gap-3 pt-4">
                    <flux:button type="button" wire:click="closeCreateModal" variant="ghost" class="flex-1">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="flex-1">
                        Create Rule
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Modal -->
    <flux:modal wire:model="showEditModal" class="md:w-[600px]">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Automation Rule</flux:heading>

            <form wire:submit="updateRule" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field class="col-span-2">
                        <flux:label>Rule Name</flux:label>
                        <flux:input wire:model="name" placeholder="e.g., Auto-assign Technical Issues" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field class="col-span-2">
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" placeholder="Describe what this rule does (optional)" rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Rule Type</flux:label>
                        <flux:select wire:model.live="type">
                            <flux:select.option value="assignment">Auto Assignment</flux:select.option>
                            <flux:select.option value="priority">Priority Change</flux:select.option>
                            <flux:select.option value="auto_reply">Auto Reply</flux:select.option>
                            <flux:select.option value="escalation">Escalation</flux:select.option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Execution Priority</flux:label>
                        <flux:input type="number" wire:model="priority" min="0" max="1000" placeholder="0" />
                        <p class="text-xs text-zinc-500 mt-1">Lower numbers execute first</p>
                        <flux:error name="priority" />
                    </flux:field>
                </div>

                <!-- Conditions Section -->
                <div class="border border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-white">Conditions</h4>

                    @if(in_array($type, ['assignment', 'priority', 'auto_reply', 'escalation']))
                        <flux:field>
                            <flux:label>Category (optional)</flux:label>
                            <flux:select wire:model="category_id">
                                <flux:select.option value="">Any Category</flux:select.option>
                                @foreach($this->categories as $category)
                                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    @endif

                    @if($type === 'priority')
                        <flux:field>
                            <flux:label>Keywords (in subject or description)</flux:label>
                            <div class="flex gap-2">
                                <flux:input wire:model="newKeyword" placeholder="Add keyword" wire:keydown.enter.prevent="addKeyword" />
                                <flux:button type="button" wire:click="addKeyword" variant="ghost">Add</flux:button>
                            </div>
                            @if(count($keywords) > 0)
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($keywords as $index => $keyword)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-zinc-800 text-zinc-300 text-xs rounded-full">
                                            {{ $keyword }}
                                            <button type="button" wire:click="removeKeyword({{ $index }})" class="hover:text-red-400">×</button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </flux:field>
                    @endif

                    @if($type === 'escalation')
                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Idle Hours</flux:label>
                                <flux:input type="number" wire:model="idle_hours" min="1" max="720" />
                                <p class="text-xs text-zinc-500 mt-1">Hours without activity before escalation</p>
                            </flux:field>

                            <flux:field>
                                <flux:label>Ticket Statuses</flux:label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="pending" class="rounded bg-zinc-800 border-zinc-700 text-teal-500 focus:ring-teal-500">
                                        <span class="text-sm text-zinc-300">Pending</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="open" class="rounded bg-zinc-800 border-zinc-700 text-teal-500 focus:ring-teal-500">
                                        <span class="text-sm text-zinc-300">Open</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="in_progress" class="rounded bg-zinc-800 border-zinc-700 text-teal-500 focus:ring-teal-500">
                                        <span class="text-sm text-zinc-300">In Progress</span>
                                    </label>
                                </div>
                            </flux:field>
                        </div>
                    @endif

                    @if($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Trigger on ticket creation</flux:label>
                            <flux:switch wire:model="on_create" />
                        </flux:field>
                    @endif
                </div>

                <!-- Actions Section -->
                <div class="border border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-white">Actions</h4>

                    @if($type === 'assignment')
                        <flux:field variant="inline">
                            <flux:label>Assign to specialist matching category</flux:label>
                            <flux:switch wire:model.live="assign_to_specialist" />
                        </flux:field>

                        @if($assign_to_specialist)
                            <flux:field variant="inline">
                                <flux:label>Fallback to generalist if no specialist</flux:label>
                                <flux:switch wire:model="fallback_to_generalist" />
                            </flux:field>
                        @else
                            <flux:field>
                                <flux:label>Assign to specific operator</flux:label>
                                <flux:select wire:model="assign_to_operator_id">
                                    <flux:select.option value="">Select Operator</flux:select.option>
                                    @foreach($this->operators as $operator)
                                        <flux:select.option value="{{ $operator->id }}">{{ $operator->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                        @endif
                    @endif

                    @if($type === 'priority')
                        <flux:field>
                            <flux:label>Set Priority To</flux:label>
                            <flux:select wire:model="set_priority">
                                <flux:select.option value="low">Low</flux:select.option>
                                <flux:select.option value="medium">Medium</flux:select.option>
                                <flux:select.option value="high">High</flux:select.option>
                                <flux:select.option value="urgent">Urgent</flux:select.option>
                            </flux:select>
                        </flux:field>
                    @endif

                    @if($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Send email to customer</flux:label>
                            <flux:switch wire:model.live="send_email" />
                        </flux:field>

                        @if($send_email)
                            <flux:field>
                                <flux:label>Email Subject (optional)</flux:label>
                                <flux:input wire:model="email_subject" placeholder="Leave empty for 'Re: [Ticket Subject]'" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Message</flux:label>
                                <flux:textarea wire:model="email_message" rows="3" />
                            </flux:field>
                        @endif
                    @endif

                    @if($type === 'escalation')
                        <flux:field variant="inline">
                            <flux:label>Escalate priority to next level</flux:label>
                            <flux:switch wire:model.live="escalate_priority" />
                        </flux:field>

                        @if(!$escalate_priority)
                            <flux:field>
                                <flux:label>Set Priority To</flux:label>
                                <flux:select wire:model="set_priority">
                                    <flux:select.option value="low">Low</flux:select.option>
                                    <flux:select.option value="medium">Medium</flux:select.option>
                                    <flux:select.option value="high">High</flux:select.option>
                                    <flux:select.option value="urgent">Urgent</flux:select.option>
                                </flux:select>
                            </flux:field>
                        @endif

                        <flux:field variant="inline">
                            <flux:label>Notify administrators</flux:label>
                            <flux:switch wire:model="notify_admin" />
                        </flux:field>
                    @endif
                </div>

                <flux:field variant="inline">
                    <flux:label>Rule Active</flux:label>
                    <flux:switch wire:model="is_active" />
                </flux:field>

                <div class="flex gap-3 pt-4">
                    <flux:button type="button" wire:click="closeEditModal" variant="ghost" class="flex-1">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="flex-1">
                        Update Rule
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteConfirmation" class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">Delete Rule</flux:heading>

            <p class="text-zinc-400">
                Are you sure you want to delete this automation rule? This action cannot be undone.
            </p>

            <div class="flex gap-3 pt-4">
                <flux:button type="button" wire:click="cancelDelete" variant="ghost" class="flex-1">
                    Cancel
                </flux:button>
                <flux:button type="button" wire:click="deleteRule" variant="danger" class="flex-1">
                    Delete Rule
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
