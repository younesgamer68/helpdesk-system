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

    @vite(['resources/css/welcome.css'])

    <!-- Alpine.js — ui-state store must load before Alpine starts -->
    <x-ui-state />
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.8/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data
    class="welcome-body flex min-h-screen flex-col font-[Instrument_Sans,ui-sans-serif,system-ui,sans-serif] antialiased transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-black text-white' : 'bg-[#ffffff] text-[#17494D]'">

    <!-- Navigation -->
    <x-nav-bar />
    <x-loading-overlay />

    {{-- ═══════════════════════════════════════════════════════════════════
        HERO — Minimal centered w/ headline, email form, privacy note
    ═══════════════════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden" x-data="{ heroEmail: '', heroMsg: '', heroMsgOk: false }">
    {{-- Background wave — dark mode only --}}
    <div class="pointer-events-none absolute inset-0 transition-opacity duration-300"
        :class="$store.ui.darkMode ? 'opacity-100' : 'opacity-0'">
        <img src="{{ asset('images/Backgrounds/Wave SVG_DM.png') }}" alt=""
            class="h-full w-full object-cover object-center" style="max-width: 1280px; max-height: 720px; margin: auto;" />
    </div>

 

    <div class="relative mx-auto max-w-5xl px-6 pb-16 pt-16 text-center sm:pb-20 sm:pt-24 lg:pb-10 lg:pt-17">
        {{-- Headlines container with controlled width --}}
        <div class="mx-auto max-w-4xl">
            <h1 class="text-5xl font-extralight leading-[1.1] tracking-tight sm:text-6xl lg:text-[4.5rem]"
                :class="$store.ui.darkMode ? 'text-white' : 'text-gray-950'"
                x-text="$store.ui.t('heroHeadline1')">
            </h1>
            <h1 class="mt-1 text-5xl font-extralight leading-[1.1] tracking-tight sm:text-6xl lg:text-[4.5rem]"
                :class="$store.ui.darkMode ? 'text-white' : 'text-gray-950'"
                x-text="$store.ui.t('heroHeadline2')">
            </h1>
        </div>

        {{-- Subtitle with better width control --}}
        <p class="mx-auto mt-6 max-w-3xl text-lg sm:mt-7 sm:text-xl lg:text-2xl"
            :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'"
            x-text="$store.ui.t('heroSubtitle')">
        </p>

        {{-- Flash message --}}
        <p x-show="heroMsg" x-text="heroMsg" x-transition
            class="mt-4 text-sm font-medium"
            :class="heroMsgOk ? 'text-green-500' : 'text-red-500'"></p>

        {{-- CTA area --}}
        @if (Route::has('login'))
            @auth
                {{-- Logged-in → Dashboard --}}
                <div class="mt-10 flex justify-center sm:mt-12">
                    <a href="{{ route('tickets', Auth::user()->company->slug) }}"
                        class="inline-flex items-center gap-2 rounded-full bg-green-600 px-8 py-4 text-[1.05rem] font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-green-500 hover:shadow-xl">
                        <span x-text="$store.ui.t('heroDashboard')"></span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            @else
                {{-- Guest → Email sign-up form --}}
                <form class="mx-auto mt-10 flex w-full max-w-[550px] flex-col items-center gap-3.5 sm:mt-12 sm:flex-row sm:max-w-[600px]"
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
        <p class="mt-6 text-sm sm:mt-5"
            :class="$store.ui.darkMode ? 'text-white/35' : 'text-gray-500'">
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
        <div class="scene mx-auto flex max-w-[1100px] items-center justify-center px-8">

            {{-- Left Far Image --}}
            <div class="side-card side-card--left-far relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #c7d2fe 0%, #a5b4fc 100%);">
                    <img src="{{ asset('images/Personnes/close-up-volunteer-oganizing-stuff-donation.jpg') }}" alt="Team member 1" class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

            {{-- Left Near Image --}}
            <div class="side-card side-card--left-near relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #fda4af 0%, #fb7185 100%);">
                    <img src="{{ asset('images/Personnes/image.png') }}" alt="Team member 2" class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

            {{-- CENTER CARD --}}
            <div class="center-card relative z-10 flex-shrink-0 overflow-hidden rounded-[20px] shadow-2xl"
                :class="$store.ui.darkMode ? 'bg-gray-800' : 'bg-white'"
                x-data="{ visible: false }"
                x-init="setTimeout(() => visible = true, 80)"
                :style="visible ? 'opacity:1; transform:translateY(0) scale(1)' : 'opacity:0; transform:translateY(24px) scale(0.97)'"
                style="transition: opacity 0.5s ease, transform 0.7s ease;">
                <div class="center-card__inner flex h-full">
                    <div class="flex h-full w-full items-center justify-center overflow-hidden rounded-2xl"
                        style="background: linear-gradient(135deg, #ff0000, #000000); min-height: 300px;">
                        <img src="{{ asset('images/Personnes/image-1773113418059.png') }}" alt="Center image" class="max-h-full max-w-full object-cover" />
                    </div>
                </div>
            </div>


            
            {{-- Right Near Image --}}
            <div class="side-card side-card--right-near relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #fcd34d 0%, #f59e0b 100%);">
                    <img src="{{ asset('images/Personnes/man-woman-working-together-startup-company.jpg') }}" alt="Team member 3" class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

            {{-- Right Far Image --}}
            <div class="side-card side-card--right-far relative flex-shrink-0 overflow-hidden rounded-2xl shadow-lg">
                <div class="img-placeholder h-full w-full overflow-hidden rounded-2xl"
                    style="background: linear-gradient(160deg, #6ee7b7 0%, #34d399 100%);">
                    <img src="{{ asset('images/Personnes/smiley-man-working-laptop-while-standing.jpg') }}" alt="Team member 4" class="absolute inset-0 h-full w-full object-cover" />
                </div>
            </div>

        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
        DISCOVER — Tabbed image showcase
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="w-full py-20 px-6 flex flex-col items-center transition-colors duration-300"
        :class="$store.ui.darkMode ? 'bg-gray-950' : 'bg-white'"
        x-data="{
            activeTab: 'automations',
            tabs: ['ticketList', 'ticketView', 'automations', 'reports'],
            colorMap: {
                ticketList: 'bg-red-500',
                ticketView: 'bg-blue-500',
                automations: 'bg-violet-500',
                reports: 'bg-yellow-400'
            }
        }">

        {{-- Badges --}}
        <div class="mb-6 flex items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-400">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1L7.2 4.4H10.8L7.9 6.5L9.1 9.9L6 7.8L2.9 9.9L4.1 6.5L1.2 4.4H4.8L6 1Z" fill="white"/></svg>
                </div>
                <span class="text-xs font-medium"
                    :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                    x-text="$store.ui.t('discoverBadge1')"></span>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-400">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="5" stroke="white" stroke-width="1.2"/><path d="M3.5 6L5 7.5L8.5 4" stroke="white" stroke-width="1.2" stroke-linecap="round"/></svg>
                </div>
                <span class="text-xs font-medium"
                    :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-500'"
                    x-text="$store.ui.t('discoverBadge2')"></span>
            </div>
        </div>

        {{-- Title --}}
        <h2 class="mb-10 text-center text-4xl font-extrabold tracking-tight sm:text-5xl"
            :class="$store.ui.darkMode ? 'text-white' : 'text-gray-950'"
            x-text="$store.ui.t('discoverTitle')"></h2>

        {{-- Tabs --}}
        <div class="mb-12 flex border-b transition-colors duration-300"
            :class="$store.ui.darkMode ? 'border-white/10' : 'border-gray-200'">
            <template x-for="tab in tabs" :key="tab">
                <button
                    class="relative cursor-pointer border-none bg-transparent px-8 py-3 text-sm font-normal transition-colors duration-200"
                    :class="{
                        'text-blue-600 font-semibold': activeTab === tab,
                        [$store.ui.darkMode ? 'text-gray-500 hover:text-gray-300' : 'text-gray-500 hover:text-gray-700']: activeTab !== tab
                    }"
                    @click="activeTab = tab"
                    x-text="$store.ui.t('discoverTab_' + tab)">
                </button>
            </template>
        </div>

        {{-- Active tab indicator (separate for cleaner styling) --}}
        <style>
            [x-cloak] { display: none !important; }
        </style>

        {{-- Image container --}}
        <div class="w-full max-w-[960px]">
            <div class="h-[420px] w-full overflow-hidden rounded-[18px] border transition-colors duration-300"
                :class="[
                    colorMap[activeTab],
                    $store.ui.darkMode ? 'border-white/10' : 'border-gray-200'
                ]">
                <img src="" alt="" class="h-full w-full rounded-[18px] object-cover" />
            </div>
        </div>

        {{-- CTA --}}
        <a href="{{ Route::has('register') ? route('register') : '#' }}"
            class="mt-14 inline-block rounded-lg bg-red-600 px-10 py-3 text-base font-bold text-white transition hover:opacity-90"
            x-text="$store.ui.t('discoverCta')"></a>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
        SUPPORT HEROES — 24/7 support showcase
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="w-full py-24 px-6 transition-colors duration-300"
        :class="$store.ui.darkMode ? 'bg-gray-950' : 'bg-white'">
        <div class="mx-auto flex max-w-[1050px] flex-col items-center justify-center gap-12 md:flex-row md:gap-20">

            {{-- Image --}}
            <div class="w-full flex-shrink-0 overflow-hidden rounded-[18px] md:w-[420px]"
                :class="$store.ui.darkMode ? 'bg-gray-800' : 'bg-white'">
                <img src="" alt="Support hero"
                    class="aspect-square w-full object-cover" />
            </div>

            {{-- Text --}}
            <div class="max-w-[420px] text-center md:text-left">
                <h2 class="mb-5 text-4xl font-extrabold leading-[1.15] tracking-tight sm:text-[2.8rem]"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                    <span x-text="$store.ui.t('heroesTitle1')"></span><br>
                    <span x-text="$store.ui.t('heroesTitle2')"></span>
                </h2>
                <p class="mb-7 text-[1.05rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'"
                    x-text="$store.ui.t('heroesDescription')"></p>
                <a href="#"
                    class="inline-block rounded-lg bg-[#5edb56] px-7 py-3.5 text-base font-bold text-white transition hover:opacity-90"
                    x-text="$store.ui.t('heroesCta')"></a>
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
            <p class="mx-auto max-w-2xl text-lg"
                :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/80'">
                Powerful features that help your team resolve issues faster and keep customers happy.
            </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Card 1 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Ticket Management</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Create, assign and track support tickets from start to resolution with smart routing and SLA tracking.
                </p>
            </div>

            {{-- Card 2 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
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
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold">Analytics &amp; Reporting</h3>
                <p class="text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    Real-time dashboards and custom reports to measure response times, satisfaction and team performance.
                </p>
            </div>

            {{-- Card 4 --}}
            <div class="group rounded-2xl border p-8 transition-all duration-300"
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' : 'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
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
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
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
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand/15 transition-colors group-hover:bg-brand/25">
                    <svg class="h-6 w-6 stroke-brand" fill="none" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
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
                <p class="mt-2 text-sm font-medium"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">Support teams</p>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-brand sm:text-5xl">10M+</div>
                <p class="mt-2 text-sm font-medium"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">Tickets resolved</p>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-brand sm:text-5xl">98%</div>
                <p class="mt-2 text-sm font-medium"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">Customer satisfaction</p>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-brand sm:text-5xl">&lt;2min</div>
                <p class="mt-2 text-sm font-medium"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#17494D]/70'">Avg. first response</p>
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
                <span class="mb-3 inline-block rounded-full bg-brand/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-brand">Omnichannel</span>
                <h3 class="mb-4 text-3xl font-bold tracking-tight">Meet your customers where they are</h3>
                <p class="mb-6 text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                    Email, chat, social, phone — every conversation in one unified inbox. No more switching between tools or losing context.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Unified agent workspace
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Full conversation history
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Smart channel routing
                    </li>
                </ul>
            </div>
            {{-- Illustration placeholder --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="flex h-64 w-full items-center justify-center rounded-2xl border-2 border-dashed sm:h-80"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.02]' : 'border-gray-200 bg-gray-50'">
                    <svg class="h-20 w-20" :class="$store.ui.darkMode ? 'text-white/10' : 'text-gray-200'" fill="currentColor" viewBox="0 0 24 24"><path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z"/><path d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z"/></svg>
                </div>
            </div>
        </div>

        {{-- Highlight 2: AI-Powered (reversed) --}}
        <div class="flex flex-col-reverse items-center gap-12 md:flex-row">
            {{-- Illustration placeholder --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="flex h-64 w-full items-center justify-center rounded-2xl border-2 border-dashed sm:h-80"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.02]' : 'border-gray-200 bg-gray-50'">
                    <svg class="h-20 w-20" :class="$store.ui.darkMode ? 'text-white/10' : 'text-gray-200'" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .75a8.25 8.25 0 00-4.135 15.39c.686.398 1.115 1.008 1.134 1.623a.75.75 0 00.577.706 7.998 7.998 0 004.848 0 .75.75 0 00.577-.706c.02-.615.448-1.225 1.134-1.623A8.25 8.25 0 0012 .75z"/><path fill-rule="evenodd" d="M9.013 19.9a.75.75 0 01.877-.597 11.319 11.319 0 004.22 0 .75.75 0 11.28 1.473 12.819 12.819 0 01-4.78 0 .75.75 0 01-.597-.876zM9.754 22.344a.75.75 0 01.824-.668 13.682 13.682 0 002.844 0 .75.75 0 11.156 1.492 15.156 15.156 0 01-3.156 0 .75.75 0 01-.668-.824z" clip-rule="evenodd"/></svg>
                </div>
            </div>
            <div class="flex-1">
                <span class="mb-3 inline-block rounded-full bg-brand/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-brand">AI-Powered</span>
                <h3 class="mb-4 text-3xl font-bold tracking-tight">Resolve issues before they escalate</h3>
                <p class="mb-6 text-[0.9375rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                    Built-in AI suggests responses, summarizes tickets and surfaces relevant knowledge base articles — so agents can focus on what matters.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        AI-suggested replies
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Auto-ticket summaries
                    </li>
                    <li class="flex items-center gap-3 text-[0.9375rem]"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        <svg class="h-5 w-5 shrink-0 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
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
                <p class="mx-auto max-w-xl text-lg"
                    :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
                    See why thousands of teams choose us to power their customer support.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                {{-- Testimonial 1 --}}
                <div class="rounded-2xl border p-8 transition-colors duration-300"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03]' : 'border-gray-200 bg-white'">
                    <div class="mb-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-brand" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mb-6 text-[0.9375rem] leading-relaxed"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        &ldquo;Reduced our average response time by 60%. The automation features are a game changer for our team.&rdquo;
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/20 text-sm font-bold text-brand">SJ</div>
                        <div>
                            <div class="text-sm font-semibold">Sarah Johnson</div>
                            <div class="text-xs"
                                :class="$store.ui.darkMode ? 'text-white/40' : 'text-gray-400'">Head of Support, TechCorp</div>
                        </div>
                    </div>
                </div>

                {{-- Testimonial 2 --}}
                <div class="rounded-2xl border p-8 transition-colors duration-300"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03]' : 'border-gray-200 bg-white'">
                    <div class="mb-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-brand" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mb-6 text-[0.9375rem] leading-relaxed"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        &ldquo;The unified inbox is exactly what we needed. Our agents love it and our CSAT scores have never been higher.&rdquo;
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/20 text-sm font-bold text-brand">MR</div>
                        <div>
                            <div class="text-sm font-semibold">Michael Rivera</div>
                            <div class="text-xs"
                                :class="$store.ui.darkMode ? 'text-white/40' : 'text-gray-400'">CTO, StartupFlow</div>
                        </div>
                    </div>
                </div>

                {{-- Testimonial 3 --}}
                <div class="rounded-2xl border p-8 transition-colors duration-300"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03]' : 'border-gray-200 bg-white'">
                    <div class="mb-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-brand" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mb-6 text-[0.9375rem] leading-relaxed"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-gray-600'">
                        &ldquo;We migrated from Zendesk and haven&rsquo;t looked back. Faster, cleaner and the AI features actually work.&rdquo;
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/20 text-sm font-bold text-brand">AL</div>
                        <div>
                            <div class="text-sm font-semibold">Amy Liu</div>
                            <div class="text-xs"
                                :class="$store.ui.darkMode ? 'text-white/40' : 'text-gray-400'">VP Operations, CloudBase</div>
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
        <p class="mx-auto mb-10 max-w-xl text-lg"
            :class="$store.ui.darkMode ? 'text-white/50' : 'text-gray-500'">
            Join thousands of teams delivering faster, smarter customer support. Start your free trial today — no credit card required.
        </p>
        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-10 py-4 text-base font-semibold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/30">
                        Go to Dashboard
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-10 py-4 text-base font-semibold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/30">
                        Start free trial
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
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

    <!-- Footer -->
    <x-footer />

    <!-- Chatbot -->
    <x-chatbot />
</body>

</html>