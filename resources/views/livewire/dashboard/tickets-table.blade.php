<div>
    <x-flash-message></x-flash-message>
 
        <!-- Filters Section -->
        <div class="mb-3 p-4  rounded-lg border border-zinc-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-white">Filters & Search</h3>
                @if ($this->hasActiveFilters)
                    <button wire:click="clearFilters"
                        class="px-3 py-1.5 text-sm text-zinc-400 hover:text-white transition-colors">
                        Clear all filters
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input wire:model.live.debounce.500ms="search" type="text"
                        placeholder="Search ticket ID, subject, customer..."
                        class="w-full pl-10 pr-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                </div>

                <!-- Status Filter -->
                <div>
                    <select wire:model.live="statusFilter"
                        class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <select wire:model.live="priorityFilter"
                        class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                        <option value="">All Priorities</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <select wire:model.live="categoryFilter"
                        class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                        <option value="">All Categories</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assigned To Filter (Admin only) -->
                @if (Auth::user()->isAdmin())
                    <div>
                        <select wire:model.live="assignedFilter"
                            class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                            <option value="">All Agents</option>
                            @foreach ($this->agents as $agent)
                                <option value="{{ $agent->id }}">{{  $agent->name === Auth::user()->name ? $agent->name . ' (You)' : $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Filters Display -->
        @if ($this->hasActiveFilters)
            <div class="mb-4 flex flex-wrap gap-2">
                @if ($search)
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-teal-500/10 text-teal-400 text-xs font-medium rounded-full border border-teal-500/20">
                        Search: {{ $search }}
                    </span>
                @endif
                @if ($statusFilter)
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-500/10 text-blue-400 text-xs font-medium rounded-full border border-blue-500/20">
                        Status: {{ ucfirst(str_replace('_', ' ', $statusFilter)) }}
                    </span>
                @endif
                @if ($priorityFilter)
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-500/10 text-orange-400 text-xs font-medium rounded-full border border-orange-500/20">
                        Priority: {{ ucfirst($priorityFilter) }}
                    </span>
                @endif
                @if ($categoryFilter)
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-500/10 text-purple-400 text-xs font-medium rounded-full border border-purple-500/20">
                        Category: {{ $this->categories->find($categoryFilter)?->name ?? '' }}
                    </span>
                @endif
                @if ($assignedFilter && Auth::user()->isAdmin())
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-500/10 text-green-400 text-xs font-medium rounded-full border border-green-500/20">
                        Assigned: {{ $this->agents->find($assignedFilter)?->name ?? 'Unassigned' }}
                    </span>
                @endif
            </div>
        @endif

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border border-zinc-800">
            <table class="w-full">
                <thead>
                    <tr class="bg-zinc-900/50 border-b border-zinc-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                            wire:click="setSortBy('ticket_number')">
                            <div class="flex items-center gap-1">
                                Ticket ID
                                @if ($sortBy === 'ticket_number')
                                    <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @else
                                    <span class="opacity-0 group-hover:opacity-50">↕</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                            wire:click="setSortBy('subject')">
                            <div class="flex items-center gap-1">
                                Subject
                                @if ($sortBy === 'subject')
                                    <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @else
                                    <span class="opacity-0 group-hover:opacity-50">↕</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                            wire:click="setSortBy('customer_name')">
                            <div class="flex items-center gap-1">
                                Customer
                                @if ($sortBy === 'customer_name')
                                    <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @else
                                    <span class="opacity-0 group-hover:opacity-50">↕</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                            Assigned To</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                            wire:click="setSortBy('priority')">
                            <div class="flex items-center gap-1">
                                Priority
                                @if ($sortBy === 'priority')
                                    <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @else
                                    <span class="opacity-0 group-hover:opacity-50">↕</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors group"
                            wire:click="setSortBy('status')">
                            <div class="flex items-center gap-1">
                                Status
                                @if ($sortBy === 'status')
                                    <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @else
                                    <span class="opacity-0 group-hover:opacity-50">↕</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                            Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse ($this->tickets as $ticket)
                        <tr class="hover:bg-zinc-900/30 transition-colors" wire:key="{{ $ticket->id }}">
                            <td class="px-4 py-3 text-sm text-zinc-300 font-mono">{{ $ticket->ticket_number }}</td>
                            <td class="px-4 py-3 text-sm text-white font-medium">{{ Str::limit($ticket->subject, 50) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-300">{{ $ticket->customer_name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    @if ($ticket->user)
                                        <div
                                            class="w-6 h-6 rounded-full bg-gradient-to-br from-teal-400 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">
                                            {{ substr($ticket->user->name, 0, 1) }}
                                        </div>
                                        <span class="text-zinc-300">{{ $ticket->user->name }}</span>
                                    @else
                                        <div
                                            class="w-6 h-6 rounded-full bg-gradient-to-br from-zinc-400 to-zinc-500 flex items-center justify-center text-white text-xs font-semibold">
                                            U
                                        </div>
                                        <span class="text-zinc-300">Unassigned</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $priorityBg = match ($ticket->priority) {
                                        'low' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                        'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'high' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                        'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $priorityBg }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $statusBg = match ($ticket->status) {
                                        'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                        'resolved' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                        'closed' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                        'in_progress' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusBg }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-300">{{ $ticket->category->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                    <button @click="open = !open"
                                        class="p-2 hover:bg-zinc-800 rounded-lg transition-colors">
                                        <svg class="w-5 h-5 text-zinc-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 mt-2 w-48 bg-zinc-800 rounded-lg shadow-xl border border-zinc-700 z-10 overflow-hidden"
                                        style="display: none;">
                                        <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}"
                                            wire:navigate
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-700 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View details
                                        </a>
                                        <button wire:click="deleteTicket({{ $ticket->id }})"
                                            wire:confirm="Are you sure you want to soft delete this ticket?"
                                            @click="open = false"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-400 hover:bg-zinc-700 hover:text-red-300 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-zinc-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="mt-4 text-zinc-400">No tickets found. Try adjusting your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $this->tickets->withPath(route('tickets', ['company' => Auth::user()->company->slug]))->links() }}
        </div>
    

    <!-- Create Ticket Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-black/50 
         z-50 flex items-center justify-center p-4"
            x-data="{ showingDiscard: @entangle('showDiscardConfirmation') }"
            @click.self="$wire.attemptCloseCreateModal()"
            @keydown.escape.window="$wire.attemptCloseCreateModal()">
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                @click.stop>
                <!-- Header -->
                <div class="sticky top-0 bg-zinc-900 border-b border-zinc-800 px-6 py-4 flex items-center justify-between z-10">
                    <div>
                        <h3 class="text-xl font-semibold text-white">Create New Ticket</h3>
                        <p class="text-sm text-zinc-400 mt-1">Fill in the details to create a support ticket</p>
                    </div>
                    <button wire:click="attemptCloseCreateModal"
                        class="text-zinc-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit="createTicket" class="p-6 space-y-6">
                    <!-- Draft Indicator -->
                    @if ($this->hasFormData)
                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-3 flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-amber-400 font-medium">Draft saved</p>
                                <p class="text-xs text-amber-300/80 mt-0.5">Your changes are automatically preserved</p>
                            </div>
                            <button type="button" wire:click="clearForm"
                                class="text-xs text-amber-400 hover:text-amber-300 underline">
                                Clear all
                            </button>
                        </div>
                    @endif

                    <!-- Customer Information Section -->
                    <div>
                        <h4 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Customer Information
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Customer Name -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-zinc-300 mb-2">
                                    Customer Name <span class="text-red-400">*</span>
                                </label>
                                <input wire:model.blur="customer_name" type="text"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    placeholder="John Doe">
                                @error('customer_name')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Customer Email -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">
                                    Customer Email <span class="text-red-400">*</span>
                                </label>
                                <input wire:model.blur="customer_email" type="email"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    placeholder="john@example.com">
                                @error('customer_email')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Customer Phone -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">
                                    Customer Phone
                                </label>
                                <input wire:model.blur="customer_phone" type="text"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    placeholder="+1 (555) 123-4567">
                                @error('customer_phone')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Details Section -->
                    <div class="pt-4 border-t border-zinc-800">
                        <h4 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ticket Details
                        </h4>

                        <div class="space-y-4">
                            <!-- Subject -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">
                                    Subject <span class="text-red-400">*</span>
                                </label>
                                <input wire:model.blur="subject" type="text"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    placeholder="Brief description of the issue">
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">
                                    Description <span class="text-red-400">*</span>
                                </label>
                                <textarea wire:model.blur="description" rows="5"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 resize-none"
                                    placeholder="Detailed description of the issue..."></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Priority -->
                                <div>
                                    <label class="block text-sm font-medium text-zinc-300 mb-2">
                                        Priority <span class="text-red-400">*</span>
                                    </label>
                                    <select wire:model.live="priority"
                                        class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('priority')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-zinc-300 mb-2">
                                        Status <span class="text-red-400">*</span>
                                    </label>
                                    <select wire:model.live="status"
                                        class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                        <option value="pending">Pending</option>
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div class="pt-4 border-t border-zinc-800">
                        <h4 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Assignment & Categorization
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Assign To -->
                            @if (Auth::user()->role === 'admin')
                                <div>
                                    <label class="block text-sm font-medium text-zinc-300 mb-2">
                                        Assign To
                                    </label>
                                    <select wire:model.live="assigned_to"
                                        class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                        <option value="">Unassigned</option>
                                        @foreach ($this->agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-2">
                                    Category
                                </label>
                                <select wire:model.live="category_id"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                    <option value="">No Category</option>
                                    @foreach ($this->categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Info Note -->
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm text-blue-400 font-medium">Auto-verified Ticket</p>
                                <p class="text-xs text-blue-300/80 mt-1">Tickets created by staff are automatically verified and don't require email confirmation.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" wire:click="attemptCloseCreateModal"
                            class="flex-1 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Ticket
                        </button>
                    </div>
                </form>

                <!-- Discard Confirmation Dialog -->
                <div x-show="showingDiscard"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-black/60  flex items-center justify-center p-4"
                    style="display: none;">
                    <div x-show="showingDiscard"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="transform scale-95 opacity-0"
                        x-transition:enter-end="transform scale-100 opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="transform scale-100 opacity-100"
                        x-transition:leave-end="transform scale-95 opacity-0"
                        class="bg-zinc-800 border border-zinc-700 rounded-lg shadow-2xl max-w-md w-full p-6"
                        @click.stop>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-amber-500/10 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white">Discard changes?</h3>
                                <p class="text-sm text-zinc-400 mt-2">
                                    You have unsaved changes. If you close now, your progress will be lost.
                                </p>
                                <div class="flex gap-3 mt-6">
                                    <button wire:click="cancelDiscard"
                                        class="flex-1 px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-zinc-200 font-medium rounded-lg transition-colors">
                                        Keep editing
                                    </button>
                                    <button wire:click="confirmDiscard"
                                        class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors">
                                        Discard
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</div>