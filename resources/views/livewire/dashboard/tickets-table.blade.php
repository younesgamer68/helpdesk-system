<div>
    <!-- Filters Section -->
    <div class="mb-6 p-4 bg-slate-800 rounded-lg border border-slate-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-white">Filters & Search</h3>
            @if ($hasActiveFilters)
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    Clear all filters
                </flux:button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search Input -->
            <div>
                <flux:input wire:model.live.debounce.500ms="search" type="text"
                    placeholder="Search ticket ID, subject, customer..." icon="magnifying-glass" />
            </div>

            <!-- Status Filter -->
            <div>
                <flux:select wire:model.live="statusFilter" placeholder="All Statuses">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Priority Filter -->
            <div>
                <flux:select wire:model.live="priorityFilter" placeholder="All Priorities">
                    <option value="">All Priorities</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Category Filter -->
            <div>
                <flux:select wire:model.live="categoryFilter" placeholder="All Categories">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Assigned To Filter (Admin only) -->
            @if ($user->isAdmin())
                <div>
                    <flux:select wire:model.live="assignedFilter" placeholder="All Agents">
                        <option value="">All Agents</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>
    </div>

    <!-- Active Filters Display -->
    @if ($hasActiveFilters)
        <div class="mb-4 flex flex-wrap gap-2">
            @if ($search)
                <flux:badge>Search: {{ $search }}</flux:badge>
            @endif
            @if ($statusFilter)
                <flux:badge>Status: {{ ucfirst($statusFilter) }}</flux:badge>
            @endif
            @if ($priorityFilter)
                <flux:badge>Priority: {{ ucfirst($priorityFilter) }}</flux:badge>
            @endif
            @if ($categoryFilter)
                <flux:badge>Category: {{ $categories->find($categoryFilter)->name ?? '' }}</flux:badge>
            @endif
            @if ($assignedFilter && $user->isAdmin())
                <flux:badge>Assigned: {{ $agents->find($assignedFilter)->name ?? '' }}</flux:badge>
            @endif
        </div>
    @endif

    <!-- Results Count -->
    <div class="mb-4 text-sm text-zinc-400">
        Showing {{ $tickets->count() }} of {{ $tickets->total() }} tickets
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border border-slate-700 rounded-lg">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-800 border-b border-slate-700">
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400 cursor-pointer hover:text-white"
                        wire:click="setSortBy('ticket_number')">
                        Ticket ID
                        @if ($sortBy === 'ticket_number')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400 cursor-pointer hover:text-white"
                        wire:click="setSortBy('subject')">
                        Subject
                        @if ($sortBy === 'subject')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400 cursor-pointer hover:text-white"
                        wire:click="setSortBy('customer_name')">
                        Customer
                        @if ($sortBy === 'customer_name')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400">Assigned To</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400 cursor-pointer hover:text-white"
                        wire:click="setSortBy('priority')">
                        Priority
                        @if ($sortBy === 'priority')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400 cursor-pointer hover:text-white"
                        wire:click="setSortBy('status')">
                        Status
                        @if ($sortBy === 'status')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400">Category</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-zinc-400"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr class="border-b border-zinc-700 hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-white">{{ $ticket->ticket_number }}</td>
                        <td class="px-4 py-3 text-sm text-white whitespace-nowrap">{{ $ticket->subject }}</td>
                        <td class="px-4 py-3 text-sm text-white">{{ $ticket->customer_name }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-white">{{ $ticket->user->name }}</td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $priorityBg = match ($ticket->priority) {
                                    'low' => 'bg-green-500/20 text-green-400',
                                    'medium' => 'bg-blue-500/20 text-blue-400',
                                    'high' => 'bg-orange-500/20 text-orange-400',
                                    'urgent' => 'bg-red-500/20 text-red-400',
                                    default => 'bg-gray-500/20 text-gray-400',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-md text-xs font-medium {{ $priorityBg }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $statusBg = match ($ticket->status) {
                                    'open' => 'bg-blue-500/20 text-blue-400',
                                    'on-hold' => 'bg-yellow-500/20 text-yellow-400',
                                    'resolved' => 'bg-green-500/20 text-green-400',
                                    'closed' => 'bg-slate-500/20 text-slate-400',
                                    default => 'bg-gray-500/20 text-gray-400',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-md text-xs font-medium {{ $statusBg }}">
                                {{ ucfirst(str_replace('-', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-white">{{ $ticket->category->name }}</td>
                        <td class="px-4 py-3 text-sm">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                    inset="top bottom">
                                </flux:button>
                                <flux:navmenu>
                                    <flux:navmenu.item icon="eye">
                                        <a href="{{ route('details', ['company' => $user->company->slug, 'ticket' => $ticket]) }}"
                                            wire:navigate>View details</a>
                                    </flux:navmenu.item>
                                    <flux:navmenu.item icon="trash">Delete</flux:navmenu.item>
                                </flux:navmenu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-zinc-400">
                            No tickets found. Try adjusting your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-5">
        {{ $tickets->links() }}
    </div>
</div>
