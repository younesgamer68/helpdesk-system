<div>
    <x-ui.flash-message />

    <!-- Header with Add Rule button -->
    <div class="mb-4 flex justify-end">
        <button wire:click="openCreateModal"
            class="px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg flex items-center gap-2 hover:bg-emerald-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Rule
        </button>
    </div>

    <!-- Filters Section -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Search Input -->
        <div class="relative">
            <svg class="absolute left-0 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400 dark:text-zinc-500"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search rules..."
                class="w-full pl-8 pr-4 py-2 bg-transparent border-0 border-b border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-0 transition-colors">
        </div>

        <!-- Type Filter -->
        <flux:dropdown>
            <button type="button"
                class="w-full flex justify-between items-center px-3 py-2 bg-transparent border-0 border-b border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-zinc-900 dark:focus:border-zinc-100 focus:ring-0 transition-colors">
                <span>
                    @php
                        $typeLabels = [
                            '' => 'All Types',
                            'assignment' => 'Auto Assignment',
                            'priority' => 'Priority Change',
                            'auto_reply' => 'Auto Reply',
                            'escalation' => 'Escalation',
                            'sla_breach' => 'SLA Breach',
                        ];
                    @endphp
                    {{ $typeLabels[$filterType] ?? 'All Types' }}
                </span>
                <svg class="h-4 w-4 text-zinc-900 dark:text-zinc-100" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <flux:menu class="w-[200px]">
                <flux:menu.radio.group wire:model.live="filterType">
                    <flux:menu.radio value=""
                        class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                        All Types</flux:menu.radio>
                    @if ($filterMode === 'all' || $filterMode === 'assignment')
                        <flux:menu.radio value="assignment"
                            class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                            Auto Assignment</flux:menu.radio>
                    @endif
                    @if ($filterMode === 'all' || $filterMode === 'ticket')
                        <flux:menu.radio value="priority"
                            class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                            Priority Change</flux:menu.radio>
                        <flux:menu.radio value="auto_reply"
                            class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                            Auto Reply</flux:menu.radio>
                        <flux:menu.radio value="escalation"
                            class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                            Escalation</flux:menu.radio>
                        <flux:menu.radio value="sla_breach"
                            class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                            SLA Breach</flux:menu.radio>
                    @endif
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>

        <!-- Status Filter -->
        <flux:dropdown>
            <button type="button"
                class="w-full flex justify-between items-center px-3 py-2 bg-transparent border-0 border-b border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-zinc-900 dark:focus:border-zinc-100 focus:ring-0 transition-colors">
                <span>
                    @php
                        $statusLabels = [
                            '' => 'All Status',
                            '1' => 'Active',
                            '0' => 'Inactive',
                        ];
                    @endphp
                    {{ $statusLabels[$filterStatus] ?? 'All Status' }}
                </span>
                <svg class="h-4 w-4 text-zinc-900 dark:text-zinc-100" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <flux:menu class="w-[200px]">
                <flux:menu.radio.group wire:model.live="filterStatus">
                    <flux:menu.radio value=""
                        class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                        All Status</flux:menu.radio>
                    <flux:menu.radio value="1"
                        class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                        Active</flux:menu.radio>
                    <flux:menu.radio value="0"
                        class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">
                        Inactive</flux:menu.radio>
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-zinc-500 dark:text-zinc-400">
            <thead class="bg-zinc-50/50 dark:bg-zinc-900/50 border-b border-zinc-200/50 dark:border-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider cursor-pointer group hover:text-zinc-700 dark:hover:text-zinc-300"
                        wire:click="setSortBy('priority')">
                        <div class="flex items-center gap-1">
                            Priority
                            @if ($sortBy === 'priority')
                                <span
                                    class="text-emerald-500 dark:text-emerald-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider cursor-pointer group hover:text-zinc-700 dark:hover:text-zinc-300"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Name
                            @if ($sortBy === 'name')
                                <span
                                    class="text-emerald-500 dark:text-emerald-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-4 py-3 text-xs font-medium uppercase tracking-wider cursor-pointer group hover:text-zinc-700 dark:hover:text-zinc-300"
                        wire:click="setSortBy('executions_count')">
                        <div class="flex items-center gap-1">
                            Executions
                            @if ($sortBy === 'executions_count')
                                <span
                                    class="text-emerald-500 dark:text-emerald-400 ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50 ml-2">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->automationRules as $rule)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        wire:key="rule-{{ $rule->id }}">
                        <td class="px-4 py-4 text-zinc-600 dark:text-zinc-400">
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-medium text-xs border border-zinc-200 dark:border-zinc-700">
                                {{ $rule->priority }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div>
                                <span
                                    class="font-medium text-zinc-900 dark:text-zinc-100 text-sm">{{ $rule->name }}</span>
                                @if ($rule->description)
                                    <p class="mt-0.5 text-xs text-zinc-500">{{ Str::limit($rule->description, 50) }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $typeColors = [
                                    'assignment' =>
                                        'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                    'priority' =>
                                        'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
                                    'auto_reply' =>
                                        'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                    'escalation' => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
                                    'sla_breach' =>
                                        'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                ];
                                $typeLabels = [
                                    'assignment' => 'Auto Assignment',
                                    'priority' => 'Priority Change',
                                    'auto_reply' => 'Auto Reply',
                                    'escalation' => 'Escalation',
                                    'sla_breach' => 'SLA Breach',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded border {{ $typeColors[$rule->type] }}">
                                {{ $typeLabels[$rule->type] }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <button wire:click="toggleRuleStatus({{ $rule->id }})"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $rule->is_active ? 'bg-emerald-500' : 'bg-zinc-200 dark:bg-zinc-700' }}">
                                <span
                                    class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform {{ $rule->is_active ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                            </button>
                        </td>
                        <td class="px-4 py-4">
                            <div>
                                <span
                                    class="font-medium text-zinc-900 dark:text-zinc-100 text-sm">{{ number_format($rule->executions_count) }}</span>
                                @if ($rule->last_executed_at)
                                    <p class="text-xs text-zinc-500">Last:
                                        {{ $rule->last_executed_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="editRule({{ $rule->id }})"
                                    class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-emerald-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="confirmDeletion($wire, {{ $rule->id }}, 'deleteRule', 'automation rule')"
                                    class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-red-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
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
                                <svg class="w-12 h-12 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-zinc-500 dark:text-zinc-400">No automation rules found</p>
                                <button wire:click="openCreateModal"
                                    class="mt-2 px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                                    Create your first rule
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->automationRules->hasPages())
            <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-800">
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
                        <flux:textarea wire:model="description" placeholder="Describe what this rule does (optional)"
                            rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Rule Type</flux:label>
                        <flux:select wire:model.live="type">
                            @if ($filterMode === 'all' || $filterMode === 'assignment')
                                <flux:select.option value="assignment">Auto Assignment</flux:select.option>
                            @endif
                            @if ($filterMode === 'all' || $filterMode === 'ticket')
                                <flux:select.option value="priority">Priority Change</flux:select.option>
                                <flux:select.option value="auto_reply">Auto Reply</flux:select.option>
                                <flux:select.option value="escalation">Escalation</flux:select.option>
                                <flux:select.option value="sla_breach">SLA Breach</flux:select.option>
                            @endif
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Execution Priority</flux:label>
                        <flux:input type="number" wire:model="priority" min="0" max="1000"
                            placeholder="0" />
                        <p class="text-xs text-zinc-500 mt-1">Lower numbers execute first</p>
                        <flux:error name="priority" />
                    </flux:field>
                </div>

                <!-- Conditions Section -->
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Conditions</h4>

                    @if (in_array($type, ['assignment', 'priority', 'auto_reply', 'escalation', 'sla_breach']))
                        <flux:field>
                            <flux:label>Category (optional)</flux:label>
                            <select wire:model="category_id"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Any Category</option>
                                @foreach ($this->categories as $parentCategory)
                                    <optgroup label="{{ $parentCategory->name }}">
                                        <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}
                                        </option>
                                        @foreach ($parentCategory->children as $childCategory)
                                            <option value="{{ $childCategory->id }}">{{ $childCategory->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @if (in_array($type, ['assignment', 'priority']))
                                <p class="mt-1 text-xs text-zinc-500">Selecting a parent category also matches tickets
                                    filed under its subcategories.</p>
                            @endif
                        </flux:field>
                    @endif

                    @if ($type === 'priority')
                        <flux:field>
                            <flux:label>Keywords (in subject or description)</flux:label>
                            <div class="flex gap-2">
                                <flux:input wire:model="newKeyword" placeholder="Add keyword"
                                    wire:keydown.enter.prevent="addKeyword" />
                                <flux:button type="button" wire:click="addKeyword" variant="ghost">Add</flux:button>
                            </div>
                            @if (count($keywords) > 0)
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach ($keywords as $index => $keyword)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs rounded-full">
                                            {{ $keyword }}
                                            <button type="button" wire:click="removeKeyword({{ $index }})"
                                                class="hover:text-red-400">×</button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </flux:field>
                    @endif

                    @if ($type === 'escalation')
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
                                        <input type="checkbox" wire:model="conditionStatuses" value="pending"
                                            class="rounded bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-emerald-500 focus:ring-emerald-500">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">Pending</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="open"
                                            class="rounded bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-emerald-500 focus:ring-emerald-500">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">Open</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="in_progress"
                                            class="rounded bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-emerald-500 focus:ring-emerald-500">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">In Progress</span>
                                    </label>
                                </div>
                            </flux:field>
                        </div>
                    @endif

                    @if ($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Trigger on ticket creation</flux:label>
                            <flux:switch wire:model="on_create" />
                        </flux:field>
                    @endif
                </div>

                <!-- Actions Section -->
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Actions</h4>

                    @if ($type === 'assignment')
                        <flux:field variant="inline">
                            <flux:label>Assign to specialist matching category</flux:label>
                            <flux:switch wire:model.live="assign_to_specialist" />
                        </flux:field>

                        @if ($assign_to_specialist)
                            <flux:field variant="inline">
                                <flux:label>Fallback to generalist if no specialist</flux:label>
                                <flux:switch wire:model="fallback_to_generalist" />
                            </flux:field>
                        @else
                            <div x-data="{ operatorId: $wire.entangle('assign_to_operator_id'), teamId: $wire.entangle('assign_to_team_id') }" class="space-y-4">
                                <flux:field>
                                    <flux:label>Assign to specific operator</flux:label>
                                    <select x-model="operatorId" @change="if (operatorId) teamId = null"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="">Select Operator</option>
                                        @foreach ($this->operators as $operator)
                                            <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                                        @endforeach
                                    </select>
                                </flux:field>

                                <flux:field>
                                    <flux:label>Or assign to team</flux:label>
                                    <select x-model="teamId" @change="if (teamId) operatorId = null"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="">Select Team</option>
                                        @foreach ($this->teamsForSelect as $team)
                                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                </flux:field>
                            </div>
                        @endif
                    @endif

                    @if ($type === 'priority')
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

                    @if ($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Send email to customer</flux:label>
                            <flux:switch wire:model.live="send_email" />
                        </flux:field>

                        @if ($send_email)
                            <flux:field>
                                <flux:label>Email Subject (optional)</flux:label>
                                <flux:input wire:model="email_subject"
                                    placeholder="Leave empty for 'Re: [Ticket Subject]'" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Message</flux:label>
                                <flux:textarea wire:model="email_message" rows="3" />
                            </flux:field>
                        @endif
                    @endif

                    @if (in_array($type, ['escalation', 'sla_breach']))
                        <flux:field variant="inline">
                            <flux:label>Escalate priority to next level</flux:label>
                            <flux:switch wire:model.live="escalate_priority" />
                        </flux:field>

                        @if (!$escalate_priority)
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

                        <flux:field>
                            <flux:label>Assign to specific operator</flux:label>
                            <flux:select wire:model="assign_to_operator_id">
                                <flux:select.option value="">Leave Unchanged</flux:select.option>
                                @foreach ($this->operators as $operator)
                                    <flux:select.option value="{{ $operator->id }}">{{ $operator->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>

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
                        <flux:textarea wire:model="description" placeholder="Describe what this rule does (optional)"
                            rows="2" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Rule Type</flux:label>
                        <flux:select wire:model.live="type">
                            @if ($filterMode === 'all' || $filterMode === 'assignment')
                                <flux:select.option value="assignment">Auto Assignment</flux:select.option>
                            @endif
                            @if ($filterMode === 'all' || $filterMode === 'ticket')
                                <flux:select.option value="priority">Priority Change</flux:select.option>
                                <flux:select.option value="auto_reply">Auto Reply</flux:select.option>
                                <flux:select.option value="escalation">Escalation</flux:select.option>
                                <flux:select.option value="sla_breach">SLA Breach</flux:select.option>
                            @endif
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Execution Priority</flux:label>
                        <flux:input type="number" wire:model="priority" min="0" max="1000"
                            placeholder="0" />
                        <p class="text-xs text-zinc-500 mt-1">Lower numbers execute first</p>
                        <flux:error name="priority" />
                    </flux:field>
                </div>

                <!-- Conditions Section -->
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Conditions</h4>

                    @if (in_array($type, ['assignment', 'priority', 'auto_reply', 'escalation', 'sla_breach']))
                        <flux:field>
                            <flux:label>Category (optional)</flux:label>
                            <select wire:model="category_id"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Any Category</option>
                                @foreach ($this->categories as $parentCategory)
                                    <optgroup label="{{ $parentCategory->name }}">
                                        <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}
                                        </option>
                                        @foreach ($parentCategory->children as $childCategory)
                                            <option value="{{ $childCategory->id }}">{{ $childCategory->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @if (in_array($type, ['assignment', 'priority']))
                                <p class="mt-1 text-xs text-zinc-500">Selecting a parent category also matches tickets
                                    filed under its subcategories.</p>
                            @endif
                        </flux:field>
                    @endif

                    @if ($type === 'priority')
                        <flux:field>
                            <flux:label>Keywords (in subject or description)</flux:label>
                            <div class="flex gap-2">
                                <flux:input wire:model="newKeyword" placeholder="Add keyword"
                                    wire:keydown.enter.prevent="addKeyword" />
                                <flux:button type="button" wire:click="addKeyword" variant="ghost">Add</flux:button>
                            </div>
                            @if (count($keywords) > 0)
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach ($keywords as $index => $keyword)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs rounded-full">
                                            {{ $keyword }}
                                            <button type="button" wire:click="removeKeyword({{ $index }})"
                                                class="hover:text-red-400">×</button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </flux:field>
                    @endif

                    @if ($type === 'escalation')
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
                                        <input type="checkbox" wire:model="conditionStatuses" value="pending"
                                            class="rounded bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-emerald-500 focus:ring-emerald-500">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">Pending</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="open"
                                            class="rounded bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-emerald-500 focus:ring-emerald-500">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">Open</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="conditionStatuses" value="in_progress"
                                            class="rounded bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-emerald-500 focus:ring-emerald-500">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">In Progress</span>
                                    </label>
                                </div>
                            </flux:field>
                        </div>
                    @endif

                    @if ($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Trigger on ticket creation</flux:label>
                            <flux:switch wire:model="on_create" />
                        </flux:field>
                    @endif
                </div>

                <!-- Actions Section -->
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-4">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Actions</h4>

                    @if ($type === 'assignment')
                        <flux:field variant="inline">
                            <flux:label>Assign to specialist matching category</flux:label>
                            <flux:switch wire:model.live="assign_to_specialist" />
                        </flux:field>

                        @if ($assign_to_specialist)
                            <flux:field variant="inline">
                                <flux:label>Fallback to generalist if no specialist</flux:label>
                                <flux:switch wire:model="fallback_to_generalist" />
                            </flux:field>
                        @else
                            <div x-data="{ operatorId: $wire.entangle('assign_to_operator_id'), teamId: $wire.entangle('assign_to_team_id') }" class="space-y-4">
                                <flux:field>
                                    <flux:label>Assign to specific operator</flux:label>
                                    <select x-model="operatorId" @change="if (operatorId) teamId = null"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="">Select Operator</option>
                                        @foreach ($this->operators as $operator)
                                            <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                                        @endforeach
                                    </select>
                                </flux:field>

                                <flux:field>
                                    <flux:label>Or assign to team</flux:label>
                                    <select x-model="teamId" @change="if (teamId) operatorId = null"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="">Select Team</option>
                                        @foreach ($this->teamsForSelect as $team)
                                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                </flux:field>
                            </div>
                        @endif
                    @endif

                    @if ($type === 'priority')
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

                    @if ($type === 'auto_reply')
                        <flux:field variant="inline">
                            <flux:label>Send email to customer</flux:label>
                            <flux:switch wire:model.live="send_email" />
                        </flux:field>

                        @if ($send_email)
                            <flux:field>
                                <flux:label>Email Subject (optional)</flux:label>
                                <flux:input wire:model="email_subject"
                                    placeholder="Leave empty for 'Re: [Ticket Subject]'" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Message</flux:label>
                                <flux:textarea wire:model="email_message" rows="3" />
                            </flux:field>
                        @endif
                    @endif

                    @if (in_array($type, ['escalation', 'sla_breach']))
                        <flux:field variant="inline">
                            <flux:label>Escalate priority to next level</flux:label>
                            <flux:switch wire:model.live="escalate_priority" />
                        </flux:field>

                        @if (!$escalate_priority)
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

                        <flux:field>
                            <flux:label>Assign to specific operator</flux:label>
                            <flux:select wire:model="assign_to_operator_id">
                                <flux:select.option value="">Leave Unchanged</flux:select.option>
                                @foreach ($this->operators as $operator)
                                    <flux:select.option value="{{ $operator->id }}">{{ $operator->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>

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

            <p class="text-zinc-500 dark:text-zinc-400">
                Are you sure you want to delete this automation rule? This action cannot be undone.
            </p>

            <div class="flex gap-3 pt-4">
                <flux:button type="button" wire:click="cancelDelete" variant="ghost" class="flex-1">
                    Cancel
                </flux:button>
                <flux:button type="button" wire:click="deleteRule" variant="primary" class="flex-1 !bg-emerald-500 hover:!bg-emerald-600">
                    Delete Rule
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
