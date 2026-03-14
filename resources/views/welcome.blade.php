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
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/welcome.css'])

    <!-- Alpine.js — ui-state store must load before Alpine starts -->
    <x-ui.ui-state />
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
    :class="$store.ui.darkMode ? 'bg-black text-white' : 'bg-[#FFF0E5] text-[#17494D]'">

    <!-- Navigation -->
    <x-layout.nav-bar />

    {{-- ═══════════════════════════════════════════════════════════════════
        HERO — Full-width centered w/ badge, headline, subtitle, CTAs
    ═══════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden">
        {{-- Background glow --}}
        <div class="pointer-events-none absolute inset-0" :class="$store.ui.darkMode ? 'opacity-100' : 'opacity-0'"
            style="transition: opacity 0.3s">
            <div
                class="absolute left-1/2 top-0 h-[600px] w-[900px] -translate-x-1/2 -translate-y-1/3 rounded-full bg-brand/10 blur-[120px]">
            </div>
        </div>

        <div class="relative mx-auto max-w-4xl px-6 pb-20 pt-28 text-center sm:pb-28 sm:pt-36">
            {{-- Badge --}}
            <div class="mb-8 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-medium uppercase tracking-widest transition-colors duration-300"
                :class="$store.ui.darkMode ? 'border-brand/30 text-brand' : 'border-brand/40 bg-brand/5 text-brand-dark'">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                Support made simple
            </div>

            {{-- Headline --}}
            <h1 class="mb-6 text-5xl font-bold leading-[1.1] tracking-tight sm:text-6xl lg:text-7xl">
                Get help fast.
                <br>
                <span class="text-brand">Stay organized.</span>
            </h1>

            {{-- Subtitle --}}
            <p class="mx-auto mb-10 max-w-2xl text-lg leading-relaxed sm:text-xl"
                :class="$store.ui.darkMode ? 'text-white/55' : 'text-[#17494D]/80'">
                The complete helpdesk platform to manage tickets, collaborate with your team and deliver outstanding
                customer support — all in one place.
            </p>

            {{-- CTA buttons --}}
            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('tickets', Auth::user()->company->slug) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-brand px-8 py-3.5 text-[0.9375rem] font-semibold text-black shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/30">
                            Go to Dashboard
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand px-8 py-3.5 text-[0.9375rem] font-semibold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/30">
                            Start free trial
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-xl border px-8 py-3.5 text-[0.9375rem] font-semibold transition hover:-translate-y-0.5"
                            :class="$store.ui.darkMode ? 'border-white/20 text-white hover:border-white/40 hover:bg-white/5' :
                                'border-[#17494D]/30 text-[#17494D] hover:border-[#17494D]/50 hover:bg-[#17494D]/5'">
                            View demo
                        </a>
                    @endauth
                @endif
            </div>

            {{-- Social proof line --}}
            <p class="mt-8 text-sm" :class="$store.ui.darkMode ? 'text-white/35' : 'text-gray-400'">
                Trusted by 2,000+ support teams worldwide
            </p>
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
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' :
                    'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
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
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' :
                    'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
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
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' :
                    'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
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
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' :
                    'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
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
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' :
                    'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
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
                :class="$store.ui.darkMode ? 'border-white/10 bg-white/[0.03] hover:border-brand/30 hover:bg-brand/[0.04]' :
                    'border-gray-200 bg-white hover:border-brand/40 hover:shadow-lg'">
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
                            <div class="text-xs" :class="$store.ui.darkMode ? 'text-white/40' : 'text-gray-400'">Head
                                of Support, TechCorp</div>
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
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-10 py-4 text-base font-semibold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/30">
                        Start free trial
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-10 py-4 text-base font-semibold transition hover:-translate-y-0.5"
                        :class="$store.ui.darkMode ? 'border-white/20 text-white hover:border-white/40 hover:bg-white/5' :
                            'border-[#17494D]/30 text-[#17494D] hover:border-[#17494D]/50 hover:bg-[#17494D]/5'">
                        Talk to sales
                    </a>
                @endauth
            @endif
        </div>
    </section>

    <!-- Footer -->
    <x-layout.footer />

    <!-- Chatbot -->
    <x-chatbot />
</body>

</html>
