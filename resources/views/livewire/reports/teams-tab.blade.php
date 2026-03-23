<div class="space-y-6">
    @if ($this->teamStats->isEmpty())
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-12 text-center">
            <flux:icon.user-group class="w-10 h-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
            <p class="text-zinc-500 dark:text-zinc-400">No teams have been created yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($this->teamStats as $team)
                <div
                    class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $team['color'] }}"></span>
                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $team['name'] }}</h3>
                        <span class="ml-auto text-xs text-zinc-500">{{ $team['members_count'] }}
                            {{ Str::plural('member', $team['members_count']) }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-zinc-500 mb-1">Total Tickets</p>
                            <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $team['total'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 mb-1">Open</p>
                            <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $team['open'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 mb-1">Resolved</p>
                            <p class="text-lg font-bold text-emerald-500">{{ $team['resolved'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 mb-1">Resolution Rate</p>
                            <p
                                class="text-lg font-bold {{ $team['resolution_rate'] >= 70 ? 'text-emerald-500' : ($team['resolution_rate'] >= 40 ? 'text-amber-500' : 'text-red-500') }}">
                                {{ $team['resolution_rate'] }}%
                            </p>
                        </div>
                    </div>

                    <div class="col-span-2 border-t border-zinc-100 dark:border-zinc-700 pt-3 mt-1">
                        <p class="text-xs text-zinc-500 mb-1">Avg Resolution Time</p>
                        <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $team['avg_resolution_hours'] !== null ? $team['avg_resolution_hours'] . 'h' : 'No data' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
