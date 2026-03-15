{{-- =====================================================================
Navbar — local state only; $store.ui.darkMode / lang / t() from $store.ui
===================================================================== --}}

{{-- ════════ UTILITY BAR ════════ --}}
<div class="navbar-animate relative z-50 w-full transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-gray-900' : 'bg-white'">
    <div class="mx-auto flex h-8 max-w-7xl items-center justify-end gap-6 px-6">
        @auth
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                    class="text-xs font-medium transition-colors duration-200 cursor-pointer"
                    :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
                    x-text="$store.ui.t('utilityLogout')"></button>
            </form>
        @else
            <a href="{{ route('login') }}"
                class="text-xs font-medium transition-colors duration-200"
                :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
                x-text="$store.ui.t('utilitySignIn')"></a>
        @endauth
        <a href="#"
            class="text-xs font-medium transition-colors duration-200"
            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
            x-text="$store.ui.t('utilityHelpCenter')"></a>
        <a href="#"
            class="text-xs font-medium transition-colors duration-200"
            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
            x-text="$store.ui.t('utilityCompany')"></a>
        <a href="#"
            class="text-xs font-medium transition-colors duration-200"
            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
            x-text="$store.ui.t('utilityContactUs')"></a>
    </div>
</div>

<nav x-data="{
        langOpen: false,
        mobileOpen: false,
        activeDropdown: null,
        openDropdown(name) { this.activeDropdown = name },
        closeDropdown() { this.activeDropdown = null },
    }" :class="$store.ui.darkMode ? 'bg-gray-950' : 'bg-[#fffff]'"
    class="navbar-animate relative z-50 w-full transition-colors duration-300">

    {{-- Main bar (h-[72px] taller) --}}
    <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-6">

        {{-- LEFT Logo --}}
        <x-logo variant="landing" size="lg" href="/" />

        {{-- CENTER Desktop nav links --}}
        <div class="hidden flex-1 items-center justify-center gap-1 md:flex">
            <div class="relative pb-8 -mb-8" @mouseenter="openDropdown('products')" @mouseleave="closeDropdown()">
                <button type="button" :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#17494D]'"
                    class="navlink-btn group relative flex items-center gap-1 rounded-lg px-4 py-2.5 text-[15px] font-medium transition-colors duration-200">
                    <span x-text="$store.ui.t('products')"></span>
                    <svg class="h-3.5 w-3.5 transition-transform duration-200"
                        :class="activeDropdown === 'products' ? 'rotate-180' : ''" viewBox="0 0 12 12" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="2.5 4.5 6 8 9.5 4.5" />
                    </svg>
                    <span
                        class="absolute bottom-0 left-2 h-[2.5px] w-0 origin-left rounded-full transition-all duration-300 ease-out group-hover:w-[calc(100%-1rem)]"
                        :class="$store.ui.darkMode ? 'bg-white' : 'bg-[#17494D]'"></span>
                </button>
            </div>
            <div class="relative pb-8 -mb-8" @mouseenter="openDropdown('solutions')" @mouseleave="closeDropdown()">
                <button type="button" :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#17494D]'"
                    class="navlink-btn group relative flex items-center gap-1 rounded-lg px-4 py-2.5 text-[15px] font-medium transition-colors duration-200">
                    <span x-text="$store.ui.t('solutions')"></span>
                    <svg class="h-3.5 w-3.5 transition-transform duration-200"
                        :class="activeDropdown === 'solutions' ? 'rotate-180' : ''" viewBox="0 0 12 12" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="2.5 4.5 6 8 9.5 4.5" />
                    </svg>
                    <span
                        class="absolute bottom-0 left-2 h-[2.5px] w-0 origin-left rounded-full transition-all duration-300 ease-out group-hover:w-[calc(100%-1rem)]"
                        :class="$store.ui.darkMode ? 'bg-white' : 'bg-[#17494D]'"></span>
                </button>
            </div>
            <div class="relative pb-8 -mb-8" @mouseenter="openDropdown('resources')" @mouseleave="closeDropdown()">
                <button type="button" :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#17494D]'"
                    class="navlink-btn group relative flex items-center gap-1 rounded-lg px-4 py-2.5 text-[15px] font-medium transition-colors duration-200">
                    <span x-text="$store.ui.t('resources')"></span>
                    <svg class="h-3.5 w-3.5 transition-transform duration-200"
                        :class="activeDropdown === 'resources' ? 'rotate-180' : ''" viewBox="0 0 12 12" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="2.5 4.5 6 8 9.5 4.5" />
                    </svg>
                    <span
                        class="absolute bottom-0 left-2 h-[2.5px] w-0 origin-left rounded-full transition-all duration-300 ease-out group-hover:w-[calc(100%-1rem)]"
                        :class="$store.ui.darkMode ? 'bg-white' : 'bg-[#17494D]'"></span>
                </button>
            </div>
            <a href="#" :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#17494D]'"
                class="navlink-btn group relative rounded-lg px-4 py-2.5 text-[15px] font-medium transition-colors duration-200">
                <span x-text="$store.ui.t('pricing')"></span>
                <span
                    class="absolute bottom-0 left-2 h-[2.5px] w-0 origin-left rounded-full transition-all duration-300 ease-out group-hover:w-[calc(100%-1rem)]"
                    :class="$store.ui.darkMode ? 'bg-white' : 'bg-[#17494D]'"></span>
            </a>
        </div>

        {{-- RIGHT Controls --}}
        <div class="flex items-center gap-4">
            {{-- Dark mode toggle — clean fade --}}
            <button type="button"
                @click="$store.ui.showLoading(400); setTimeout(() => { $store.ui.darkMode = !$store.ui.darkMode }, 150)"
                class="relative flex h-9 w-9 items-center justify-center rounded-full transition-colors duration-200"
                :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-[#1F1F1F] hover:bg-gray-100'"
                title="Toggle dark mode">
                {{-- Moon icon (shown in light mode) --}}
                <svg x-show="!$store.ui.darkMode"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M15.5 11.5A7 7 0 016.5 2.5a7 7 0 109 9z" fill="none" stroke="currentColor"
                        stroke-width="1.4" stroke-linecap="round" />
                </svg>
                {{-- Sun icon (shown in dark mode) — outline only, no yellow --}}
                <svg x-show="$store.ui.darkMode"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                    style="display:none">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41" />
                </svg>
            </button>

            {{-- Language dropdown — globe icon only, flags in menu --}}
            <div class="relative" @click.outside="langOpen = false">
                <button type="button" @click="langOpen = !langOpen"
                    :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-[#1F1F1F] hover:bg-gray-100'"
                    class="flex h-9 w-9 items-center justify-center rounded-full transition-colors duration-200"
                    title="Language">
                    {{-- Globe icon --}}
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3" />
                        <ellipse cx="8" cy="8" rx="2.8" ry="6.5" stroke="currentColor" stroke-width="1.3" />
                        <line x1="1.5" y1="6" x2="14.5" y2="6" stroke="currentColor" stroke-width="1.3" />
                        <line x1="1.5" y1="10" x2="14.5" y2="10" stroke="currentColor" stroke-width="1.3" />
                    </svg>
                </button>

                <div x-show="langOpen" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    :class="$store.ui.darkMode ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'"
                    class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border shadow-xl drop-shadow-lg"
                    style="display:none">
                    <button @click="$store.ui.showLoading(400); setTimeout(() => { $store.ui.lang = 'English'; langOpen = false }, 150)" :class="[
                            $store.ui.lang === 'English' ? 'font-bold' : 'font-normal',
                            $store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-50'
                        ]"
                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm transition-colors duration-150">
                        <span class="text-lg leading-none">&#127468;&#127463;</span>
                        English
                    </button>
                    <div :class="$store.ui.darkMode ? 'border-gray-700' : 'border-gray-200'" class="border-t"></div>
                    <button @click="$store.ui.showLoading(400); setTimeout(() => { $store.ui.lang = 'French'; langOpen = false }, 150)" :class="[
                            $store.ui.lang === 'French' ? 'font-bold' : 'font-normal',
                            $store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-50'
                        ]"
                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm transition-colors duration-150">
                        <span class="text-lg leading-none">&#127467;&#127479;</span>
                        Fran&ccedil;ais
                    </button>
                </div>
            </div>

            {{-- CTA buttons (desktop) --}}
            <div class="hidden items-center gap-3 md:flex">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="rounded-full bg-[#5EDB56] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#4cc944] hover:shadow-md">
                            <span x-text="$store.ui.t('dashboard')"></span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            :class="$store.ui.darkMode ? 'border-gray-500 text-gray-200 hover:bg-white/10 hover:border-gray-300' : 'border-[#1F1F1F] text-[#1F1F1F] hover:bg-gray-50'"
                            class="rounded-full border px-6 py-2.5 text-sm font-semibold transition-all duration-200 hover:shadow-md">
                            <span x-text="$store.ui.t('viewDemo')"></span>
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="rounded-full bg-[#5EDB56] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#4cc944] hover:shadow-md">
                                <span x-text="$store.ui.t('tryFree')"></span>
                            </a>
                        @endif
                    @endauth
                @endif
            </div>

            {{-- Hamburger (mobile) --}}
            <button type="button" @click="mobileOpen = !mobileOpen"
                :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors duration-200 md:hidden">
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                </svg>
                <svg x-show="mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24" style="display:none">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- MEGA DROPDOWN Products (Freshworks-style: sections + trending) --}}
    <div x-show="activeDropdown === 'products'" @mouseenter="openDropdown('products')" @mouseleave="closeDropdown()"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="absolute inset-x-0 top-full z-40 flex justify-center px-6" style="display:none">
        <div class="flex w-full max-w-6xl gap-12 rounded-b-2xl border px-10 py-8 shadow-xl drop-shadow-lg"
            :class="$store.ui.darkMode ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
            <div class="flex-1">
                <h3 class="mb-4 text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                    x-text="$store.ui.t('platform')"></h3>
                <div class="grid grid-cols-2 gap-x-8 gap-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex items-center gap-2.5 rounded-lg px-2 py-2.5 text-[15px] transition-colors duration-150">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z" />
                        </svg>
                        <span x-text="$store.ui.t('platformOverview')"></span>
                    </a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex items-center gap-2.5 rounded-lg px-2 py-2.5 text-[15px] transition-colors duration-150">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.86-2.556a4.5 4.5 0 00-1.242-7.244l-4.5-4.5a4.5 4.5 0 00-6.364 6.364L4.34 8.284" />
                        </svg>
                        <span x-text="$store.ui.t('integrations')"></span>
                    </a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex items-center gap-2.5 rounded-lg px-2 py-2.5 text-[15px] transition-colors duration-150">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                        <span x-text="$store.ui.t('latestInnovations')"></span>
                    </a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex items-center gap-2.5 rounded-lg px-2 py-2.5 text-[15px] transition-colors duration-150">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <span x-text="$store.ui.t('appMarketplace')"></span>
                    </a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="flex items-center gap-2.5 rounded-lg px-2 py-2.5 text-[15px] transition-colors duration-150">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                        </svg>
                        <span x-text="$store.ui.t('developers')"></span>
                    </a>
                </div>
            </div>
            <div class="w-72 shrink-0 border-l pl-8"
                :class="$store.ui.darkMode ? 'border-gray-700' : 'border-gray-200'">
                <h3 class="mb-4 text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                    x-text="$store.ui.t('trending')"></h3>
                <div class="space-y-4">
                    <a href="#" class="group block">
                        <div class="text-sm font-semibold transition-colors duration-150"
                            :class="$store.ui.darkMode ? 'text-gray-200 group-hover:text-white' : 'text-gray-900 group-hover:text-brand'"
                            x-text="$store.ui.t('trendItem1')"></div>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                            x-text="$store.ui.t('trendDesc1')"></p>
                    </a>
                    <a href="#" class="group block">
                        <div class="text-sm font-semibold transition-colors duration-150"
                            :class="$store.ui.darkMode ? 'text-gray-200 group-hover:text-white' : 'text-gray-900 group-hover:text-brand'"
                            x-text="$store.ui.t('trendItem2')"></div>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                            x-text="$store.ui.t('trendDesc2')"></p>
                    </a>
                    <a href="#" class="group block">
                        <div class="text-sm font-semibold transition-colors duration-150"
                            :class="$store.ui.darkMode ? 'text-gray-200 group-hover:text-white' : 'text-gray-900 group-hover:text-brand'"
                            x-text="$store.ui.t('trendItem3')"></div>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                            x-text="$store.ui.t('trendDesc3')"></p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MEGA DROPDOWN Solutions (By Team + By Size) --}}
    <div x-show="activeDropdown === 'solutions'" @mouseenter="openDropdown('solutions')" @mouseleave="closeDropdown()"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="absolute inset-x-0 top-full z-40 flex justify-center px-6" style="display:none">
        <div class="flex w-full max-w-6xl gap-16 rounded-b-2xl border px-10 py-8 shadow-xl drop-shadow-lg"
            :class="$store.ui.darkMode ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
            <div>
                <h3 class="mb-4 text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                    x-text="$store.ui.t('byTeam')"></h3>
                <div class="space-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('customerService')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('itSupport')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('hrTeams')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('salesTeams')"></a>
                </div>
            </div>
            <div>
                <h3 class="mb-4 text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                    x-text="$store.ui.t('bySize')"></h3>
                <div class="space-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('enterprise')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('midMarket')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('smallBusiness')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('startups')"></a>
                </div>
            </div>
        </div>
    </div>

    {{-- MEGA DROPDOWN Resources (Learn + Connect) --}}
    <div x-show="activeDropdown === 'resources'" @mouseenter="openDropdown('resources')" @mouseleave="closeDropdown()"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="absolute inset-x-0 top-full z-40 flex justify-center px-6" style="display:none">
        <div class="flex w-full max-w-6xl gap-16 rounded-b-2xl border px-10 py-8 shadow-xl drop-shadow-lg"
            :class="$store.ui.darkMode ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
            <div>
                <h3 class="mb-4 text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                    x-text="$store.ui.t('learn')"></h3>
                <div class="space-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('blog')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('documentation')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('webinars')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('academy')"></a>
                </div>
            </div>
            <div>
                <h3 class="mb-4 text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                    x-text="$store.ui.t('connect')"></h3>
                <div class="space-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('community')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('events')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('partnerProgram')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-300 hover:text-white hover:bg-white/5' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                        class="block rounded-lg px-2 py-2 text-[15px] transition-colors duration-150"
                        x-text="$store.ui.t('support')"></a>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile slide-down menu --}}
    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4" x-cloak
        :class="$store.ui.darkMode ? 'bg-gray-950 border-gray-800' : 'bg-white border-gray-200'"
        class="border-b md:hidden" style="display:none">
        <div class="mx-auto max-w-7xl space-y-1 px-6 py-4">
            {{-- Mobile: Products --}}
            <div x-data="{ open: false }">
                <button @click="open = !open"
                    :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                    class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                    <span x-text="$store.ui.t('products')"></span>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                        stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 space-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('platformOverview')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('integrations')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('latestInnovations')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('appMarketplace')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('developers')"></a>
                </div>
            </div>
            {{-- Mobile: Solutions --}}
            <div x-data="{ open: false }">
                <button @click="open = !open"
                    :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                    class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                    <span x-text="$store.ui.t('solutions')"></span>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                        stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 space-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('customerService')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('itSupport')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('hrTeams')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('salesTeams')"></a>
                </div>
            </div>
            {{-- Mobile: Resources --}}
            <div x-data="{ open: false }">
                <button @click="open = !open"
                    :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                    class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                    <span x-text="$store.ui.t('resources')"></span>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                        stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 space-y-1">
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('blog')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('documentation')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('webinars')"></a>
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                        class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                        x-text="$store.ui.t('community')"></a>
                </div>
            </div>
            {{-- Mobile: Pricing --}}
            <a href="#"
                :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                <span x-text="$store.ui.t('pricing')"></span>
            </a>
            {{-- Mobile: Auth --}}
            <div class="space-y-2 border-t pt-4" :class="$store.ui.darkMode ? 'border-gray-800' : 'border-gray-200'">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="block rounded-full bg-[#5EDB56] px-5 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#4cc944] hover:shadow-md">
                            <span x-text="$store.ui.t('dashboard')"></span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            :class="$store.ui.darkMode ? 'border-gray-500 text-gray-200 hover:border-gray-300' : 'border-[#1F1F1F] text-[#1F1F1F] hover:bg-gray-50'"
                            class="block rounded-full border px-5 py-2.5 text-center text-sm font-semibold shadow-sm transition-all duration-200 hover:shadow-md">
                            <span x-text="$store.ui.t('viewDemo')"></span>
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="block rounded-full bg-[#5EDB56] px-5 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#4cc944] hover:shadow-md">
                                <span x-text="$store.ui.t('tryFree')"></span>
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>