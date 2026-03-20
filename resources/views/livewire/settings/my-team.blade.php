<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('My Teams') }}</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('View the teams you belong to and your teammates') }}</p>
    </div>

    <div class="w-4xl space-y-6">
        @forelse($this->teams as $team)
            <div
                class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                {{-- Team header --}}
                <div class="flex items-center gap-3 px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold text-white"
                        style="background-color: {{ $team->color ?? '#14b8a6' }}">
                        {{ strtoupper(substr($team->name, 0, 1)) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $team->name }}</h3>
                        @if ($team->description)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 truncate">{{ $team->description }}
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400 shrink-0">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            {{ $team->members_count }} {{ Str::plural('member', $team->members_count) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                            </svg>
                            {{ $team->tickets_count }} {{ Str::plural('ticket', $team->tickets_count) }}
                        </span>
                    </div>
                </div>

                {{-- Your role badge --}}
                @php
                    $myMembership = $team->members->firstWhere('id', auth()->id());
                    $myRole = $myMembership?->pivot?->role ?? 'member';
                @endphp
                <div class="px-5 py-2.5 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Your role:') }}</span>
                    <span
                        class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                            {{ $myRole === 'lead' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' }}">
                        {{ ucfirst($myRole) }}
                    </span>
                </div>

                {{-- Members list --}}
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($team->members as $member)
                        <div
                            class="flex items-center gap-3 px-5 py-3 {{ $member->id === auth()->id() ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : '' }}">
                            <span
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 shrink-0">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $member->name }}
                                    @if ($member->id === auth()->id())
                                        <span class="text-xs text-zinc-400">({{ __('you') }})</span>
                                    @endif
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $member->email }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium shrink-0
                                    {{ $member->pivot->role === 'lead' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ ucfirst($member->pivot->role) }}
                            </span>
                            @if ($member->is_available)
                                <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 shrink-0"
                                    title="{{ __('Available') }}"></span>
                            @else
                                <span class="flex h-2.5 w-2.5 rounded-full bg-zinc-300 dark:bg-zinc-600 shrink-0"
                                    title="{{ __('Unavailable') }}"></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div
                class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-8 text-center">
                <svg class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor"
                    stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __("You haven't been assigned to any teams yet.") }}</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                    {{ __('Your administrator will add you to a team when needed.') }}</p>
            </div>
        @endforelse
    </div>
</div>
