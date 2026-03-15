<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Helpdesk System</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|righteous:400" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Montserrat:wght@400;500;600;700&family=Raleway:wght@400;500;600&family=Poppins:wght@600;700&family=Sora:wght@600;700&family=DM+Sans:wght@500;700&family=Inter:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/welcome.css'])

    <!-- Alpine.js — ui-state store must load before Alpine starts -->
    <x-ui-state />
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data x-cloak
    class="welcome-body flex min-h-screen flex-col bg-[#ffffff] text-[#17494D] font-[Instrument_Sans,ui-sans-serif,system-ui,sans-serif] antialiased transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-black text-white' : 'bg-[#ffffff] text-[#17494D]'">

    <!-- Navigation -->
    <x-nav-bar />
    <x-loading-overlay />

    {{-- ═══════════════════════════════════════════════════════════════════
    HERO — Minimal centered w/ headline, email form, privacy note
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="hero-section" class="relative z-0 overflow-hidden"
        x-data="{ heroEmail: '', heroMsg: '', heroMsgOk: false }" style="opacity: 0;">
        {{-- Background wave — dark mode only --}}




        <div class="relative mx-auto max-w-5xl px-6 pb-16 pt-16 text-center sm:pb-20 sm:pt-24 lg:pb-10 lg:pt-17">
            {{-- Headlines container with controlled width --}}
            <div class="mx-auto max-w-4xl">
                <h1 class="text-5xl font-extralight leading-[1.1] tracking-tight sm:text-6xl lg:text-[4.5rem]"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-950'" x-text="$store.ui.t('heroHeadline1')">
                </h1>
                <h1 class="mt-1 text-5xl font-extralight leading-[1.1] tracking-tight sm:text-6xl lg:text-[4.5rem]"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-950'" x-text="$store.ui.t('heroHeadline2')">
                </h1>
            </div>

            {{-- Subtitle with better width control --}}
            <p class="mx-auto mt-6 max-w-3xl text-lg sm:mt-7 sm:text-xl lg:text-2xl"
                :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'" x-text="$store.ui.t('heroSubtitle')">
            </p>

            {{-- Flash message --}}
            <p x-show="heroMsg" x-text="heroMsg" x-transition class="mt-4 text-sm font-medium"
                :class="heroMsgOk ? 'text-green-500' : 'text-red-500'"></p>

            {{-- CTA area --}}
            @if (Route::has('login'))
                @auth
                    {{-- Logged-in → Dashboard --}}
                    <div class="mt-10 flex justify-center sm:mt-12">
                        <a href="{{ route('tickets', Auth::user()->company->slug) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-green-600 px-8 py-4 text-[1.05rem] font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-green-500 hover:shadow-xl">
                            <span x-text="$store.ui.t('heroDashboard')"></span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                @else
                    {{-- Guest → Email sign-up form --}}
                    <form
                        class="mx-auto mt-10 flex w-full max-w-[550px] flex-col items-center gap-3.5 sm:mt-12 sm:flex-row sm:max-w-[600px]"
                        @submit.prevent="
                                                                        const em = heroEmail.trim();
                                                                        if (!em || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) {
                                                                            heroMsg = $store.ui.t('heroInvalidEmail');
                                                                            heroMsgOk = false;
                                                                            return;
                                                                        }
                                                                        heroMsg = $store.ui.t('heroThankYou');
                                                                        heroMsgOk = true;
                                                                        heroEmail = '';
                                                                    ">
                        <input type="email" x-model="heroEmail"
                            class="w-full flex-1 rounded-lg border px-5 py-4 text-base outline-none transition-colors duration-200 sm:w-auto sm:px-6 sm:py-[15px] sm:text-[17px]"
                            :class="$store.ui.darkMode
                                                                            ? 'border-white/20 bg-white/5 text-white placeholder-white/40 focus:border-white/40 focus:ring-1 focus:ring-white/20'
                                                                            : 'border-gray-400 bg-white text-gray-800 placeholder-gray-400 focus:border-gray-500 focus:ring-1 focus:ring-gray-400'"
                            :placeholder="$store.ui.t('heroPlaceholder')" />
                        <button type="submit"
                            class="w-full flex-shrink-0 cursor-pointer rounded-full bg-[#5EDB56] px-8 py-4 text-base font-bold text-white transition hover:bg-green-500 sm:w-auto sm:px-[30px] sm:py-[15px] sm:text-[17px]"
                            x-text="$store.ui.t('heroTryFree')"></button>
                    </form>
                @endauth
            @endif

            {{-- Privacy note --}}
            <p class="mt-6 text-sm sm:mt-5" :class="$store.ui.darkMode ? 'text-white/35' : 'text-gray-500'">
                <span x-text="$store.ui.t('heroPrivacy')"></span>
                <a href="#" class="underline transition-colors duration-200"
                    :class="$store.ui.darkMode ? 'text-white/50 hover:text-white' : 'text-gray-700 hover:text-black'"
                    x-text="$store.ui.t('heroPrivacyLink')"></a>.
            </p>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    SCENE — Animated card strip with side photos + center showcase
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="w-full overflow-hidden py-0 transition-colors duration-300"
        :class="$store.ui.darkMode ? 'bg-gray-950' : 'bg-[#ffffff]'">
        <div class="scene mx-auto flex max-w-[1100px] items-center justify-center px-8 py-10">

            {{-- Left Far Image --}}
            <div class="side-card side-card--left-far relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #c7d2fe 0%, #a5b4fc 100%);">
                    <img src="{{ asset('images/Personnes/Personne04.jpg') }}" alt="Team member 1"
                        class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

            {{-- Left Near Image --}}
            <div class="side-card side-card--left-near relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #fda4af 0%, #fb7185 100%);">
                    <img src="{{ asset('images/Personnes/Personne03.jpg') }}" alt="Team member 2"
                        class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

            {{-- CENTER CARD --}}
            <div class="center-card relative z-10 flex-shrink-0 overflow-hidden rounded-[20px]">
                <div class="center-card__inner flex h-full">
                    <div class="flex h-full w-full items-center justify-center overflow-hidden rounded-2xl"
                        style="background: linear-gradient(135deg, #bebdbdff, #ffffffff); min-height: 410px;">
                        <img src="{{ asset('images/Personnes/Blockframe.png') }}" alt="Center image"
                            class="max-h-full max-w-full object-cover" />
                    </div>
                </div>
            </div>

            {{-- Right Near Image --}}
            <div class="side-card side-card--right-near relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #fcd34d 0%, #f59e0b 100%);">
                    <img src="{{ asset('images/Personnes/Personne02.jpg') }}" alt="Team member 3"
                        class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

            {{-- Right Far Image --}}
            <div class="side-card side-card--right-far relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #6ee7b7 0%, #34d399 100%);">
                    <img src="{{ asset('images/Personnes/Personne01.jpg') }}" alt="Team member 4"
                        class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

        </div>
    </section>

    {{-- Script for smooth entrance + scroll animations --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ── Clear navbar animation after it finishes (removes stacking context from transform) ──
            document.querySelectorAll('.navbar-animate').forEach(function (el) {
                el.addEventListener('animationend', function () {
                    el.style.animation = 'none';
                    el.style.opacity = '1';
                }, { once: true });
            });

            // ── Hero section fade-in ──
            var hero = document.getElementById('hero-section');
            if (hero) {
                setTimeout(function () {
                    hero.style.transition = 'opacity 0.9s cubic-bezier(0.16, 1, 0.3, 1)';
                    hero.style.opacity = '1';
                }, 250);
            }

            // ── Scene card scroll animations ──
            var scene = document.querySelector('.scene');
            if (!scene) return;

            var observer = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) {
                    observer.disconnect();

                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            scene.classList.add('show-center');

                            setTimeout(function () {
                                scene.classList.add('show-near');
                            }, 800);

                            setTimeout(function () {
                                scene.classList.add('show-far');
                            }, 1000);
                        });
                    });
                }
            }, { threshold: 0.15 });

            observer.observe(scene);
        });
    </script>

    {{-- ═══════════════════════════════════════════════════════════════════
    BRAND STAGE — Cycling brand logo carousel
    ═══════════════════════════════════════════════════════════════════ --}}
    <x-brand-stage />


    {{-- ═══════════════════════════════════════════════════════════════════
    DISCOVER — Tabbed image showcase
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="discoverSection"
        class="w-full px-6 py-20 opacity-0 translate-y-8 blur-sm transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="$store.ui.darkMode ? 'bg-gray-950' : 'bg-white'" x-data="{
            activeTab: 'automations',
            tabs: ['ticketList', 'ticketView', 'automations', 'reports'],
            colorMap: {
                ticketList: 'from-[#ff8a9b] via-[#ff6f91] to-[#fd4f7d]',
                ticketView: 'from-[#4db3ff] via-[#3f8cff] to-[#2f66ff]',
                automations: 'from-[#8a6dff] via-[#7253f8] to-[#5b3ff0]',
                reports: 'from-[#ffd36a] via-[#ffb347] to-[#ff9742]'
            }
        }">

        <div class="mx-auto flex w-full max-w-6xl flex-col items-center">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                :class="$store.ui.darkMode ? 'border-white/15 bg-white/5 text-white/75' : 'border-gray-200 bg-gray-100 text-gray-600'">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                <span class="text-[11px] font-semibold uppercase tracking-[0.24em]">Discover HelpDesk</span>
            </div>

            {{-- Badges --}}
            <div class="mb-7 flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5"
                    :class="$store.ui.darkMode ? 'bg-white/5 text-white/70' : 'bg-gray-100 text-gray-600'">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-400">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M6 1L7.2 4.4H10.8L7.9 6.5L9.1 9.9L6 7.8L2.9 9.9L4.1 6.5L1.2 4.4H4.8L6 1Z"
                                fill="white" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium" x-text="$store.ui.t('discoverBadge1')"></span>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5"
                    :class="$store.ui.darkMode ? 'bg-white/5 text-white/70' : 'bg-gray-100 text-gray-600'">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-400">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <circle cx="6" cy="6" r="5" stroke="white" stroke-width="1.2" />
                            <path d="M3.5 6L5 7.5L8.5 4" stroke="white" stroke-width="1.2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium" x-text="$store.ui.t('discoverBadge2')"></span>
                </div>
            </div>

            {{-- Title --}}
            <h2 class="mb-4 text-center font-[Playfair_Display,ui-serif,Georgia,serif] text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl"
                :class="$store.ui.darkMode ? 'text-white' : 'text-gray-950'" x-text="$store.ui.t('discoverTitle')"></h2>

            <p class="mb-12 max-w-3xl text-center text-base leading-relaxed sm:text-lg"
                :class="$store.ui.darkMode ? 'text-white/60' : 'text-gray-600'">
                Explore powerful workflows, ticket views, and reports built to help your team move faster with
                confidence.
            </p>

            {{-- Tabs --}}
            <div class="mb-12 w-full max-w-5xl rounded-2xl border p-2"
                :class="$store.ui.darkMode ? 'border-white/15 bg-white/[0.03]' : 'border-gray-200 bg-white shadow-sm'">
                <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                    <template x-for="tab in tabs" :key="tab">
                        <button type="button"
                            class="group relative cursor-pointer rounded-xl px-4 py-3 text-sm font-medium transition-all duration-300"
                            :class="activeTab === tab
                                ? ($store.ui.darkMode
                                    ? 'bg-white text-gray-900 shadow-[0_10px_30px_-14px_rgba(255,255,255,0.95)]'
                                    : 'bg-gray-900 text-white shadow-[0_10px_24px_-14px_rgba(0,0,0,0.5)]')
                                : ($store.ui.darkMode
                                    ? 'text-white/65 hover:bg-white/10 hover:text-white'
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900')" @click="activeTab = tab">
                            <span x-text="$store.ui.t('discoverTab_' + tab)"></span>
                            <span
                                class="pointer-events-none absolute inset-x-3 bottom-1 h-px rounded-full transition-opacity duration-300"
                                :class="activeTab === tab
                                    ? ($store.ui.darkMode ? 'bg-gray-900/25 opacity-100' : 'bg-white/40 opacity-100')
                                    : 'opacity-0'">
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Image container --}}
            <div class="w-full max-w-240" id="discoverVisualPanel">
                <div class="rounded-3xl p-[1px] transition-all duration-500" :class="activeTab === 'ticketList' ? 'bg-linear-to-r from-[#ff95a4]/90 to-[#fd4f7d]/90' : ''
                        || activeTab === 'ticketView' ? 'bg-linear-to-r from-[#4db3ff]/90 to-[#2f66ff]/90' : ''
                        || activeTab === 'automations' ? 'bg-linear-to-r from-[#8a6dff]/90 to-[#5b3ff0]/90' : ''
                        || activeTab === 'reports' ? 'bg-linear-to-r from-[#ffd36a]/90 to-[#ff9742]/90' : ''">
                    <div class="relative h-105 w-full overflow-hidden rounded-[22px] border transition-all duration-500"
                        :class="[$store.ui.darkMode ? 'border-white/10' : 'border-gray-200']">
                        <div class="absolute inset-0 bg-linear-to-br transition-all duration-500"
                            :class="colorMap[activeTab]"></div>
                        <div
                            class="absolute inset-0 bg-[radial-gradient(circle_at_22%_20%,rgba(255,255,255,0.35),transparent_38%),radial-gradient(circle_at_78%_80%,rgba(255,255,255,0.18),transparent_46%)]">
                        </div>
                        <img src="" alt=""
                            class="relative z-[1] h-full w-full rounded-[22px] object-cover opacity-90 transition-transform duration-700 hover:scale-[1.02]" />
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between rounded-xl px-4 py-3"
                    :class="$store.ui.darkMode ? 'bg-white/5 text-white/70' : 'bg-gray-100 text-gray-600'">
                    <span class="text-xs uppercase tracking-[0.2em]">Preview</span>
                    <span class="text-xs font-semibold uppercase tracking-[0.16em]"
                        x-text="$store.ui.t('discoverTab_' + activeTab)"></span>
                </div>
            </div>

            {{-- CTA --}}
            <a href="{{ Route::has('register') ? route('register') : '#' }}"
                class="mt-14 inline-flex items-center gap-2 rounded-xl px-10 py-3.5 text-base font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.01]"
                :class="$store.ui.darkMode ? 'bg-white text-gray-900 hover:bg-white/90' : 'bg-red-600 hover:bg-red-500'">
                <span x-text="$store.ui.t('discoverCta')"></span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>


    {{-- ═══════════════════════════════════════════════════════════════════
    SUPPORT FEATURES SLIDER
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="sfs-section">
        <div class="sfs-header">
            <h2 :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Plus, all the little things support teams
                love</h2>
            <div class="sfs-nav-btns">
                <button class="sfs-nav-btn" id="sfsPrevBtn">⬅</button>
                <button class="sfs-nav-btn" id="sfsNextBtn">➡</button>
            </div>
        </div>

        <div class="sfs-slider-outer group"
            :class="$store.ui.darkMode ? 'before:from-black after:from-black' : 'before:from-white after:from-white'">
            <div class="sfs-slider-track" id="sfsTrack">

                <!-- 1 Workflows -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c3">
                        <div class="sfs-wf-panel">
                            <div class="sfs-wf-row" id="sfsWfR1">
                                <div class="sfs-wf-av" style="background:#6366f1;">JM</div>
                                <span>Julie Martinelli <span style="color:#bbb;font-size:10px;">Hi there, was
                                        hoping…</span></span>
                            </div>
                            <div class="sfs-wf-row" id="sfsWfR2">
                                <div class="sfs-wf-tag">⚡ Refunds and Returns <em
                                        style="font-weight:400;color:#b45309;">
                                        was run</em></div>
                            </div>
                            <div class="sfs-wf-row" id="sfsWfR3">
                                <div class="sfs-wf-av" style="background:#f59e0b;">LM</div>
                                <span>Landon Montgomery <span
                                        style="color:#1a1aff;font-size:11px;font-weight:700;">@Sabrina B<span
                                            class="sfs-wf-cursor"></span></span></span>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                            Workflows</div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            Automate the tedious but critical tasks that keep your team (and Inbox) organized.</div>
                    </div>
                </div>

                <!-- 2 Tags & Labels -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c8">
                        <div class="sfs-tag-wrap">
                            <div class="sfs-tag-search">
                                🔍 <span class="sfs-tag-search-text" id="sfsTagSearchText"></span><span
                                    id="sfsTagCursor"
                                    style="display:inline-block;width:2px;height:13px;background:#555;border-radius:1px;margin-left:1px;vertical-align:middle;"></span>
                            </div>
                            <div class="sfs-tag-grid" id="sfsTagGrid">
                                <div class="sfs-tag-chip" style="background:#dbeafe;color:#1e40af;">🔵 Billing</div>
                                <div class="sfs-tag-chip" style="background:#fce7f3;color:#9d174d;">🩷 VIP</div>
                                <div class="sfs-tag-chip" style="background:#dcfce7;color:#166534;">🟢 Resolved</div>
                                <div class="sfs-tag-chip" style="background:#fef3c7;color:#92400e;">🟡 Pending</div>
                                <div class="sfs-tag-chip" style="background:#ede9fe;color:#5b21b6;">🟣 Bug</div>
                                <div class="sfs-tag-chip" style="background:#f1f5f9;color:#334155;">⚪ General</div>
                                <div class="sfs-tag-chip" style="background:#fce7f3;color:#be185d;">🏷 Onboarding</div>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Tags
                            &amp; Labels</div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            Organize every conversation with color-coded tags so nothing falls through the cracks.</div>
                    </div>
                </div>

                <!-- 3 Multiple Inboxes -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c5">
                        <div class="sfs-mi-av-row" id="sfsMiAvRow">
                            <img src="{{ asset('images/Personnes/Personne01.jpg') }}" alt="Michael Rivera"
                                class="sfs-mi-av border-white object-cover" />
                            <img src="{{ asset('images/Personnes/Personne02.jpg') }}" alt="Sarah Johnson"
                                class="sfs-mi-av border-white object-cover" />
                            <img src="{{ asset('images/Personnes/Personne03.jpg') }}" alt="Amy Liu"
                                class="sfs-mi-av border-white object-cover" />
                        </div>
                        <div class="sfs-mi-cols" id="sfsMiCols">
                            <div class="sfs-mi-col">
                                <div class="sfs-mi-head">
                                    <div class="sfs-mi-brand">J&amp;G General</div>
                                    <div class="sfs-mi-email">support@jandgfashion.com</div>
                                </div>
                                <div class="sfs-mi-row">Chats</div>
                                <div class="sfs-mi-row bold">Unassigned <span class="sfs-mi-badge">26</span></div>
                                <div class="sfs-mi-row">Mine</div>
                                <div class="sfs-mi-row bold">Assigned <span class="sfs-mi-badge">12</span></div>
                                <div class="sfs-mi-row">Drafts <span class="sfs-mi-badge">1</span></div>
                            </div>
                            <div class="sfs-mi-col">
                                <div class="sfs-mi-head">
                                    <div class="sfs-mi-brand">Content Team</div>
                                    <div class="sfs-mi-email">content@jandg…</div>
                                </div>
                                <div class="sfs-mi-row">Unassigned</div>
                                <div class="sfs-mi-row">Mine</div>
                                <div class="sfs-mi-row">Assigned</div>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                            Multiple Inboxes</div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">Give
                            every department or product its own dedicated place for support.</div>
                    </div>
                </div>

                <!-- 4 Saved Replies -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c4">
                        <div class="sfs-sr-panel">
                            <div class="sfs-sr-msg">
                                <div class="sfs-sr-ic">🤖</div>
                                <div><strong>Account:</strong> Can I pause my account?<br>Here's how you can pause your
                                    subscription…</div>
                            </div>
                            <div class="sfs-sr-msg" style="border-bottom:none;">
                                <div class="sfs-sr-ic">🤖</div>
                                <div><strong>Account:</strong> How do I un-freeze my paused account?<br>If your account
                                    has been frozen or pause…</div>
                            </div>
                            <div class="sfs-sr-footer">Search: <span>Pause account</span> ›</div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Saved
                            replies</div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">Add
                            proven answers to common questions or situations with a few clicks.</div>
                    </div>
                </div>

                <!-- 5 Snooze -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c1">
                        <div class="sfs-snooze-panel">
                            <div class="sfs-snooze-top">
                                <div class="sfs-snooze-user">
                                    <div class="sfs-snooze-avatar"></div>
                                    <div class="sfs-snooze-user-text">
                                        <strong></strong>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="sfs-snooze-chip">Later today</div>
                            </div>
                            <div class="sfs-snooze-lines">
                                <div class="sfs-snooze-line lg"></div>
                                <div class="sfs-snooze-line md"></div>
                                <div class="sfs-snooze-line sm"></div>
                            </div>
                        </div>
                        <div class="sfs-snooze-calendar">
                            <div class="sfs-snooze-calendar-head"></div>
                            <div class="sfs-snooze-calendar-grid">
                                <span></span><span></span><span></span><span></span>
                                <span></span><span></span><span></span><span></span>
                                <span></span><span></span><span></span><span></span>
                            </div>
                        </div>
                        <div class="sfs-snooze-wrap">
                            <div class="sfs-snooze-badge">🔔 Snoozed until 8:00 am</div>
                            <div class="sfs-snooze-info">Surface at a later date<br>or snooze.</div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Snooze
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            Surface a conversation at a later date or time with snooze.</div>
                    </div>
                </div>

                <!-- 6 Channels -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c6">
                        <div class="sfs-ch-topbar">
                            <div class="sfs-ch-logo">☰</div>
                            <div class="sfs-ch-tab on">Inboxes ▾</div>
                            <div class="sfs-ch-tab">Docs</div>
                        </div>
                        <div class="sfs-ch-lines">
                            <div class="sfs-ch-line"
                                style="width:62%;background:linear-gradient(to right,#818cf8,#c7d2fe);"></div>
                            <div class="sfs-ch-line"
                                style="width:44%;background:linear-gradient(to right,#c7d2fe,#e0e7ff);"></div>
                            <div class="sfs-ch-line" style="width:55%;background:#e8e8f5;"></div>
                        </div>
                        <div class="sfs-ch-icons">
                            <div class="sfs-ch-ic">
                                <svg viewBox="0 0 48 48" width="26" height="26">
                                    <path d="M8 36V14l16 12 16-12v22H8z" fill="#fff" stroke="#eee" />
                                    <path d="M8 14l16 12 16-12H8z" fill="#EA4335" />
                                    <rect x="6" y="12" width="36" height="26" rx="3" fill="none" stroke="#FBBC05"
                                        stroke-width="1.5" />
                                </svg>
                            </div>
                            <div class="sfs-ch-ic" style="background:#0078d4;">
                                <svg viewBox="0 0 48 48" width="26" height="26">
                                    <rect width="48" height="48" rx="8" fill="#0078d4" />
                                    <rect x="5" y="13" width="22" height="22" rx="3" fill="#fff" opacity=".9" />
                                    <circle cx="16" cy="24" r="5.5" fill="#0078d4" />
                                    <rect x="29" y="17" width="14" height="3.5" rx="1.5" fill="#fff" />
                                    <rect x="29" y="22.5" width="14" height="3.5" rx="1.5" fill="#fff" />
                                    <rect x="29" y="28" width="10" height="3.5" rx="1.5" fill="#fff" />
                                </svg>
                            </div>
                            <div class="sfs-ch-ic" style="background:linear-gradient(135deg,#0099ff,#a033ff);">
                                <svg viewBox="0 0 48 48" width="26" height="26">
                                    <defs>
                                        <linearGradient id="g1" x1="0" y1="1" x2="1" y2="0">
                                            <stop offset="0%" stop-color="#0099ff" />
                                            <stop offset="100%" stop-color="#a033ff" />
                                        </linearGradient>
                                    </defs>
                                    <circle cx="24" cy="24" r="24" fill="url(#g1)" />
                                    <path
                                        d="M24 10C16 10 10 15.8 10 23c0 3.9 1.7 7.4 4.4 9.8V37l4.4-2.4c1.6.5 3.4.6 5.2.6 8 0 14-5.8 14-13S32 10 24 10z"
                                        fill="#fff" />
                                    <path d="M21 27l-4-4.5 8-4.5-4 4.5 4 4.5-8 4.5z" fill="#0099ff" />
                                </svg>
                            </div>
                            <div class="sfs-ch-ic" style="background:#96bf48;">
                                <svg viewBox="0 0 48 48" width="26" height="26">
                                    <rect width="48" height="48" rx="8" fill="#96bf48" />
                                    <path
                                        d="M30 13c-.3-.2-.8-.2-1.3 0-.4-.9-1-1.7-2-1.7h-.3C26 10.1 25 9 23.8 9c-3.1 0-4.6 3.7-5 5.6L15 15.8C14 16 14 16 13.9 17L12 33h14.5L29 22 30 13z"
                                        fill="#fff" opacity=".9" />
                                </svg>
                            </div>
                            <div class="sfs-ch-ic"
                                style="background:linear-gradient(135deg,#f09433,#dc2743 50%,#bc1888);">
                                <svg viewBox="0 0 48 48" width="26" height="26">
                                    <defs>
                                        <linearGradient id="g2" x1="0" y1="1" x2="1" y2="0">
                                            <stop offset="0%" stop-color="#f09433" />
                                            <stop offset="100%" stop-color="#bc1888" />
                                        </linearGradient>
                                    </defs>
                                    <rect width="48" height="48" rx="8" fill="url(#g2)" />
                                    <rect x="12" y="12" width="24" height="24" rx="7" fill="none" stroke="#fff"
                                        stroke-width="2.5" />
                                    <circle cx="24" cy="24" r="6.5" fill="none" stroke="#fff" stroke-width="2.5" />
                                    <circle cx="32" cy="16" r="1.8" fill="#fff" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                            Channels</div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">Close
                            those extra tabs and handle messages from social, Shopify, and more in one place.</div>
                    </div>
                </div>

                <!-- 7 Reports -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c7">
                        <div class="sfs-rp-wrap">
                            <div class="sfs-rp-head">📊 Team Performance · This Week</div>
                            <div class="sfs-rp-bars">
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b" style="height:40%"></div>
                                    <div class="sfs-rp-l">Mon</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b" style="height:65%"></div>
                                    <div class="sfs-rp-l">Tue</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b hi" style="height:100%"></div>
                                    <div class="sfs-rp-l">Wed</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b" style="height:72%"></div>
                                    <div class="sfs-rp-l">Thu</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b" style="height:55%"></div>
                                    <div class="sfs-rp-l">Fri</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b" style="height:30%"></div>
                                    <div class="sfs-rp-l">Sat</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b" style="height:20%"></div>
                                    <div class="sfs-rp-l">Sun</div>
                                </div>
                            </div>
                            <div class="sfs-rp-stats">
                                <div class="sfs-rp-stat">
                                    <div class="sfs-rp-val">94%</div>
                                    <div class="sfs-rp-key">CSAT</div>
                                </div>
                                <div class="sfs-rp-stat">
                                    <div class="sfs-rp-val">1.8h</div>
                                    <div class="sfs-rp-key">Avg Reply</div>
                                </div>
                                <div class="sfs-rp-stat">
                                    <div class="sfs-rp-val">342</div>
                                    <div class="sfs-rp-key">Resolved</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Reports
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">Track
                            team performance, response times, and customer satisfaction at a glance.</div>
                    </div>
                </div>

                <!-- 8 Send Later -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c2">
                        <div class="sfs-sl-compose">
                            <div class="sfs-sl-compose-top">
                                <div class="sfs-sl-compose-pill main"></div>
                                <div class="sfs-sl-compose-pill side"></div>
                            </div>
                            <div class="sfs-sl-compose-lines">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                        <div class="sfs-sl-floating-time text-gray-700">Best send time · 1:00 pm</div>
                        <div class="sfs-sl-box border border-gray-100">
                            <div class="sfs-sl-name text-gray-900">Josie G</div>
                            <div class="sfs-sl-time">🕐 Jul 5 at 1:00 pm</div>
                            <div class="sfs-sl-btn">Schedule</div>
                        </div>
                        <svg class="sfs-sl-cursor" viewBox="0 0 20 20" fill="none">
                            <path d="M3 3l14 7-7 1-3 7z" fill="#222" stroke="#fff" stroke-width="1" />
                        </svg>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Send
                            later</div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            Schedule a reply so the customer gets it at the perfect time.</div>
                    </div>
                </div>

            </div><!-- /track -->
        </div><!-- /slider-outer -->
    </div>



    {{-- ═══════════════════════════════════════════════════════════════════
    CUSTOMER EXPERIENCE STORIES — Tailwind slider from gg.html
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="homeStorySlider"
        class="w-full overflow-hidden px-6 pb-20 pt-6 opacity-0 blur-sm translate-y-10 transition-all duration-700"
        :class="$store.ui.darkMode ? 'bg-gray-950 text-white' : 'bg-[#f3efe7] text-[#111827]'">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10">
            <p class="text-center text-[11px] font-medium uppercase tracking-[0.18em]"
                :class="$store.ui.darkMode ? 'text-white/80' : 'text-gray-900'">
                Perfect the customer experience
            </p>

            <div class="flex items-center gap-4 md:gap-6">
                <button id="ggPrevBtn"
                    class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full border text-sm transition md:inline-flex"
                    :class="$store.ui.darkMode
                        ? 'border-white/25 bg-white/5 text-white hover:bg-white hover:text-black'
                        : 'border-gray-300 bg-gray-200 text-gray-900 hover:bg-gray-900 hover:text-white'"
                    aria-label="Previous story">
                    ←
                </button>

                <div id="ggViewport" class="min-w-0 flex-1 overflow-hidden rounded-md">
                    <div id="ggTrack" class="flex transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)]">
                        <article class="min-w-full">
                            <div
                                class="flex flex-col items-start gap-8 md:gap-10 lg:flex-row lg:items-center lg:gap-12">
                                <div class="relative h-75 w-full overflow-hidden rounded-2xl md:h-90 lg:h-105 lg:w-90 lg:shrink-0"
                                    :class="$store.ui.darkMode ? 'bg-white/10' : 'bg-[#d5cfc4]'">
                                    <img src="{{ asset('images/Personnes/Personne03.jpg') }}" alt="Liberty London"
                                        class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Playfair_Display,ui-serif,Georgia,serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "With Zendesk AI, I'm seeing an exciting opportunity to streamline and be more
                                        efficient. That will allow our team to have more time to work on projects of
                                        importance to the business, be it driving revenue or new sales channels."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">Ian Hunt</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">Director of
                                            CX at Liberty London</span>
                                    </div>
                                    <a href="#"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Read
                                        customer story →</a>
                                </div>
                            </div>
                        </article>

                        <article class="min-w-full">
                            <div
                                class="flex flex-col items-start gap-8 md:gap-10 lg:flex-row lg:items-center lg:gap-12">
                                <div class="relative h-75 w-full overflow-hidden rounded-2xl md:h-90 lg:h-105 lg:w-90 lg:shrink-0"
                                    :class="$store.ui.darkMode ? 'bg-white/10' : 'bg-[#d5cfc4]'">
                                    <img src="{{ asset('images/Personnes/Personne02.jpg') }}" alt="Khan Academy"
                                        class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Playfair_Display,ui-serif,Georgia,serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "Zendesk has helped us scale our support without scaling our team
                                        proportionally. It's been a game-changer for delivering personalized help to
                                        millions of learners worldwide."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">Kristen DiCerbo</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">Chief
                                            Learning Officer at Khan Academy</span>
                                    </div>
                                    <a href="#"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Read
                                        customer story →</a>
                                </div>
                            </div>
                        </article>

                        <article class="min-w-full">
                            <div
                                class="flex flex-col items-start gap-8 md:gap-10 lg:flex-row lg:items-center lg:gap-12">
                                <div class="relative h-75 w-full overflow-hidden rounded-2xl md:h-90 lg:h-105 lg:w-90 lg:shrink-0"
                                    :class="$store.ui.darkMode ? 'bg-white/10' : 'bg-[#d5cfc4]'">
                                    <img src="{{ asset('images/Personnes/Personne01.jpg') }}" alt="ZeroFox"
                                        class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Playfair_Display,ui-serif,Georgia,serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "With Zendesk, our support team now resolves tickets 40% faster. The automation
                                        features free us up to focus on high-priority threats that actually need human
                                        judgment and expertise."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">James Foster</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">CEO at
                                            ZeroFox</span>
                                    </div>
                                    <a href="#"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Read
                                        customer story →</a>
                                </div>
                            </div>
                        </article>

                        <article class="min-w-full">
                            <div
                                class="flex flex-col items-start gap-8 md:gap-10 lg:flex-row lg:items-center lg:gap-12">
                                <div class="relative h-75 w-full overflow-hidden rounded-2xl md:h-90 lg:h-105 lg:w-90 lg:shrink-0"
                                    :class="$store.ui.darkMode ? 'bg-white/10' : 'bg-[#d5cfc4]'">
                                    <img src="{{ asset('images/Personnes/Personne03.jpg') }}" alt="Thrasio"
                                        class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Playfair_Display,ui-serif,Georgia,serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "Zendesk allowed Thrasio to unify support across dozens of brands under one
                                        roof. Our CSAT scores jumped significantly within the first quarter of going
                                        live."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">Carlos Cashman</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">Co-CEO at
                                            Thrasio</span>
                                    </div>
                                    <a href="#"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Read
                                        customer story →</a>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <button id="ggNextBtn"
                    class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full border text-sm transition md:inline-flex"
                    :class="$store.ui.darkMode
                        ? 'border-white/25 bg-white/5 text-white hover:bg-white hover:text-black'
                        : 'border-gray-300 bg-gray-200 text-gray-900 hover:bg-gray-900 hover:text-white'"
                    aria-label="Next story">
                    →
                </button>
            </div>

            <div id="ggTabs" class="grid grid-cols-2 border-t md:grid-cols-4"
                :class="$store.ui.darkMode ? 'border-white/20' : 'border-gray-300'">
                <button type="button" data-index="0"
                    class="gg-tab group relative border-t-2 border-transparent px-4 py-5 text-center opacity-100 transition"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                    <span class="gg-progress pointer-events-none absolute left-0 -top-0.5 h-0.5 w-0"></span>
                    <span
                        class="font-[Playfair_Display,ui-serif,Georgia,serif] text-[1.2rem] tracking-[0.08em]">LIBERTY.</span>
                </button>

                <button type="button" data-index="1"
                    class="gg-tab group relative border-t-2 border-transparent px-4 py-5 text-center opacity-45 transition"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                    <span class="gg-progress pointer-events-none absolute left-0 -top-0.5 h-0.5 w-0"></span>
                    <span class="text-sm font-extrabold tracking-wide">Khan Academy</span>
                </button>

                <button type="button" data-index="2"
                    class="gg-tab group relative border-t-2 border-transparent px-4 py-5 text-center opacity-45 transition"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                    <span class="gg-progress pointer-events-none absolute left-0 -top-0.5 h-0.5 w-0"></span>
                    <span class="text-xs font-bold tracking-[0.12em]">ZEROFOX</span>
                </button>

                <button type="button" data-index="3"
                    class="gg-tab group relative border-t-2 border-transparent px-4 py-5 text-center opacity-45 transition"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                    <span class="gg-progress pointer-events-none absolute left-0 -top-0.5 h-0.5 w-0"></span>
                    <span class="text-xl tracking-[0.14em]">THRASIO</span>
                </button>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    SUPPORT HEROES — 24/7 support showcase
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="supportHeroesSection"
        class="relative w-full overflow-hidden px-6 py-24 opacity-0 translate-y-8 blur-sm transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="$store.ui.darkMode ? 'bg-gray-950' : 'bg-white'">
        <div class="pointer-events-none absolute -left-12 top-8 h-44 w-44 rounded-full blur-3xl"
            :class="$store.ui.darkMode ? 'bg-emerald-300/10' : 'bg-emerald-200/55'"></div>
        <div class="pointer-events-none absolute -right-10 bottom-8 h-56 w-56 rounded-full blur-3xl"
            :class="$store.ui.darkMode ? 'bg-cyan-300/10' : 'bg-cyan-200/45'"></div>

        <div class="relative z-1 mx-auto flex max-w-275 flex-col items-center justify-center gap-12 rounded-3xl border px-6 py-8 md:flex-row md:gap-20 md:px-10 md:py-10"
            :class="$store.ui.darkMode ? 'border-white/10 bg-white/2' : 'border-gray-200 bg-white/90 shadow-[0_20px_60px_-30px_rgba(17,24,39,0.3)]'">

            {{-- Image --}}
            <div id="supportHeroesVisual"
                class="group w-full shrink-0 overflow-hidden rounded-[22px] border p-2 transition-all duration-500 hover:-translate-y-1 md:w-107.5"
                :class="$store.ui.darkMode ? 'border-white/12 bg-gray-900/70 shadow-[0_18px_40px_-20px_rgba(0,0,0,0.75)]' : 'border-gray-200 bg-white shadow-[0_18px_42px_-26px_rgba(15,23,42,0.35)]'">
                <img src="{{ asset('images/Personnes/Team_photo.png') }}" alt="Support hero"
                    class="aspect-square w-full rounded-2xl object-cover transition-transform duration-700 group-hover:scale-103" />
            </div>

            {{-- Text --}}
            <div id="supportHeroesContent" class="max-w-115 text-center md:text-left">
                <span
                    class="mb-4 inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.2em]"
                    :class="$store.ui.darkMode ? 'border-white/15 bg-white/5 text-white/70' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    SUPPORT HEROES
                </span>
                <h2 class="mb-5 font-[Playfair_Display,ui-serif,Georgia,serif] text-4xl font-bold leading-[1.08] tracking-tight sm:text-[2.95rem]"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                    <span x-text="$store.ui.t('heroesTitle1')"></span><br>
                    <span x-text="$store.ui.t('heroesTitle2')"></span>
                </h2>
                <p class="mb-8 font-[DM_Sans,ui-sans-serif,system-ui,sans-serif] text-[1.05rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'"
                    x-text="$store.ui.t('heroesDescription')"></p>
                <button type="button" id="supportHeroesChatBtn"
                    class="inline-flex items-center gap-2 rounded-xl px-8 py-3.5 text-base font-bold text-white shadow-[0_16px_30px_-16px_rgba(34,197,94,0.75)] transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.01]"
                    :class="$store.ui.darkMode ? 'bg-linear-to-r from-emerald-400 to-green-500 hover:from-emerald-300 hover:to-green-400' : 'bg-linear-to-r from-emerald-500 to-green-600 hover:from-emerald-400 hover:to-green-500'">
                    <span x-text="$store.ui.t('heroesCta')"></span>
                    <span aria-hidden="true">→</span>
                </button>
            </div>

        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    LOGO BAR — Trusted brands
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="border-y transition-colors duration-300"
        :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.02]' : 'border-gray-200 bg-gray-50/50'">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <p class="mb-8 text-center text-xs font-semibold uppercase tracking-widest"
                :class="$store.ui.darkMode ? 'text-white/30' : 'text-gray-400'">
                Powering support for industry leaders
            </p>
            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6"
                :class="$store.ui.darkMode ? 'opacity-40' : 'opacity-30'">
                {{-- Placeholder logos using text (swap for real SVGs) --}}
                <span class="text-xl font-bold tracking-tight"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Shopify</span>
                <span class="text-xl font-bold tracking-tight"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Slack</span>
                <span class="text-xl font-bold tracking-tight"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Stripe</span>
                <span class="text-xl font-bold tracking-tight"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Notion</span>
                <span class="text-xl font-bold tracking-tight"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Vercel</span>
                <span class="text-xl font-bold tracking-tight"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Linear</span>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    FEATURES GRID — 3 column with icon cards
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="features" class="mx-auto max-w-6xl px-6 py-24">
        <div class="mb-16 text-center">
            <h2 class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl text-[#17494D]">
                Everything you need to <span class="text-brand">deliver great support</span>
            </h2>
            <p class="mx-auto max-w-2xl text-lg" :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/80'">
                Powerful features that help your team resolve issues faster and keep customers happy.
            </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Card 1 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div
                    class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Ticket Management</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Create, assign and track support tickets from start to resolution with smart routing and SLA
                    tracking.
                </p>
            </div>

            {{-- Card 2 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div
                    class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Team Collaboration</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Internal notes, mentions and shared views so your team stays aligned and responds together.
                </p>
            </div>

            {{-- Card 3 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div
                    class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Analytics &amp; Reporting</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Real-time dashboards and custom reports to measure response times, satisfaction and team
                    performance.
                </p>
            </div>

            {{-- Card 4 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div
                    class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Live Chat</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Engage customers in real time with an embedded chat widget that routes to the right agent instantly.
                </p>
            </div>

            {{-- Card 5 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div
                    class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Automation</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Auto-assign, auto-tag and trigger workflows so repetitive tasks handle themselves.
                </p>
            </div>

            {{-- Card 6 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div
                    class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Knowledge Base</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Help customers help themselves with searchable articles, FAQs and guided troubleshooting.
                </p>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    STATS BAR — Social proof numbers
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="border-y transition-colors duration-300"
        :class="$store.ui.darkMode ? 'border-white/10 bg-brand/[0.03]' : 'border-[#17494D]/10 bg-brand/[0.05]'">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-8 px-6 py-16 sm:grid-cols-4">
            <div class="text-center">
                <div class="text-4xl font-bold text-brand sm:text-5xl">2K+</div>
                <p class="mt-2 text-sm font-medium" :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">
                    Support teams</p>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-brand sm:text-5xl">10M+</div>
                <p class="mt-2 text-sm font-medium" :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">
                    Tickets resolved</p>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-brand sm:text-5xl">98%</div>
                <p class="mt-2 text-sm font-medium" :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">
                    Customer satisfaction</p>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-brand sm:text-5xl">&lt;2min</div>
                <p class="mt-2 text-sm font-medium" :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">
                    Avg. first response</p>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    SPOTLIGHT — Two-column feature highlights (alternating)
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="mx-auto max-w-6xl space-y-24 px-6 py-24">

        {{-- Highlight 1: Omnichannel --}}
        <div class="flex flex-col items-center gap-12 md:flex-row">
            <div class="flex-1">
                <span
                    class="mb-3 inline-block rounded-full bg-brand/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-brand">Omnichannel</span>
                <h3 class="mb-4 text-3xl font-bold tracking-tight">Meet your customers where they are</h3>
                <p class="mb-6 text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                    Email, chat, social, phone — every conversation in one unified inbox. No more switching between
                    tools or losing context.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        Unified agent workspace
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        Full conversation history
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        Smart channel routing
                    </li>
                </ul>
            </div>
            {{-- Illustration placeholder --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="flex h-64 w-full items-center justify-center rounded-2xl border-2 border-dashed sm:h-80"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.02]' : 'border-gray-200 bg-gray-50'">
                    <svg class="h-20 w-20" :class="$store.ui.darkMode ? 'text-white/10' : 'text-gray-200'"
                        fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" />
                        <path
                            d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Highlight 2: AI-Powered (reversed) --}}
        <div class="flex flex-col-reverse items-center gap-12 md:flex-row">
            {{-- Illustration placeholder --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="flex h-64 w-full items-center justify-center rounded-2xl border-2 border-dashed sm:h-80"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.02]' : 'border-gray-200 bg-gray-50'">
                    <svg class="h-20 w-20" :class="$store.ui.darkMode ? 'text-white/10' : 'text-gray-200'"
                        fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 .75a8.25 8.25 0 00-4.135 15.39c.686.398 1.115 1.008 1.134 1.623a.75.75 0 00.577.706 7.998 7.998 0 004.848 0 .75.75 0 00.577-.706c.02-.615.448-1.225 1.134-1.623A8.25 8.25 0 0012 .75z" />
                        <path fill-rule="evenodd"
                            d="M9.013 19.9a.75.75 0 01.877-.597 11.319 11.319 0 004.22 0 .75.75 0 11.28 1.473 12.819 12.819 0 01-4.78 0 .75.75 0 01-.597-.876zM9.754 22.344a.75.75 0 01.824-.668 13.682 13.682 0 002.844 0 .75.75 0 11.156 1.492 15.156 15.156 0 01-3.156 0 .75.75 0 01-.668-.824z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <span
                    class="mb-3 inline-block rounded-full bg-brand/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-brand">AI-Powered</span>
                <h3 class="mb-4 text-3xl font-bold tracking-tight">Resolve issues before they escalate</h3>
                <p class="mb-6 text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                    Built-in AI suggests responses, summarizes tickets and surfaces relevant knowledge base articles —
                    so agents can focus on what matters.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        AI-suggested replies
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        Auto-ticket summaries
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                        Intelligent chatbot
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    TESTIMONIALS — 3 cards
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="border-y transition-colors duration-300"
        :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.02]' : 'border-gray-200 bg-gray-50/50'">
        <div class="mx-auto max-w-6xl px-6 py-24">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl">Loved by support teams</h2>
                <p class="mx-auto max-w-xl text-lg" :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    See why thousands of teams choose us to power their customer support.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                {{-- Testimonial 1 --}}
                <div class="rounded-2xl border p-8 transition-colors duration-300"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03]' : 'border-gray-200 bg-white'">
                    <div class="mb-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-brand" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="mb-6 text-[0.9375rem] leading-relaxed"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        &ldquo;Reduced our average response time by 60%. The automation features are a game changer for
                        our team.&rdquo;
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/20 text-sm font-bold text-brand">
                            SJ</div>
                        <div>
                            <div class="text-sm font-semibold">Sarah Johnson</div>
                            <div class="text-xs" :class="$store.ui.darkMode ? 'text-white/40' : 'text-gray-400'">Head of
                                Support, TechCorp</div>
                        </div>
                    </div>
                </div>

                {{-- Testimonial 2 --}}
                <div class="rounded-2xl border p-8 transition-colors duration-300"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03]' : 'border-gray-200 bg-white'">
                    <div class="mb-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-brand" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="mb-6 text-[0.9375rem] leading-relaxed"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        &ldquo;The unified inbox is exactly what we needed. Our agents love it and our CSAT scores have
                        never been higher.&rdquo;
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/20 text-sm font-bold text-brand">
                            MR</div>
                        <div>
                            <div class="text-sm font-semibold">Michael Rivera</div>
                            <div class="text-xs" :class="$store.ui.darkMode ? 'text-white/40' : 'text-gray-400'">CTO,
                                StartupFlow</div>
                        </div>
                    </div>
                </div>

                {{-- Testimonial 3 --}}
                <div class="rounded-2xl border p-8 transition-colors duration-300"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03]' : 'border-gray-200 bg-white'">
                    <div class="mb-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-brand" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="mb-6 text-[0.9375rem] leading-relaxed"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        &ldquo;We migrated from Zendesk and haven&rsquo;t looked back. Faster, cleaner and the AI
                        features actually work.&rdquo;
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/20 text-sm font-bold text-brand">
                            AL</div>
                        <div>
                            <div class="text-sm font-semibold">Amy Liu</div>
                            <div class="text-xs" :class="$store.ui.darkMode ? 'text-white/40' : 'text-gray-400'">VP
                                Operations, CloudBase</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    FINAL CTA
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="mx-auto max-w-4xl px-6 py-28 text-center">
        <h2 class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
            Ready to transform your support?
        </h2>
        <p class="mx-auto mb-10 max-w-xl text-lg" :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
            Join thousands of teams delivering faster, smarter customer support. Start your free trial today — no credit
            card required.
        </p>
        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-10 py-4 text-base font-semibold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/30">
                        Go to Dashboard
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-10 py-4 text-base font-semibold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/30">
                        Start free trial
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-10 py-4 text-base font-semibold transition hover:-translate-y-0.5"
                        :class="$store.ui.darkMode ? 'border-white/20 text-white hover:border-white/40 hover:bg-white/5' : 'border-[#17494D]/30 text-[#17494D] hover:border-[#17494D]/50 hover:bg-[#17494D]/5'">
                        Talk to sales
                    </a>
                @endauth
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    GET STARTED — Animated hero block from gg.html
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="getStartedHeroSection"
        class="relative isolate overflow-hidden bg-[#fdf0e8] px-6 pb-32 pt-10 opacity-0 translate-y-8 blur-sm transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]">
        <div
            class="absolute inset-0 z-0 bg-[radial-gradient(ellipse_70%_60%_at_20%_40%,#d8a8cc_0%,transparent_55%),radial-gradient(ellipse_60%_70%_at_80%_30%,#f0b090_0%,transparent_55%),radial-gradient(ellipse_80%_80%_at_50%_80%,#f5c898_0%,transparent_60%),linear-gradient(135deg,#dbaac8_0%,#f0b888_50%,#f5a87a_100%)]">
        </div>

        <div
            class="absolute right-0 top-0 z-1 h-65 w-65 bg-[radial-gradient(circle,rgba(180,100,80,0.25)_1.5px,transparent_1.5px)] bg-size-[18px_18px] mask-[radial-gradient(ellipse_80%_80%_at_90%_10%,black_40%,transparent_80%)]">
        </div>

        <svg class="absolute bottom-0 left-0 z-1 h-80 w-80 opacity-[0.18]" viewBox="0 0 320 320" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0 320 Q0 0 320 0" stroke="#6b3050" stroke-width="1.5" fill="none" />
            <path d="M0 320 Q0 40 280 40" stroke="#6b3050" stroke-width="1.5" fill="none" />
            <path d="M0 320 Q0 80 240 80" stroke="#6b3050" stroke-width="1.5" fill="none" />
        </svg>

        <div
            class="relative z-2 mx-auto flex min-h-[10vh] w-full max-w-5000 flex-col items-center justify-center pb-24 text-center">
            <div class="mb-7 overflow-hidden leading-none">
                <span id="getStartedHeadline"
                    class="block translate-y-15 font-[Barlow_Condensed,sans-serif] text-[clamp(72px,12vw,148px)] font-black uppercase leading-[0.92] tracking-[-1px] text-[#1a1020] opacity-0 transition-[transform,opacity] duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
                    data-get-started-reveal>
                    GET STARTED
                </span>
            </div>

            <p class="mb-10 translate-y-15 text-[clamp(16px,2vw,22px)] leading-[1.65] text-[#3a1e2e] opacity-0 transition-[transform,opacity] duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] delay-250"
                data-get-started-reveal>
                Learn the platform in less than an hour.<br>
                Become a <em class="font-[Lora,serif] italic font-normal">power user</em> in less than a day.
            </p>

            <div class="flex translate-y-15 flex-wrap justify-center gap-3.5 opacity-0 transition-[transform,opacity] duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] delay-400"
                data-get-started-reveal>
                <a href="#"
                    class="rounded-lg bg-[#2d9e5a] px-8.5 py-3.75 text-base font-semibold text-white shadow-[0_6px_20px_-4px_rgba(45,157,90,0.55)] transition hover:-translate-y-0.5 hover:shadow-[0_10px_28px_-4px_rgba(255,255,255,0.65)] active:translate-y-0">
                    Get Started
                </a>
                <a href="#"
                    class="rounded-lg border-[1.5px] border-white/90 bg-white/80 px-8.5 py-3.75 text-base font-semibold text-[#1a1020] shadow-[0_4px_14px_-4px_rgba(0,0,0,0.15)] backdrop-blur-[6px] transition hover:-translate-y-0.5 hover:bg-white active:translate-y-0">
                    Book a Demo
                </a>
            </div>

            <div class="absolute -bottom-33 left-1/2 z-3 w-[min(560px,90vw)] -translate-x-1/2 translate-y-36 opacity-0 transition-[transform,opacity] duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] delay-600 md:left-[57%] lg:left-[60%]"
                data-get-started-reveal>
                <div
                    class="flex items-center gap-3 rounded-t-2xl bg-white px-6 py-4.5 shadow-[0_-8px_40px_rgba(0,0,0,0.12)]">
                    <span class="flex-1 text-[15px] font-medium text-[#333]">
                        App help?
                        <span
                            class="ml-1 inline-block rounded bg-[#e8f5e9] px-2 py-0.75 text-[11px] font-semibold text-[#2e7d32]">vip</span>
                        <span
                            class="ml-1 inline-block rounded bg-[#fce4ec] px-2 py-0.75 text-[11px] font-semibold text-[#c62828]">sales-lead</span>
                    </span>
                    <div class="flex items-center">
                        <div
                            class="-ml-2.5 flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-[2.5px] border-[#f3e8ff] bg-linear-to-br from-[#a78bfa] to-[#7c3aed] text-lg first:ml-0">
                            <img src="https://i.pravatar.cc/88?img=12" alt="Support agent"
                                class="h-full w-full rounded-full object-cover" />
                        </div>
                        <div
                            class="-ml-2.5 flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-[2.5px] border-[#fff0ec] bg-linear-to-br from-[#fb923c] to-[#dc2626] text-lg first:ml-0">
                            <img src="https://i.pravatar.cc/88?img=32" alt="Support agent"
                                class="h-full w-full rounded-full object-cover" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <x-footer />

    <!-- Notification Banner -->
    <x-notification-banner />

    <!-- Chatbot -->
    <livewire:ai-chat-widget />
    <!-- Support Features Slider Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* ═══════════════════════════
               SLIDER CORE
            ═══════════════════════════ */
            const sfsTrack = document.getElementById('sfsTrack');
            const sfsPrevBtn = document.getElementById('sfsPrevBtn');
            const sfsNextBtn = document.getElementById('sfsNextBtn');

            if (sfsTrack && sfsPrevBtn && sfsNextBtn) {
                const sfsCards = sfsTrack.querySelectorAll('.sfs-card');
                const sfsTotal = sfsCards.length;
                const sfsStepCards = 3;
                let sfsCurrent = 0, sfsAutoTimer;

                function sfsGap() {
                    return parseFloat(getComputedStyle(sfsTrack).gap) || 16;
                }
                function sfsMaxIndex() { return Math.max(0, sfsTotal - 2); }
                function sfsStepPx() { return sfsCards[0].offsetWidth + sfsGap(); }

                function sfsGoTo(idx) {
                    sfsCurrent = Math.max(0, Math.min(idx, sfsMaxIndex()));
                    sfsTrack.style.transform = `translateX(-${sfsCurrent * sfsStepPx()}px)`;
                    sfsUpdateNavState();
                }

                function sfsUpdateNavState() {
                    const atStart = sfsCurrent === 0;
                    const atEnd = sfsCurrent === sfsMaxIndex();

                    sfsPrevBtn.classList.toggle('is-disabled', atStart);
                    sfsNextBtn.classList.toggle('is-disabled', atEnd);
                    sfsPrevBtn.setAttribute('aria-disabled', atStart ? 'true' : 'false');
                    sfsNextBtn.setAttribute('aria-disabled', atEnd ? 'true' : 'false');
                }

                function sfsStartAuto() {
                    sfsAutoTimer = setInterval(() => {
                        sfsGoTo(sfsCurrent >= sfsMaxIndex() ? 0 : sfsCurrent + sfsStepCards);
                    }, 7000);
                }
                function sfsResetAuto() { clearInterval(sfsAutoTimer); sfsStartAuto(); }

                sfsPrevBtn.addEventListener('click', () => {
                    if (sfsCurrent === 0) return;
                    sfsGoTo(sfsCurrent - sfsStepCards);
                    sfsResetAuto();
                });
                sfsNextBtn.addEventListener('click', () => {
                    if (sfsCurrent === sfsMaxIndex()) return;
                    sfsGoTo(sfsCurrent + sfsStepCards);
                    sfsResetAuto();
                });
                window.addEventListener('resize', () => { sfsGoTo(Math.min(sfsCurrent, sfsMaxIndex())); });

                sfsGoTo(0);
                sfsStartAuto();

                /* ═══════════════════════════
                   C3 – Workflows rows animation
                ═══════════════════════════ */
                const sfsWfRows = ['sfsWfR1', 'sfsWfR2', 'sfsWfR3'].map(id => document.getElementById(id));

                function sfsAnimateWF() {
                    sfsWfRows.forEach(r => { if (r) r.classList.remove('vis'); });
                    sfsWfRows.forEach((r, i) => { if (r) setTimeout(() => r.classList.add('vis'), 350 + i * 520); });
                    setTimeout(sfsAnimateWF, 5200);
                }
                setTimeout(sfsAnimateWF, 600);

                /* ═══════════════════════════
                   C8 – Tags typing animation
                ═══════════════════════════ */
                const sfsTagSearchText = document.getElementById('sfsTagSearchText');
                const sfsTagCursorEl = document.getElementById('sfsTagCursor');
                const sfsTagGrid = document.getElementById('sfsTagGrid');

                if (sfsTagSearchText && sfsTagCursorEl && sfsTagGrid) {
                    const typeWord = 'Refund';
                    const newChip = { label: '🟠 Refund', bg: '#ffedd5', color: '#9a3412' };

                    // blink cursor
                    let sfsCursorVisible = true;
                    setInterval(() => {
                        sfsCursorVisible = !sfsCursorVisible;
                        sfsTagCursorEl.style.opacity = sfsCursorVisible ? '1' : '0';
                    }, 530);

                    function sfsWait(ms) { return new Promise(r => setTimeout(r, ms)); }

                    async function runTagAnim() {
                        // type "Refund" char by char
                        sfsTagSearchText.textContent = '';
                        for (const ch of typeWord) {
                            sfsTagSearchText.textContent += ch;
                            await sfsWait(110);
                        }

                        await sfsWait(600);

                        const chip = document.createElement('div');
                        chip.className = 'sfs-tag-chip popping';
                        chip.style.background = newChip.bg;
                        chip.style.color = newChip.color;
                        chip.style.opacity = '0';
                        chip.textContent = newChip.label;
                        sfsTagGrid.appendChild(chip);

                        await sfsWait(200);
                        sfsTagSearchText.textContent = '';

                        await sfsWait(2800);

                        chip.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        chip.style.opacity = '0';
                        chip.style.transform = 'scale(0.7)';
                        await sfsWait(450);
                        chip.remove();
                        await sfsWait(700);

                        runTagAnim();
                    }

                    setTimeout(runTagAnim, 1200);
                }

                /* ═══════════════════════════
                   Scroll fade-in for section
                ═══════════════════════════ */
                const sectionEl = document.querySelector('.sfs-section');
                if (sectionEl) {
                    const showSection = () => sectionEl.classList.add('is-visible');

                    if (!('IntersectionObserver' in window)) {
                        showSection();
                    } else {
                        const revealObserver = new IntersectionObserver((entries, observer) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    showSection();
                                    observer.unobserve(entry.target);
                                }
                            });
                        }, {
                            threshold: 0.1,
                            rootMargin: '0px 0px -5% 0px'
                        });

                        revealObserver.observe(sectionEl);
                    }
                }
            }

            /* ═══════════════════════════
               Customer story slider (gg section)
            ═══════════════════════════ */
            const ggSection = document.getElementById('homeStorySlider');
            const ggTrack = document.getElementById('ggTrack');
            const ggViewport = document.getElementById('ggViewport');
            const ggTabs = Array.from(document.querySelectorAll('#ggTabs .gg-tab'));
            const ggPrevBtn = document.getElementById('ggPrevBtn');
            const ggNextBtn = document.getElementById('ggNextBtn');

            if (ggSection && ggTrack && ggViewport && ggTabs.length && ggPrevBtn && ggNextBtn) {
                const GG_AUTO_DURATION = 6000;
                const ggTotal = ggTabs.length;
                let ggCurrent = 0;
                let ggAutoTimer = null;
                let ggTouchStartX = 0;

                const ggImages = Array.from(ggSection.querySelectorAll('.gg-slide-image'));
                ggImages.forEach((image) => {
                    const placeholder = image.parentElement?.querySelector('.gg-slide-placeholder');
                    const hasImagePath = image.getAttribute('src')?.trim();

                    if (hasImagePath) {
                        image.classList.remove('hidden');
                        placeholder?.classList.add('hidden');
                    } else {
                        image.classList.add('hidden');
                        placeholder?.classList.remove('hidden');
                    }
                });

                function ggRestartTabProgress(tab, shouldAnimate) {
                    const progress = tab.querySelector('.gg-progress');
                    if (!progress) {
                        return;
                    }

                    progress.style.transition = 'none';
                    progress.style.width = '0%';
                    progress.classList.add('bg-current', 'opacity-90');

                    if (!shouldAnimate) {
                        return;
                    }

                    requestAnimationFrame(() => {
                        progress.style.transition = `width ${GG_AUTO_DURATION}ms linear`;
                        progress.style.width = '100%';
                    });
                }

                function ggStartAuto() {
                    clearInterval(ggAutoTimer);
                    ggAutoTimer = setInterval(() => {
                        ggGoTo(ggCurrent + 1, false);
                    }, GG_AUTO_DURATION);
                }

                function ggGoTo(index, resetAuto = true) {
                    ggCurrent = ((index % ggTotal) + ggTotal) % ggTotal;
                    ggTrack.style.transform = `translateX(-${ggCurrent * 100}%)`;

                    ggTabs.forEach((tab, tabIndex) => {
                        const isActive = tabIndex === ggCurrent;
                        tab.classList.toggle('opacity-100', isActive);
                        tab.classList.toggle('opacity-45', !isActive);
                        tab.classList.toggle('border-current', isActive);
                        tab.classList.toggle('border-transparent', !isActive);
                        tab.setAttribute('aria-current', isActive ? 'true' : 'false');
                        ggRestartTabProgress(tab, isActive);
                    });

                    if (resetAuto) {
                        ggStartAuto();
                    }
                }

                ggPrevBtn.addEventListener('click', () => ggGoTo(ggCurrent - 1));
                ggNextBtn.addEventListener('click', () => ggGoTo(ggCurrent + 1));

                ggTabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        const index = Number(tab.dataset.index ?? 0);
                        ggGoTo(index);
                    });
                });

                ggViewport.addEventListener('touchstart', (event) => {
                    ggTouchStartX = event.touches[0].clientX;
                }, { passive: true });

                ggViewport.addEventListener('touchend', (event) => {
                    const deltaX = event.changedTouches[0].clientX - ggTouchStartX;
                    if (Math.abs(deltaX) > 40) {
                        ggGoTo(deltaX < 0 ? ggCurrent + 1 : ggCurrent - 1);
                    }
                }, { passive: true });

                ggGoTo(0);

                const ggShowSection = () => {
                    ggSection.classList.remove('opacity-0', 'blur-sm', 'translate-y-10');
                    ggSection.classList.add('opacity-100', 'blur-0', 'translate-y-0');
                };

                if (!('IntersectionObserver' in window)) {
                    ggShowSection();
                } else {
                    const ggObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                ggShowSection();
                                observer.unobserve(entry.target);
                            }
                        });
                    }, {
                        threshold: 0.22,
                        rootMargin: '0px 0px -6% 0px',
                    });

                    ggObserver.observe(ggSection);
                }
            }

            /* ═══════════════════════════
               Support heroes motion + chatbot shortcut
            ═══════════════════════════ */
            const supportHeroesSection = document.getElementById('supportHeroesSection');
            const supportHeroesVisual = document.getElementById('supportHeroesVisual');
            const supportHeroesContent = document.getElementById('supportHeroesContent');
            const supportHeroesChatBtn = document.getElementById('supportHeroesChatBtn');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let supportHeroesRevealed = false;

            if (supportHeroesSection) {
                const showSupportHeroes = () => {
                    supportHeroesRevealed = true;
                    supportHeroesSection.classList.remove('opacity-0', 'translate-y-8', 'blur-sm');
                    supportHeroesSection.classList.add('opacity-100', 'translate-y-0', 'blur-0');
                };

                if (!('IntersectionObserver' in window)) {
                    showSupportHeroes();
                } else {
                    const supportHeroesObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting && !supportHeroesRevealed) {
                                showSupportHeroes();
                                observer.unobserve(entry.target);
                            }
                        });
                    }, {
                        threshold: 0.2,
                        rootMargin: '0px 0px -8% 0px',
                    });

                    supportHeroesObserver.observe(supportHeroesSection);
                }

                if (!prefersReducedMotion) {
                    const onSupportHeroesScroll = () => {
                        const rect = supportHeroesSection.getBoundingClientRect();
                        const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / (window.innerHeight + rect.height)));
                        const visualShift = (0.5 - progress) * 20;
                        const contentShift = (0.5 - progress) * 12;

                        if (supportHeroesVisual) {
                            supportHeroesVisual.style.transform = `translateY(${visualShift}px)`;
                        }

                        if (supportHeroesContent) {
                            supportHeroesContent.style.transform = `translateY(${contentShift}px)`;
                        }
                    };

                    window.addEventListener('scroll', onSupportHeroesScroll, { passive: true });
                    onSupportHeroesScroll();
                }
            }

            if (supportHeroesChatBtn) {
                supportHeroesChatBtn.addEventListener('click', () => {
                    const chatbotWidget = document.getElementById('chatbot-widget');
                    chatbotWidget?.scrollIntoView({ behavior: 'smooth', block: 'end' });
                    window.dispatchEvent(new CustomEvent('open-chatbot-widget'));
                });
            }

            /* ═══════════════════════════
               Discover section polish animation
            ═══════════════════════════ */
            const discoverSection = document.getElementById('discoverSection');
            const discoverVisualPanel = document.getElementById('discoverVisualPanel');
            let discoverRevealed = false;

            if (discoverSection) {
                const showDiscover = () => {
                    discoverRevealed = true;
                    discoverSection.classList.remove('opacity-0', 'translate-y-8', 'blur-sm');
                    discoverSection.classList.add('opacity-100', 'translate-y-0', 'blur-0');
                };

                if (!('IntersectionObserver' in window)) {
                    showDiscover();
                } else {
                    const discoverObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting && !discoverRevealed) {
                                showDiscover();
                                observer.unobserve(entry.target);
                            }
                        });
                    }, {
                        threshold: 0.16,
                        rootMargin: '0px 0px -8% 0px',
                    });

                    discoverObserver.observe(discoverSection);
                }

                if (discoverVisualPanel) {
                    const onDiscoverScroll = () => {
                        const rect = discoverSection.getBoundingClientRect();
                        const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / (window.innerHeight + rect.height)));
                        const translateY = (0.5 - progress) * 14;
                        discoverVisualPanel.style.transform = `translateY(${translateY}px)`;
                    };

                    window.addEventListener('scroll', onDiscoverScroll, { passive: true });
                    onDiscoverScroll();
                }
            }

            /* ═══════════════════════════
               Get Started hero block (gg section)
            ═══════════════════════════ */
            const getStartedSection = document.getElementById('getStartedHeroSection');
            const getStartedHeadline = document.getElementById('getStartedHeadline');
            const getStartedRevealItems = Array.from(document.querySelectorAll('[data-get-started-reveal]'));
            let getStartedRevealed = false;

            if (getStartedSection && getStartedHeadline && getStartedRevealItems.length) {
                const revealGetStarted = () => {
                    getStartedRevealed = true;
                    getStartedSection.classList.remove('opacity-0', 'translate-y-8', 'blur-sm');
                    getStartedSection.classList.add('opacity-100', 'translate-y-0', 'blur-0');
                    getStartedRevealItems.forEach((item) => {
                        item.classList.remove('opacity-0', 'translate-y-15', 'translate-y-24', 'translate-y-36');
                    });
                };

                if (!('IntersectionObserver' in window)) {
                    revealGetStarted();
                } else {
                    const getStartedObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting && !getStartedRevealed) {
                                revealGetStarted();
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.2 });

                    getStartedObserver.observe(getStartedSection);
                }

                const onGetStartedScroll = () => {
                    if (!getStartedRevealed) {
                        return;
                    }

                    const rect = getStartedSection.getBoundingClientRect();
                    const sectionHeight = getStartedSection.offsetHeight;
                    const scrolledPast = Math.max(0, -rect.top);
                    const progress = Math.min(scrolledPast / sectionHeight, 1);

                    const scale = 1 + (progress * 0.28);
                    const translateY = -(progress * 18);
                    const opacity = 1 - (progress * 0.35);

                    getStartedHeadline.style.transform = `translateY(${translateY}px) scale(${scale})`;
                    getStartedHeadline.style.opacity = `${opacity}`;
                };

                window.addEventListener('scroll', onGetStartedScroll, { passive: true });
            }
        });
    </script>
</body>

</html>