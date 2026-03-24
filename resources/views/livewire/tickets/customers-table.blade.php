<div>
    <x-ui.flash-message />

    <!-- Filters Bar -->
    <div class="mb-6 space-y-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="relative w-full xl:max-w-sm">
                <svg class="pointer-events-none absolute left-0 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.500ms="search" type="text"
                    placeholder="Search name, email, or phone..."
                    class="w-full border-0 border-b border-zinc-200 bg-transparent py-2 pl-6 pr-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-teal-500 focus:outline-none focus:ring-0 dark:border-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500">
            </div>

            <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                <flux:dropdown>
                    <button type="button" class="flex items-center justify-between min-w-[150px] rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-600 focus:border-emerald-500 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                        <span>
                            @php
                                $statusLabels = [
                                    '' => 'All Statuses',
                                    'active' => 'Active',
                                    'deactivated' => 'Deactivated'
                                ];
                            @endphp
                            {{ $statusLabels[$statusFilter] ?? 'All Statuses' }}
                        </span>
                        <svg class="h-3.5 w-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <flux:menu class="w-[150px]">
                        <flux:menu.radio.group wire:model.live="statusFilter">
                            <flux:menu.radio value="" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">All Statuses</flux:menu.radio>
                            <flux:menu.radio value="active" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">Active</flux:menu.radio>
                            <flux:menu.radio value="deactivated" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">Deactivated</flux:menu.radio>
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        @if ($this->hasActiveFilters)
            <div class="flex items-center gap-3 text-xs">
                <button wire:click="clearFilters"
                    class="text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    Clear all filters
                </button>
            </div>
        @endif
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer group hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                        wire:click="setSortBy('name')">
                        <div class="flex items-center gap-1">
                            Name
                            @if ($sortBy === 'name')
                                <span class="text-teal-500 dark:text-teal-400 ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-100 ml-1 transition-opacity text-zinc-400">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer group hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                        wire:click="setSortBy('email')">
                        <div class="flex items-center gap-1">
                            Email
                            @if ($sortBy === 'email')
                                <span class="text-teal-500 dark:text-teal-400 ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-100 ml-1 transition-opacity text-zinc-400">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer group hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                        wire:click="setSortBy('tickets_count')">
                        <div class="flex items-center gap-1">
                            Tickets
                            @if ($sortBy === 'tickets_count')
                                <span class="text-teal-500 dark:text-teal-400 ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-100 ml-1 transition-opacity text-zinc-400">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer group hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                        wire:click="setSortBy('created_at')">
                        <div class="flex items-center gap-1">
                            Joined
                            @if ($sortBy === 'created_at')
                                <span class="text-teal-500 dark:text-teal-400 ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-100 ml-1 transition-opacity text-zinc-400">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400 cursor-pointer group hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors"
                        wire:click="setSortBy('is_active')">
                        <div class="flex items-center gap-1">
                            Status
                            @if ($sortBy === 'is_active')
                                <span class="text-teal-500 dark:text-teal-400 ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @else
                                <span class="opacity-0 group-hover:opacity-100 ml-1 transition-opacity text-zinc-400">↕</span>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-400"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->customers as $customer)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors group cursor-pointer"
                        wire:key="{{ $customer->id }}"
                        wire:click="navigateToDetails({{ $customer->id }})">
                        <td class="px-4 py-4 font-medium text-sm text-zinc-900 dark:text-zinc-100">
                            {{ $customer->name }}
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            <div class="flex flex-col">
                                <span>{{ $customer->email }}</span>
                                @if ($customer->phone)
                                    <span class="text-xs text-zinc-400">{{ $customer->phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                {{ $customer->tickets_count }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
                            {{ $customer->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if ($customer->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                    <span class="w-1 h-1 rounded-full bg-zinc-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right text-sm" wire:click.stop>
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('customers.details', ['company' => Auth::user()->company->slug, 'customer' => $customer->id]) }}"
                                    wire:navigate
                                    class="p-1 text-zinc-400 hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                                    title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                <button @click="confirmAction($wire, {{ $customer->id }}, 'toggleStatus', 'Are you sure?', 'Are you sure you want to {{ $customer->is_active ? 'deactivate' : 'activate' }} this customer?', 'Yes, {{ $customer->is_active ? 'deactivate' : 'activate' }}!')"
                                    class="p-1 {{ $customer->is_active ? 'text-zinc-400 hover:text-red-500' : 'text-zinc-400 hover:text-emerald-500' }} transition-colors"
                                    title="{{ $customer->is_active ? 'Deactivate' : 'Activate' }}">
                                    @if ($customer->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-3" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-200">No customers found</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Try allowing more lenient filters to see results.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $this->customers->links('pagination.tickets-compact') }}
        </div>
    </div>
</div>
