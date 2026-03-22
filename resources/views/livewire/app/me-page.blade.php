<div class="animate-enter">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Me</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Your profile, stats, and settings</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left Column: Profile + Stats --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Profile Card --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    {{-- Avatar --}}
                    <div class="relative shrink-0">
                        @if (Auth::user()->avatar)
                            <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-14 h-14 rounded-full object-cover">
                        @else
                            <div class="flex items-center justify-center w-14 h-14 bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 rounded-full text-lg font-semibold">
                                {{ Auth::user()->initials() }}
                            </div>
                        @endif
                        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-zinc-900 {{ Auth::user()->is_available ? 'bg-green-500' : 'bg-zinc-400' }}"></span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        @if ($editingName)
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="name" wire:keydown.enter="saveName" wire:keydown.escape="cancelEditName"
                                    class="px-2 py-1 text-base font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-300 dark:border-zinc-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
                                    autofocus>
                                <button wire:click="saveName" class="text-emerald-500 hover:text-emerald-600 text-sm font-medium">Save</button>
                                <button wire:click="cancelEditName" class="text-zinc-400 hover:text-zinc-500 text-sm">Cancel</button>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-zinc-900 dark:text-white truncate">{{ Auth::user()->name }}</h2>
                                <button wire:click="$set('editingName', true)" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors" title="Edit name">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">{{ Auth::user()->email }}</p>

                        <div class="flex items-center gap-3 mt-2">
                            @foreach ($this->userTeams as $team)
                                <span class="inline-flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    @if ($team->color)
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $team->color }}"></span>
                                    @endif
                                    {{ $team->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Availability Toggle --}}
                    <button wire:click="toggleAvailability"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full border transition-colors shrink-0
                            {{ Auth::user()->is_available
                                ? 'bg-green-500/10 border-green-500/20 text-green-600 dark:text-green-400'
                                : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-500' }}">
                        <span class="w-2 h-2 rounded-full {{ Auth::user()->is_available ? 'bg-green-500 animate-pulse' : 'bg-zinc-400' }}"></span>
                        <span class="text-xs font-medium">{{ Auth::user()->is_available ? 'Available' : 'Unavailable' }}</span>
                    </button>
                </div>
            </div>

            {{-- This Week Stats --}}
            <div>
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">This Week</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Resolved</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $this->resolvedThisWeek }}</p>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Pending</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $this->pendingCount }}</p>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Unread</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ $this->unreadNotifications }}</p>
                    </div>
                </div>
            </div>

            {{-- Specialties --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Specialties</h3>
                    @if ($editingSpecialties)
                        <div class="flex items-center gap-2">
                            <button wire:click="saveSpecialties" class="text-xs font-medium text-emerald-500 hover:text-emerald-600">Save</button>
                            <button wire:click="$set('editingSpecialties', false)" class="text-xs text-zinc-400 hover:text-zinc-500">Cancel</button>
                        </div>
                    @else
                        <button wire:click="$set('editingSpecialties', true)" class="text-xs text-emerald-500 hover:text-emerald-400 transition-colors">Edit</button>
                    @endif
                </div>

                <div class="px-5 py-4">
                    @if ($editingSpecialties)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->categories as $category)
                                @php $isSelected = in_array((string) $category->id, $selectedCategories); @endphp
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm cursor-pointer transition-colors border
                                    {{ $isSelected
                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30'
                                        : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                                    <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="sr-only">
                                    {{ $category->name }}
                                </label>
                            @endforeach
                        </div>
                    @else
                        @php $userCategories = Auth::user()->categories; @endphp
                        @if ($userCategories->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($userCategories as $category)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No specialties selected</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: KB Search + Account Settings --}}
        <div class="space-y-6">
            {{-- KB Search --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Knowledge Base</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Search articles</p>
                </div>

                <div class="px-5 py-3">
                    <input type="text" wire:model.live.debounce.300ms="kbSearch" placeholder="Search articles..."
                        class="w-full px-3 py-1.5 text-sm bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 placeholder-zinc-400 dark:placeholder-zinc-500">
                </div>

                @if ($this->kbResults->isNotEmpty())
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($this->kbResults as $article)
                            <a href="{{ route('kb.public.article', ['company' => Auth::user()->company->slug, 'article' => $article->slug]) }}"
                                target="_blank"
                                class="block px-5 py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors no-underline">
                                <p class="text-sm text-zinc-800 dark:text-zinc-200 truncate">{{ $article->title }}</p>
                                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-0.5">Updated {{ $article->updated_at->diffForHumans() }}</p>
                            </a>
                        @endforeach
                    </div>
                @elseif (strlen($kbSearch) >= 2)
                    <div class="px-5 py-6 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No articles found</p>
                    </div>
                @endif
            </div>

            {{-- Account Settings --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Account Settings</h3>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    <a href="{{ route('settings.security', Auth::user()->company->slug) }}" wire:navigate
                        class="flex items-center justify-between px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors no-underline">
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Password & Security</span>
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('two-factor.show', Auth::user()->company->slug) }}" wire:navigate
                        class="flex items-center justify-between px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors no-underline">
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Two-Factor Authentication</span>
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('appearance.edit', Auth::user()->company->slug) }}" wire:navigate
                        class="flex items-center justify-between px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors no-underline">
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Appearance</span>
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a href="{{ route('notifications.preferences', Auth::user()->company->slug) }}" wire:navigate
                        class="flex items-center justify-between px-5 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors no-underline">
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Notification Preferences</span>
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
