<div>
    <x-ui.flash-message />

    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400 mb-4">
            <a href="{{ route('customers', ['company' => Auth::user()->company->slug]) }}"
                class="hover:text-zinc-900 dark:hover:text-white transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Customers
            </a>
            <span>/</span>
            <span class="text-zinc-600 dark:text-zinc-300">Customer Details</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div
                    class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-400 to-blue-500 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0 shadow-lg shadow-emerald-500/20">
                    {{ substr($customer->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center gap-3">
                        {{ $customer->name }}
                        @if ($customer->is_active)
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                                Active
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">
                                Deactivated
                            </span>
                        @endif
                    </h1>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:{{ $customer->email }}"
                                class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">{{ $customer->email }}</a>
                        </div>
                        @if ($customer->phone)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a href="tel:{{ $customer->phone }}"
                                    class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">{{ $customer->phone }}</a>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Joined {{ $customer->created_at->format('M j, Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button wire:click="toggleStatus"
                    wire:confirm="Are you sure you want to {{ $customer->is_active ? 'deactivate' : 'activate' }} this customer?"
                    class="px-4 py-2 text-sm font-medium rounded-lg border transition-colors flex items-center gap-2 {{ $customer->is_active ? 'border-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-500/10' : 'border-green-500/20 text-green-600 dark:text-green-400 hover:bg-green-500/10' }}">
                    @if ($customer->is_active)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Deactivate Customer
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Activate Customer
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-800">
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Subject
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Assigned To
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                        Created
                    </th>
                    <th class="w-14 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-400">
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->tickets as $ticket)
                    @php
                        $priorityBorder = match ($ticket->priority) {
                            'urgent' => 'border-l-red-500',
                            'high' => 'border-l-orange-400',
                            'medium' => 'border-l-blue-400',
                            'low' => 'border-l-zinc-300 dark:border-l-zinc-600',
                            default => 'border-l-zinc-300 dark:border-l-zinc-600',
                        };

                        $statusBadge = match ($ticket->status) {
                            'open'
                                => 'border-zinc-300 bg-transparent text-zinc-600 dark:border-zinc-700 dark:text-zinc-300',
                            'in_progress'
                                => 'border-blue-200 bg-blue-50 text-blue-600 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-300',
                            'pending'
                                => 'border-amber-200 bg-amber-50 text-amber-600 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300',
                            'resolved'
                                => 'border-green-200 bg-green-50 text-green-600 dark:border-green-900 dark:bg-green-950/30 dark:text-green-300',
                            'closed'
                                => 'border-zinc-200 bg-zinc-100 text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400',
                            default
                                => 'border-zinc-300 bg-zinc-50 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300',
                        };
                    @endphp
                    <tr
                        class="border-b border-l-3 border-zinc-100 transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-900 {{ $priorityBorder }}">
                        <td class="px-4 py-4 align-middle">
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                {{ Str::limit($ticket->subject, 65) }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400">
                                {{ $ticket->category->name ?? 'No category' }} · {{ $ticket->ticket_number }}
                            </p>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                @if ($ticket->user)
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                                        {{ substr($ticket->user->name, 0, 1) }}
                                    </div>
                                    <span
                                        class="text-sm text-zinc-600 dark:text-zinc-400">{{ $ticket->user->name }}</span>
                                @else
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">
                                        U
                                    </div>
                                    <span class="text-sm text-zinc-400">Unassigned</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span
                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs {{ $statusBadge }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $ticket->created_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket]) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1 text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                                View
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="mt-4 text-zinc-500 dark:text-zinc-400">This customer hasn't submitted any tickets
                                yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
