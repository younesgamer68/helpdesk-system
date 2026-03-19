<div>
    <!-- Filters Section -->
    <div class="mb-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Filters & Search</h3>
            @if ($this->hasActiveFilters)
                <button wire:click="clearFilters"
                    class="px-3 py-1.5 text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    Clear all filters
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <!-- Search Input -->
            <div class="relative lg:col-span-2">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.500ms="search" type="text"
                    placeholder="Search name, email, or phone..."
                    class="w-full pl-10 pr-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
            </div>

            <!-- Status Filter -->
            <div>
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="deactivated">Deactivated</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Active Filters Display -->
    @if ($this->hasActiveFilters)
        <div class="mb-4 flex flex-wrap gap-2">
            @if ($search)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-teal-500/10 text-teal-600 dark:text-teal-400 text-xs font-medium rounded-full border border-teal-500/20">
                    Search: {{ $search }}
                </span>
            @endif
            @if ($statusFilter)
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-medium rounded-full border border-blue-500/20">
                    Status: {{ ucfirst($statusFilter) }}
                </span>
            @endif
        </div>
    @endif

    <!-- Table -->
    <div
        class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Name
                            @if ($sortBy === 'name')
                                <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('email')">
                        <div class="flex items-center gap-1">
                            Email
                            @if ($sortBy === 'email')
                                <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('tickets_count')">
                        <div class="flex items-center gap-1">
                            Tickets
                            @if ($sortBy === 'tickets_count')
                                <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('created_at')">
                        <div class="flex items-center gap-1">
                            Joined
                            @if ($sortBy === 'created_at')
                                <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors group"
                        wire:click="setSortBy('is_active')">
                        <div class="flex items-center gap-1">
                            Status
                            @if ($sortBy === 'is_active')
                                <span class="text-teal-400">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-50">↕</span>
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($this->customers as $customer)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition-colors"
                        wire:key="{{ $customer->id }}">
                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 font-medium">
                            {{ $customer->name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $customer->email }}
                            @if ($customer->phone)
                                <span class="block text-xs text-zinc-500">{{ $customer->phone }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-700 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                {{ $customer->tickets_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $customer->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($customer->is_active)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                                    Active
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">
                                    Deactivated
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('customers.details', ['company' => Auth::user()->company->slug, 'customer' => $customer->id]) }}"
                                    wire:navigate
                                    class="p-1.5 text-zinc-500 dark:text-zinc-400 hover:text-teal-600 dark:hover:text-teal-400 hover:bg-teal-400/10 rounded-lg transition-colors"
                                    title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                <button wire:click="toggleStatus({{ $customer->id }})"
                                    wire:confirm="Are you sure you want to {{ $customer->is_active ? 'deactivate' : 'activate' }} this customer?"
                                    class="p-1.5 {{ $customer->is_active ? 'text-zinc-500 dark:text-zinc-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-400/10' : 'text-zinc-500 dark:text-zinc-400 hover:text-green-500 dark:hover:text-green-400 hover:bg-green-400/10' }} rounded-lg transition-colors"
                                    title="{{ $customer->is_active ? 'Deactivate' : 'Activate' }}">
                                    @if ($customer->is_active)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="mt-4 text-zinc-500 dark:text-zinc-400">No customers found. Try adjusting your
                                filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->customers->links() }}
    </div>
</div>
