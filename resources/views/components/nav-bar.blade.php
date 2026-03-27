{{-- =====================================================================
Navbar — local state only; $store.ui.darkMode / lang / t() from $store.ui
===================================================================== --}}

{{-- Utility bar is always pinned to the very top --}}
<div class="relative z-50 w-full transition-colors duration-300" :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">
    <div class="mx-auto flex h-7 max-w-7xl items-center justify-end gap-6 px-6">
        @auth
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="cursor-pointer text-xs font-medium transition-colors duration-200"
                    :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
                    x-text="$store.ui.t('utilityLogout')"></button>
            </form>
        @else
            <a href="{{ route('login') }}" class="text-xs font-medium transition-colors duration-200"
                :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
                x-text="$store.ui.t('utilitySignIn')"></a>
        @endauth
        <a href="{{ route('help-center') }}" class="text-xs font-medium transition-colors duration-200"
            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
            x-text="$store.ui.t('utilityHelpCenter')"></a>
        <a href="{{ route('about') }}" class="text-xs font-medium transition-colors duration-200"
            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'">About
            us</a>
        <a href="{{ route('contact') }}" class="text-xs font-medium transition-colors duration-200"
            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-[#68737D] hover:text-[#17494D]'"
            x-text="$store.ui.t('utilityContactUs')"></a>
    </div>
</div>

<div x-data="{
        langOpen: false,
        mobileOpen: false,
        activeDropdown: null,
        navHidden: false,
        isAtTop: true,
        hideThreshold: 4,
        showThreshold: 6,
        lastScrollY: 0,
        ticking: false,
        openDropdown(name) { this.activeDropdown = name },
        closeDropdown() { this.activeDropdown = null },
        handleScroll() {
            const currentScrollY = window.scrollY || 0;
            const scrollDelta = currentScrollY - this.lastScrollY;
            this.isAtTop = currentScrollY <= 28;

            if (currentScrollY <= 12) {
                this.navHidden = false;
            } else if (scrollDelta > this.hideThreshold) {
                this.navHidden = true;
            } else if (scrollDelta < -this.showThreshold) {
                this.navHidden = false;
            }

            this.lastScrollY = currentScrollY;
            this.ticking = false;
        },
        init() {
            this.lastScrollY = window.scrollY || 0;

            window.addEventListener('scroll', () => {
                if (this.ticking) {
                    return;
                }

                this.ticking = true;

                window.requestAnimationFrame(() => {
                    this.handleScroll();
                });
            }, { passive: true });

            this.$watch('mobileOpen', (isOpen) => {
                if (isOpen) {
                    this.navHidden = false;
                }
            });
        },
    }" class="fixed inset-x-0 z-40 transition-[top] duration-300 ease-out motion-reduce:transition-none"
    :class="navHidden ? '-top-16' : (isAtTop ? 'top-7' : 'top-0')">

    <nav class="w-full transition-colors duration-300" :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">

        {{-- Main bar (h-[72px] taller) --}}
        <div class="mx-auto flex h-17 max-w-7xl items-center justify-between px-6">

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
                <div class="relative pb-8 -mb-8" @mouseenter="openDropdown('company')" @mouseleave="closeDropdown()">
                    <button type="button" :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#17494D]'"
                        class="navlink-btn group relative flex items-center gap-1 rounded-lg px-4 py-2.5 text-[15px] font-medium transition-colors duration-200">
                        <span x-text="$store.ui.t('companyMenu')"></span>
                        <svg class="h-3.5 w-3.5 transition-transform duration-200"
                            :class="activeDropdown === 'company' ? 'rotate-180' : ''" viewBox="0 0 12 12" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="2.5 4.5 6 8 9.5 4.5" />
                        </svg>
                        <span
                            class="absolute bottom-0 left-2 h-[2.5px] w-0 origin-left rounded-full transition-all duration-300 ease-out group-hover:w-[calc(100%-1rem)]"
                            :class="$store.ui.darkMode ? 'bg-white' : 'bg-[#17494D]'"></span>
                    </button>
                </div>
                <a href="{{ route('contact') }}" :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#17494D]'"
                    class="navlink-btn group relative rounded-lg px-4 py-2.5 text-[15px] font-medium transition-colors duration-200">
                    Contact
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
                    <svg x-show="!$store.ui.darkMode" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="absolute" width="18" height="18" viewBox="0 0 18 18"
                        fill="none">
                        <path d="M15.5 11.5A7 7 0 016.5 2.5a7 7 0 109 9z" fill="none" stroke="currentColor"
                            stroke-width="1.4" stroke-linecap="round" />
                    </svg>
                    {{-- Sun icon (shown in dark mode) — outline only, no yellow --}}
                    <svg x-show="$store.ui.darkMode" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="absolute" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" style="display:none">
                        <circle cx="12" cy="12" r="4" />
                        <path
                            d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41" />
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
                        <button
                            @click="$store.ui.showLoading(400); setTimeout(() => { $store.ui.lang = 'English'; langOpen = false }, 150)"
                            :class="[
                            $store.ui.lang === 'English' ? 'font-bold' : 'font-normal',
                            $store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-50'
                        ]"
                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm transition-colors duration-150">
                            <span class="text-lg leading-none">&#127468;&#127463;</span>
                            English
                        </button>
                        <div :class="$store.ui.darkMode ? 'border-gray-700' : 'border-gray-200'" class="border-t"></div>
                        <button
                            @click="$store.ui.showLoading(400); setTimeout(() => { $store.ui.lang = 'French'; langOpen = false }, 150)"
                            :class="[
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
                                    class="rounded-full bg-[#219653] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[#4cc944] hover:shadow-md">
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
            <div class="w-full max-w-6xl rounded-b-2xl border p-4 shadow-xl drop-shadow-lg"
                :class="$store.ui.darkMode ? 'bg-[#111827] border-gray-700' : 'bg-white border-gray-200'">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                    <article class="rounded-xl border p-2.5"
                        :class="$store.ui.darkMode ? 'border-gray-700 bg-gray-900' : 'border-gray-200 bg-[#eef3f7]'">
                        <img src="https://www.freshworks.com/_next/image/?url=https%3A%2F%2Fdam.freshworks.com%2Fm%2F3569eabfd1b17a84%2Foriginal%2FNAV_FS.webp&w=828&q=75"
                            alt="Product card 1" class="h-32 w-full rounded-lg object-cover" />
                        <h4 class="mt-2 text-lg font-semibold"
                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('productCard1Title')"></h4>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-300' : 'text-[#4b5563]'"
                            x-text="$store.ui.t('productCard1Desc')"></p>
                        <a href="{{ route('help-center') }}" class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-[#6d28d9]">
                            <span x-text="$store.ui.t('productLearnMore')"></span>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                        <div class="mt-2 border-t pt-2"
                            :class="$store.ui.darkMode ? 'border-gray-700' : 'border-gray-200'">
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                                x-text="$store.ui.t('productFeatured')"></p>
                            <p class="mt-1 text-xs font-semibold"
                                :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#111827]'"
                                x-text="$store.ui.t('productTour')"></p>
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                x-text="$store.ui.t('productTourDesc1')"></p>
                            <p class="mt-1.5 text-xs font-semibold"
                                :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#111827]'"
                                x-text="$store.ui.t('productWhatsNew')"></p>
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                x-text="$store.ui.t('productUpdateDesc')"></p>
                        </div>
                    </article>

                    <article class="rounded-xl border p-2.5"
                        :class="$store.ui.darkMode ? 'border-gray-700 bg-gray-900' : 'border-gray-200 bg-[#eef6f2]'">
                        <img src="https://www.freshworks.com/_next/image/?url=https%3A%2F%2Fdam.freshworks.com%2Fm%2F5884fa4118093c51%2Foriginal%2FNAV_FD_OMNI.webp&w=828&q=75"
                            alt="Product card 2" class="h-32 w-full rounded-lg object-cover" />
                        <h4 class="mt-2 text-lg font-semibold"
                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('productCard2Title')"></h4>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-300' : 'text-[#4b5563]'"
                            x-text="$store.ui.t('productCard2Desc')"></p>
                        <a href="{{ route('help-center') }}" class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-[#6d28d9]">
                            <span x-text="$store.ui.t('productLearnMore')"></span>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                        <div class="mt-2 border-t pt-2"
                            :class="$store.ui.darkMode ? 'border-gray-700' : 'border-gray-200'">
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                                x-text="$store.ui.t('productFeatured')"></p>
                            <p class="mt-1 text-xs font-semibold"
                                :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#111827]'"
                                x-text="$store.ui.t('productTour')"></p>
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                x-text="$store.ui.t('productTourDesc2')"></p>
                            <p class="mt-1.5 text-xs font-semibold"
                                :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#111827]'"
                                x-text="$store.ui.t('productWhatsNew')"></p>
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                x-text="$store.ui.t('productUpdateDesc')"></p>
                        </div>
                    </article>

                    <article class="rounded-xl border p-2.5"
                        :class="$store.ui.darkMode ? 'border-gray-700 bg-gray-900' : 'border-gray-200 bg-[#eef6f2]'">
                        <img src="https://www.freshworks.com/_next/image/?url=https%3A%2F%2Fdam.freshworks.com%2Fm%2F8057082e9380527%2Foriginal%2FNAV_FD.webp&w=828&q=75"
                            alt="Product card 3" class="h-32 w-full rounded-lg object-cover" />
                        <h4 class="mt-2 text-lg font-semibold"
                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('productCard3Title')"></h4>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-300' : 'text-[#4b5563]'"
                            x-text="$store.ui.t('productCard3Desc')"></p>
                        <a href="{{ route('help-center') }}" class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-[#6d28d9]">
                            <span x-text="$store.ui.t('productLearnMore')"></span>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                        <div class="mt-2 border-t pt-2"
                            :class="$store.ui.darkMode ? 'border-gray-700' : 'border-gray-200'">
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                                x-text="$store.ui.t('productFeatured')"></p>
                            <p class="mt-1 text-xs font-semibold"
                                :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#111827]'"
                                x-text="$store.ui.t('productTour')"></p>
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                x-text="$store.ui.t('productTourDesc3')"></p>
                            <p class="mt-1.5 text-xs font-semibold"
                                :class="$store.ui.darkMode ? 'text-gray-200' : 'text-[#111827]'"
                                x-text="$store.ui.t('productWhatsNew')"></p>
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                x-text="$store.ui.t('productUpdateDesc')"></p>
                        </div>
                    </article>
                </div>

                <a href="{{ route('help-center') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-[#6d28d9]">
                    <span x-text="$store.ui.t('productAllTrials')"></span>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14m-6-6 6 6-6 6" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- MEGA DROPDOWN Solutions (Platform-style: sections + trending) --}}
        <div x-show="activeDropdown === 'solutions'" @mouseenter="openDropdown('solutions')"
            @mouseleave="closeDropdown()" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="absolute inset-x-0 top-full z-40 flex justify-center px-6" style="display:none">
            <div class="w-full max-w-7xl rounded-b-2xl border p-5 shadow-xl drop-shadow-lg"
                :class="$store.ui.darkMode ? 'bg-[#111827] border-gray-700' : 'bg-white border-gray-200'">
                <div class="grid grid-cols-12 gap-5">
                    <div class="col-span-12 lg:col-span-6">
                        <h3 class="mb-3 text-xl font-bold tracking-tight"
                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('platform')"></h3>

                        <div class="grid grid-cols-[minmax(0,1fr)_180px] gap-3.5">
                            <a href="{{ route('help-center') }}" class="group block">
                                <div class="overflow-hidden rounded-xl border"
                                    :class="$store.ui.darkMode ? 'border-gray-700 bg-gray-900' : 'border-gray-300 bg-white'">
                                    <img src="https://www.freshworks.com/_next/image/?url=https%3A%2F%2Fdam.freshworks.com%2Fm%2F3569eabfd1b17a84%2Foriginal%2FNAV_FS.webp&w=828&q=75"
                                        alt="Solutions preview"
                                        class="h-36 w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" />
                                </div>

                                <div class="pt-2.5">
                                    <h4 class="text-lg font-semibold leading-tight"
                                        :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#111827]'"
                                        x-text="$store.ui.t('navProductFeatureTitle')"></h4>
                                    <p class="mt-1 text-sm leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-300' : 'text-[#374151]'"
                                        x-text="$store.ui.t('navProductFeatureDescription')"></p>
                                    <div
                                        class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-[#6d28d9]">
                                        <span x-text="$store.ui.t('navProductFeatureCta')"></span>
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14m-6-6 6 6-6 6" />
                                        </svg>
                                    </div>
                                </div>
                            </a>

                            <div class="pt-0.5">
                                <div class="space-y-2">
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2 rounded-lg px-1 py-1 text-sm"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('platformOverview')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2 rounded-lg px-1 py-1 text-sm"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('integrations')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2 rounded-lg px-1 py-1 text-sm"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('latestInnovations')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2 rounded-lg px-1 py-1 text-sm"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('appMarketplace')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2 rounded-lg px-1 py-1 text-sm"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('developers')"></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-6">
                        <h3 class="mb-3 text-xl font-bold tracking-tight"
                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('trending')"></h3>

                        <div class="space-y-2">
                            <a href="{{ route('help-center') }}" class="group flex items-start gap-3 rounded-xl p-2 transition-colors"
                                :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-white'">
                                <img src="{{ asset('images/Personnes/reports.png') }}" alt="Solution trend 1"
                                    class="h-14 w-14 rounded-lg object-cover" />
                                <div>
                                    <div class="text-sm font-semibold leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#1f2937]'"
                                        x-text="$store.ui.t('trendItem1')"></div>
                                    <p class="mt-0.5 text-xs leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                        x-text="$store.ui.t('trendDesc1')"></p>
                                </div>
                            </a>
                            <a href="{{ route('help-center') }}" class="group flex items-start gap-3 rounded-xl p-2 transition-colors"
                                :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-white'">
                                <img src="{{ asset('images/Personnes/ticket view.png') }}" alt="Solution trend 2"
                                    class="h-14 w-14 rounded-lg object-cover" />
                                <div>
                                    <div class="text-sm font-semibold leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#1f2937]'"
                                        x-text="$store.ui.t('trendItem2')"></div>
                                    <p class="mt-0.5 text-xs leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                        x-text="$store.ui.t('trendDesc2')"></p>
                                </div>
                            </a>
                            <a href="{{ route('help-center') }}" class="group flex items-start gap-3 rounded-xl p-2 transition-colors"
                                :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-white'">
                                <img src="{{ asset('images/Personnes/Automatin.png') }}" alt="Solution trend 3"
                                    class="h-14 w-14 rounded-lg object-cover" />
                                <div>
                                    <div class="text-sm font-semibold leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#1f2937]'"
                                        x-text="$store.ui.t('trendItem3')"></div>
                                    <p class="mt-0.5 text-xs leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                        x-text="$store.ui.t('trendDesc3')"></p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MEGA DROPDOWN Resources (Learn + Connect) --}}
        <div x-show="activeDropdown === 'resources'" @mouseenter="openDropdown('resources')"
            @mouseleave="closeDropdown()" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="absolute inset-x-0 top-full z-40 flex justify-center px-6" style="display:none">
            <div class="w-full max-w-7xl rounded-b-2xl border p-6 shadow-xl drop-shadow-lg"
                :class="$store.ui.darkMode ? 'bg-[#111827] border-gray-700' : 'bg-white border-gray-200'">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 lg:col-span-6">
                        <h3 class="mb-3 text-2xl font-bold tracking-tight"
                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'" x-text="$store.ui.t('learn')">
                        </h3>

                        <div class="grid grid-cols-[minmax(0,1fr)_200px] gap-4">
                            <a href="{{ route('help-center') }}" class="group block">
                                <div class="overflow-hidden rounded-2xl border"
                                    :class="$store.ui.darkMode ? 'border-gray-700 bg-gray-900' : 'border-gray-300 bg-white'">
                                    <img src="{{ asset('images/Personnes/ticketlist.png') }}" alt="Resources preview"
                                        class="h-44 w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" />
                                </div>

                                <div class="pt-3.5">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xl font-semibold leading-tight"
                                            :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#111827]'"
                                            x-text="$store.ui.t('navResourceFeatureTitle')"></h4>
                                    </div>
                                    <p class="mt-1 text-sm leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-300' : 'text-[#374151]'"
                                        x-text="$store.ui.t('navResourceFeatureDescription')"></p>
                                    <div
                                        class="mt-2.5 inline-flex items-center gap-2 text-sm font-semibold text-[#6d28d9]">
                                        <span x-text="$store.ui.t('navResourceFeatureCta')"></span>
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14m-6-6 6 6-6 6" />
                                        </svg>
                                    </div>
                                </div>
                            </a>

                            <div class="pt-1">
                                <div class="space-y-2.5">
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2.5 rounded-lg px-1 py-1 text-base"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('blog')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2.5 rounded-lg px-1 py-1 text-base"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('documentation')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2.5 rounded-lg px-1 py-1 text-base"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('webinars')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2.5 rounded-lg px-1 py-1 text-base"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('academy')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2.5 rounded-lg px-1 py-1 text-base"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('community')"></a>
                                    <a href="{{ route('help-center') }}" class="group flex items-center gap-2.5 rounded-lg px-1 py-1 text-base"
                                        :class="$store.ui.darkMode ? 'text-gray-200 hover:text-white' : 'text-[#111827] hover:text-black'"
                                        x-text="$store.ui.t('events')"></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-6">
                        <h3 class="mb-4 text-2xl font-bold tracking-tight"
                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('trending')"></h3>

                        <div class="space-y-2.5">
                            <a href="{{ route('help-center') }}" class="group flex items-start gap-3.5 rounded-xl p-2 transition-colors"
                                :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-white'">
                                <img src="{{ asset('images/Personnes/reports.png') }}" alt="Resource trending 1"
                                    class="h-16 w-16 rounded-xl object-cover" />
                                <div>
                                    <div class="text-base font-semibold leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#1f2937]'"
                                        x-text="$store.ui.t('trendItem1')"></div>
                                    <p class="mt-0.5 text-sm leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                        x-text="$store.ui.t('trendDesc1')"></p>
                                </div>
                            </a>
                            <a href="{{ route('help-center') }}" class="group flex items-start gap-3.5 rounded-xl p-2 transition-colors"
                                :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-white'">
                                <img src="{{ asset('images/Personnes/ticket view.png') }}" alt="Resource trending 2"
                                    class="h-16 w-16 rounded-xl object-cover" />
                                <div>
                                    <div class="text-base font-semibold leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#1f2937]'"
                                        x-text="$store.ui.t('trendItem2')"></div>
                                    <p class="mt-0.5 text-sm leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                        x-text="$store.ui.t('trendDesc2')"></p>
                                </div>
                            </a>
                            <a href="{{ route('help-center') }}" class="group flex items-start gap-3.5 rounded-xl p-2 transition-colors"
                                :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-white'">
                                <img src="{{ asset('images/Personnes/Automatin.png') }}" alt="Resource trending 3"
                                    class="h-16 w-16 rounded-xl object-cover" />
                                <div>
                                    <div class="text-base font-semibold leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-100' : 'text-[#1f2937]'"
                                        x-text="$store.ui.t('trendItem3')"></div>
                                    <p class="mt-0.5 text-sm leading-snug"
                                        :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#4b5563]'"
                                        x-text="$store.ui.t('trendDesc3')"></p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DROPDOWN Company (small menu) --}}
        <div x-show="activeDropdown === 'company'" @mouseenter="openDropdown('company')" @mouseleave="closeDropdown()"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
            class="absolute inset-x-0 top-full z-40 flex justify-center px-6" style="display:none">
            <div class="w-full max-w-md rounded-b-2xl border p-3 shadow-xl drop-shadow-lg"
                :class="$store.ui.darkMode ? 'bg-[#111827] border-gray-700' : 'bg-white border-gray-200'">
                <div class="space-y-1.5">
                    <a href="{{ route('about') }}" class="block rounded-lg px-3 py-2.5 transition-colors"
                        :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-gray-50'">
                        <p class="text-sm font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('companyAbout')"></p>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#6b7280]'"
                            x-text="$store.ui.t('companyAboutDesc')"></p>
                    </a>
                    <a href="{{ route('help-center') }}" class="block rounded-lg px-3 py-2.5 transition-colors"
                        :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-gray-50'">
                        <p class="text-sm font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('utilityHelpCenter')"></p>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#6b7280]'"
                            x-text="$store.ui.t('companyHelpCenterDesc')"></p>
                    </a>
                    <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2.5 transition-colors"
                        :class="$store.ui.darkMode ? 'hover:bg-white/5' : 'hover:bg-gray-50'">
                        <p class="text-sm font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#111827]'"
                            x-text="$store.ui.t('utilityContactUs')"></p>
                        <p class="mt-0.5 text-xs" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-[#6b7280]'"
                            x-text="$store.ui.t('companyContactDesc')"></p>
                    </a>
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
                        <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-4 space-y-1">
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('platformOverview')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('integrations')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('latestInnovations')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('appMarketplace')"></a>
                        <a href="{{ route('help-center') }}"
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
                        <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-4 space-y-1">
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('customerService')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('itSupport')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('hrTeams')"></a>
                        <a href="{{ route('help-center') }}"
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
                        <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-4 space-y-1">
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('blog')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('documentation')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('webinars')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('community')"></a>
                    </div>
                </div>
                {{-- Mobile: Company --}}
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                        :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                        <span x-text="$store.ui.t('companyMenu')"></span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-4 space-y-1">
                        <a href="{{ route('about') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('companyAbout')"></a>
                        <a href="{{ route('help-center') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('utilityHelpCenter')"></a>
                        <a href="{{ route('contact') }}"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'"
                            class="block rounded-lg px-3 py-2 text-sm transition-colors duration-150"
                            x-text="$store.ui.t('utilityContactUs')"></a>
                    </div>
                </div>
                <a href="{{ route('help-center') }}"
                    :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                    Help Center
                </a>
                <a href="{{ route('contact') }}"
                    :class="$store.ui.darkMode ? 'text-gray-200 hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                    Contact
                </a>
                {{-- Mobile: Auth --}}
                <div class="space-y-2 border-t pt-4"
                    :class="$store.ui.darkMode ? 'border-gray-800' : 'border-gray-200'">
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
</div>

<div aria-hidden="true" class="h-23 transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'"></div>