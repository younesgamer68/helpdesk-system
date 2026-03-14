@use('Carbon\Carbon')

<div class="space-y-6">
    {{-- Filters --}}
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.300ms="ticketSearch"
                    placeholder="Search subject or customer..."
                    class="w-full rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2 placeholder-zinc-500" />
            </div>
            <flux:select wire:model.live="filterStatus" class="min-w-[130px]">
                <flux:select.option value="">All Statuses</flux:select.option>
                @foreach (['open', 'in_progress', 'pending', 'resolved', 'closed'] as $s)
                    <flux:select.option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterPriority" class="min-w-[120px]">
                <flux:select.option value="">All Priorities</flux:select.option>
                @foreach (['low', 'medium', 'high', 'urgent'] as $p)
                    <flux:select.option value="{{ $p }}">{{ ucfirst($p) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterCategory" class="min-w-[130px]">
                <flux:select.option value="">All Categories</flux:select.option>
                @foreach ($this->categories as $cat)
                    <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterAgent" class="min-w-[130px]">
                <flux:select.option value="">All Agents</flux:select.option>
                @foreach ($this->agents as $ag)
                    <flux:select.option value="{{ $ag->id }}">{{ $ag->name }}</flux:select.option>
                @endforeach
            </flux:select>
            @if ($filterStatus || $filterPriority || $filterCategory || $filterAgent || $ticketSearch)
                <flux:button wire:click="clearTicketFilters" variant="ghost" size="sm"
                    class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">Clear</flux:button>
            @endif
        </div>
    </div>

    {{-- Tickets Table --}}
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-zinc-500 dark:text-zinc-400 uppercase text-xs font-medium border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        @foreach (['ticket_number' => 'Ticket ID', 'subject' => 'Subject', 'customer_name' => 'Customer', 'category_id' => 'Category', 'priority' => 'Priority', 'status' => 'Status', 'assigned_to' => 'Agent', 'created_at' => 'Created'] as $col => $heading)
                            <th class="py-3 px-4 cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100" wire:click="setTicketSort('{{ $col }}')">
                                {{ $heading }} @if ($ticketSortBy === $col) {{ $ticketSortDir === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                        @endforeach
                        <th class="py-3 px-4 text-right">Resolution</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/90">
                    @forelse($this->paginatedTickets as $ticket)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors cursor-pointer"
                            onclick="window.location='{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}'">
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400 font-mono text-xs">{{ $ticket->ticket_number }}</td>
                            <td class="py-3 px-4 text-zinc-900 dark:text-zinc-100 max-w-[200px] truncate">{{ $ticket->subject }}</td>
                            <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">{{ $ticket->customer_name }}</td>
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $ticket->category?->name ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $this->priorityBadgeClasses($ticket->priority) }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $this->statusBadgeClasses($ticket->status) }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $ticket->user?->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-zinc-500 text-xs">{{ Carbon::parse($ticket->created_at)->format('M j, Y') }}</td>
                            <td class="py-3 px-4 text-right text-zinc-500 text-xs">
                                @if ($ticket->resolved_at)
                                    @php $mins = Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->resolved_at)); @endphp
                                    {{ floor($mins / 60) }}h {{ $mins % 60 }}m
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center">
                                <x-reports-empty :show="true" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($this->paginatedTickets->hasPages())
            <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-800">
                {{ $this->paginatedTickets->links() }}
            </div>
        @endif
    </div>
</div>
