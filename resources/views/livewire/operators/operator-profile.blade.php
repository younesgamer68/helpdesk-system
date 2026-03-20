<div>
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('operators', ['company' => Auth::user()->company->slug]) }}"
                class="p-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Operator Profile</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">View performance and manage team member settings.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" wire:click="toggleAvailability"
                class="flex items-center gap-2 px-3 py-1.5 rounded-full border transition-colors {{ $operator->is_available ? 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400' }}">
                <span
                    class="w-2 h-2 rounded-full {{ $operator->is_available ? 'bg-green-500 animate-pulse' : 'bg-red-400' }}"></span>
                <span class="text-xs font-medium">{{ $operator->is_available ? 'Available' : 'Unavailable' }}</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <div
                class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
                <div class="h-24 bg-gradient-to-r from-emerald-500 to-emerald-500"></div>
                <div class="px-6 pb-6">
                    <div class="relative -mt-12 mb-4">
                        @if ($operator->avatar)
                            <img src="{{ $operator->avatar }}"
                                class="w-24 h-24 rounded-2xl border-4 border-white dark:border-zinc-800 shadow-md object-cover">
                        @else
                            <div
                                class="w-24 h-24 rounded-2xl border-4 border-white dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-700 shadow-md flex items-center justify-center text-2xl font-bold text-zinc-500 dark:text-zinc-400">
                                {{ $operator->initials() }}
                            </div>
                        @endif
                        <!-- Status indicator (To be wired up later) -->
                        <div
                            class="absolute bottom-1 right-1 w-5 h-5 bg-white dark:border-zinc-800 border-2 border-white dark:border-zinc-800 rounded-full">
                            <div class="w-full h-full bg-emerald-500 rounded-full"></div>
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $operator->name }}</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">{{ $operator->email }}</p>

                    <div class="flex flex-wrap gap-2">
                        <span
                            class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $operator->role === 'admin' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' }}">
                            {{ $operator->role }}
                        </span>
                        <span
                            class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                            Active
                        </span>
                    </div>
                </div>
            </div>

            <!-- Specialities Card -->
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Specialities</h3>
                    <button @click="$wire.set('showSpecialtiesModal', true)"
                        class="text-xs font-medium text-emerald-500 hover:text-emerald-600 transition-colors">Edit</button>
                </div>

                <div class="flex flex-wrap gap-2">
                    @php
                        $specialties = $operator->categories;
                        if ($operator->specialty_id && !$specialties->contains('id', $operator->specialty_id)) {
                            $specialties = $specialties->push($operator->specialty);
                        }
                    @endphp
                    @forelse ($specialties->filter() as $category)
                        <span
                            class="px-3 py-1 rounded-full text-xs font-medium border bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-600">
                            {{ $category->name }}
                        </span>
                    @empty
                        <div
                            class="w-full py-4 text-center border-2 border-dashed border-zinc-100 dark:border-zinc-700 rounded-xl">
                            <p class="text-xs text-zinc-500 italic">No specialties assigned</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Teams Card -->
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm">
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Teams</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse ($operator->teams as $team)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-600">
                            <span class="w-2 h-2 rounded-full shrink-0"
                                style="background-color: {{ $team->color }}"></span>
                            {{ $team->name }}
                        </span>
                    @empty
                        <div
                            class="w-full py-4 text-center border-2 border-dashed border-zinc-100 dark:border-zinc-700 rounded-xl">
                            <p class="text-xs text-zinc-500 italic">Not assigned to any teams</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Role & Danger Zone -->
            <div
                class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm space-y-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300 mb-2">Change Role</label>
                    <div class="flex gap-2">
                        <select
                            x-on:change="if (confirm('Are you sure you want to change this member\'s role to ' + $el.value.charAt(0).toUpperCase() + $el.value.slice(1) + '?')) { $wire.set('role', $el.value); $wire.updateRole() } else { $el.value = '{{ $operator->role }}' }"
                            {{ $operator->id === Auth::id() ? 'disabled' : '' }}
                            class="flex-1 px-3 py-2 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm transition-colors focus:ring-1 focus:ring-emerald-500 outline-none disabled:opacity-50">
                            <option value="operator" {{ $operator->role === 'operator' ? 'selected' : '' }}>Operator
                            </option>
                            <option value="admin" {{ $operator->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    @if ($operator->id === Auth::id())
                        <p class="mt-2 text-[10px] text-zinc-400">You cannot modify your own role.</p>
                    @endif
                </div>

                <div class="pt-6 border-t border-zinc-100 dark:border-zinc-700">
                    <button wire:click="removeOperator"
                        wire:confirm="Are you sure you want to remove this team member? All their tickets will be set to unassigned."
                        {{ $operator->id === Auth::id() ? 'disabled' : '' }}
                        class="w-full px-4 py-2 bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 font-medium rounded-lg text-sm hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors disabled:opacity-50">
                        Remove Member
                    </button>
                    @if ($operator->id === Auth::id())
                        <p class="mt-2 text-[10px] text-zinc-400 text-center">You cannot remove yourself.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Avg. Response Time</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $this->stats['avg_response_time'] }}</p>
                </div>
                <div
                    class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Resolved (This Month)</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $this->stats['resolved_this_month'] }}</p>
                </div>
                <div
                    class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">Satisfaction Rate</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 text-emerald-500">
                        {{ $this->stats['satisfaction_rate'] }}</p>
                </div>
            </div>

            <!-- Open Tickets -->
            <div
                class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700 flex items-center justify-between">
                    <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Currently Open Tickets</h3>
                    <span
                        class="px-2 py-1 bg-zinc-100 dark:bg-zinc-700 rounded text-xs font-medium text-zinc-600 dark:text-zinc-300">
                        {{ $this->openTickets->count() }} active
                    </span>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($this->openTickets as $ticket)
                        <div
                            class="px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider 
                                    {{ $ticket->priority === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' : '' }}
                                    {{ $ticket->priority === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : '' }}
                                    {{ $ticket->priority === 'low' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' : '' }}
                                ">
                                    {{ $ticket->priority }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        #{{ $ticket->ticket_number }} - {{ $ticket->subject }}</p>
                                    <p class="text-xs text-zinc-500">Updated {{ $ticket->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $ticket->ticket_number]) }}"
                                class="opacity-0 group-hover:opacity-100 p-2 text-zinc-400 hover:text-emerald-500 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <p class="text-sm text-zinc-500">No open tickets assigned.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity -->
            <div
                class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700">
                    <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Recent Activity</h3>
                </div>

                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse ($this->recentActivity as $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if (!$loop->last)
                                            <span
                                                class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-zinc-100 dark:bg-zinc-700"
                                                aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    class="h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center ring-8 ring-white dark:ring-zinc-800">
                                                    <svg class="h-5 w-5 text-zinc-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $log->description }}
                                                        @if ($log->ticket)
                                                            <a href="{{ route('details', ['company' => Auth::user()->company->slug, 'ticket' => $log->ticket->ticket_number]) }}"
                                                                class="font-medium text-zinc-900 dark:text-zinc-100 hover:text-emerald-500 underline decoration-zinc-200 hover:decoration-emerald-500">#{{ $log->ticket->ticket_number }}</a>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-xs text-zinc-500">
                                                    <time
                                                        datetime="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-sm text-zinc-500">No recent activity found.</p>
                                </div>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Specialties Edit Modal -->
    @if ($showSpecialtiesModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="$wire.set('showSpecialtiesModal', false)"
            @keydown.escape.window="$wire.set('showSpecialtiesModal', false)">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xl max-w-lg w-full max-h-[80vh] flex flex-col"
                @click.stop>
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Edit Specialties</h3>
                    <button @click="$wire.set('showSpecialtiesModal', false)"
                        class="text-zinc-400 hover:text-zinc-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <p class="text-sm text-zinc-500 mb-4">Select categories this operator specializes in.</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($this->categories as $category)
                            <label
                                class="relative flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors {{ in_array($category->id, $selectedCategories) ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30' : 'bg-zinc-50 border-zinc-200 dark:bg-zinc-900 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}"
                                    class="w-4 h-4 text-emerald-500 border-zinc-300 rounded focus:ring-emerald-500">
                                <span
                                    class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-zinc-100 dark:border-zinc-700 flex gap-3">
                    <button @click="$wire.set('showSpecialtiesModal', false)"
                        class="flex-1 px-4 py-2 bg-zinc-100 dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 font-medium rounded-lg transition-colors">Cancel</button>
                    <button wire:click="updateSpecialties"
                        class="flex-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-lg transition-colors">Save
                        Changes</button>
                </div>
            </div>
        </div>
    @endif
</div>
