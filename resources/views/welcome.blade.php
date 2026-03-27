<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Helpdesk System</title>

    <link rel="icon" href="{{ asset('images/Logos/logos without text DM.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/Logos/logos without text DM.png') }}">

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

        @keyframes gs-wiggle-loop {

            0%,
            100% {
                transform: rotate(0deg) scale(1);
            }

            20% {
                transform: rotate(-2.5deg) scale(1.02);
            }

            40% {
                transform: rotate(2.5deg) scale(1.03);
            }

            60% {
                transform: rotate(-1.5deg) scale(1.02);
            }

            80% {
                transform: rotate(1.5deg) scale(1.01);
            }
        }

        @keyframes gs-red-pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.42), 0 0 0 6px rgba(220, 38, 38, 0.12);
                filter: saturate(1);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(220, 38, 38, 0.12), 0 0 0 12px rgba(220, 38, 38, 0.06);
                filter: saturate(1.12);
            }
        }

        .gs-younes-wiggle-red {
            animation: gs-wiggle-loop 2.6s ease-in-out infinite, gs-red-pulse 1.9s ease-in-out infinite;
            transform-origin: center;
            will-change: transform, box-shadow, filter;
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
                :class="heroMsgOk ? 'text-[#219653]' : 'text-red-500'"></p>

            {{-- CTA area --}}
            @if (Route::has('login'))
                @auth
                    {{-- Logged-in → Dashboard --}}
                    <div class="mt-10 flex justify-center sm:mt-12">
                        <a href="{{ Auth::user()->company ? route('tickets', Auth::user()->company->slug) : route('home') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-[#219653] px-8 py-4 text-[1.05rem] font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#1b7a44] hover:shadow-xl">
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
                            :class="$store.ui.darkMode ?
                                                                                                                                                                                                                                        'border-white/20 bg-white/5 text-white placeholder-white/40 focus:border-white/40 focus:ring-1 focus:ring-white/20' :
                                                                                                                                                                                                                                        'border-gray-400 bg-white text-gray-800 placeholder-gray-400 focus:border-gray-500 focus:ring-1 focus:ring-gray-400'"
                            :placeholder="$store.ui.t('heroPlaceholder')" />
                        <button type="submit"
                            class="w-full flex-shrink-0 cursor-pointer rounded-full bg-[#219653] px-8 py-4 text-base font-bold text-white transition hover:bg-[#4cc944] sm:w-auto sm:px-[30px] sm:py-[15px] sm:text-[17px]"
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
            }, {
                threshold: 0.15
            });

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
            activeTab: 'ticketView',
            imageTab: 'ticketView',
            automationNotificationVisible: true,
            imageVisible: true,
            tabs: ['ticketList', 'ticketView', 'automations', 'reports'],
            imageMap: {
                ticketList: '{{ asset('images/Personnes/ticketlist.png') }}',
                ticketView: '{{ asset('images/Personnes/ticket view.png') }}',
                automations: '{{ asset('images/Personnes/Automatin.png') }}',
                reports: '{{ asset('images/Personnes/reports.png') }}'
            },
            switchTab(tab) {
                this.activeTab = tab;

                if (tab === 'automations') {
                    this.automationNotificationVisible = false;
                }

                if (this.imageTab === tab) {
                    return;
                }

                this.imageVisible = false;

                setTimeout(() => {
                    this.imageTab = tab;
                    this.imageVisible = true;
                }, 550);
            }
        }">

        <div class="mx-auto flex w-full max-w-6xl flex-col items-center">




            {{-- Title --}}
            <h2 class="mb-4 text-center text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-[3.75rem]"
                :class="$store.ui.darkMode ? 'text-white' : 'text-gray-950'" x-text="$store.ui.t('discoverTitle')">
            </h2>

            <p class="mb-12 max-w-3xl text-center text-base leading-relaxed sm:text-lg"
                :class="$store.ui.darkMode ? 'text-white/60' : 'text-gray-600'">
                Explore powerful workflows, ticket views, and reports built to help your team move faster with
                confidence.
            </p>

            {{-- Tabs --}}
            <div class="mb-12 w-full max-w-5xl ">
                <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                    <template x-for="tab in tabs" :key="tab">
                        <button type="button"
                            class="group relative cursor-pointer rounded-xl px-4 py-3 text-sm font-medium transition-all duration-300"
                            :class="activeTab === tab ?
                                ($store.ui.darkMode ?
                                    'bg-white text-gray-900 shadow-[0_10px_30px_-14px_rgba(255,255,255,0.95)]' :
                                    'bg-gray-900 text-white shadow-[0_10px_24px_-14px_rgba(0,0,0,0.5)]') :
                                ($store.ui.darkMode ?
                                    'text-white/65 hover:bg-white/10 hover:text-white' :
                                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900')" @click="switchTab(tab)">
                            <span class="inline-flex items-center gap-2">
                                <span x-text="$store.ui.t('discoverTab_' + tab)"></span>
                                <span x-show="tab === 'automations' && automationNotificationVisible"
                                    class="relative inline-flex h-2.5 w-2.5">
                                    <span
                                        class="absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75 animate-ping"></span>
                                    <span
                                        class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500 animate-pulse"></span>
                                </span>
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Image container --}}
            <div class="m-0 w-full max-w-[1800px] p-0 transition-all duration-500" id="discoverVisualPanel"
                :class="activeTab === 'automations' ? 'self-start' : 'self-center'">
                <img :src="imageMap[imageTab]" :alt="$store.ui.t('discoverTab_' + imageTab)"
                    :style="{ opacity: imageVisible ? '1' : '0', transition: 'opacity 500ms ease-in-out' }"
                    class="m-0 block h-auto w-full p-0" />


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
    <div class="sfs-section" id="sfsSection">
        <div class="sfs-header">
            <h2 :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'" x-text="$store.ui.t('homeSfsTitle')"></h2>
            <div class="sfs-nav-btns">
                <button class="sfs-nav-btn" id="sfsPrevBtn">⬅</button>
                <button class="sfs-nav-btn" id="sfsNextBtn">➡</button>
            </div>
        </div>

        <div class="sfs-slider-outer group"
            :class="$store.ui.darkMode ? 'before:from-black after:from-black' : 'before:from-white after:from-white'">
            <div class="sfs-slider-track" id="sfsTrack">

                <!-- 1 Automation Engine -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c3">
                        <div
                            style="position:absolute;top:20px;left:20px;right:20px;background:rgba(255,255,255,0.82);border:1px solid rgba(255,255,255,0.6);border-radius:16px;padding:16px 18px;box-shadow:0 10px 24px rgba(200,140,60,0.1);backdrop-filter:blur(8px);">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                                <span
                                    style="font-size:11px;font-weight:700;color:#6366f1;background:#ede9fe;border-radius:8px;padding:3px 10px;">⚡
                                    <span x-text="$store.ui.t('homeSfsPreviewAutomationRule')"></span></span>
                                <span
                                    style="font-size:10px;color:#219653;background:#dcfce7;border-radius:6px;padding:2px 8px;"
                                    x-text="$store.ui.t('homeSfsPreviewActive')"></span>
                            </div>
                            <div
                                style="background:rgba(248,250,252,0.9);border-radius:10px;padding:10px 12px;margin-bottom:10px;font-size:12px;">
                                <div
                                    style="color:#94a3b8;font-size:9px;font-weight:700;letter-spacing:.08em;margin-bottom:6px;">
                                    <span x-text="$store.ui.t('homeSfsPreviewWhen')"></span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span
                                        style="background:#dbeafe;color:#1e40af;border-radius:6px;padding:3px 8px;font-size:11px;">Category</span>
                                    <span style="color:#cbd5e1;font-size:11px;">is</span>
                                    <span
                                        style="background:#fce7f3;color:#9d174d;border-radius:6px;padding:3px 8px;font-size:11px;">Billing</span>
                                </div>
                            </div>
                            <div
                                style="background:rgba(240,253,244,0.9);border-radius:10px;padding:10px 12px;font-size:12px;">
                                <div
                                    style="color:#94a3b8;font-size:9px;font-weight:700;letter-spacing:.08em;margin-bottom:6px;">
                                    <span x-text="$store.ui.t('homeSfsPreviewThen')"></span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;color:#374151;font-size:11px;">
                                    <span style="color:#219653;">→</span> <span
                                        x-text="$store.ui.t('homeSfsPreviewAssignTo')"></span> <strong
                                        x-text="$store.ui.t('homeSfsPreviewBillingTeam')"></strong>
                                </div>
                            </div>
                        </div>
                        <div class="sfs-anim-float"
                            style="position:absolute;bottom:18px;left:26px;background:#fff;border-radius:10px;padding:8px 14px;font-size:10px;color:#94a3b8;box-shadow:0 4px 14px rgba(0,0,0,0.1);">
                            ⚡ <span x-text="$store.ui.t('homeSfsPreviewRanTimes')"></span>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                            <span x-text="$store.ui.t('homeSfsAutomationTitle')"></span>
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeSfsAutomationBody')"></span>
                        </div>
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
                                <div class="sfs-tag-chip" style="background:#dcfce7;color:#219653;">🟢 Resolved</div>
                                <div class="sfs-tag-chip" style="background:#fef3c7;color:#92400e;">🟡 Pending</div>
                                <div class="sfs-tag-chip" style="background:#ede9fe;color:#5b21b6;">🟣 Bug</div>
                                <div class="sfs-tag-chip" style="background:#f1f5f9;color:#334155;">⚪ General</div>
                                <div class="sfs-tag-chip" style="background:#fce7f3;color:#be185d;">🏷 Onboarding
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                            x-text="$store.ui.t('homeSfsTagsTitle')"></div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeSfsTagsBody')"></span>
                        </div>
                    </div>
                </div>

                <!-- 3 Multiple Inboxes -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c5">
                        <div class="sfs-mi-av-row" id="sfsMiAvRow">
                            <img src="{{ asset('images/Personnes/walid_photo.jpeg') }}" alt="Michael Rivera"
                                class="sfs-mi-av border-white object-cover" />
                            <img src="{{ asset('images/Personnes/Younes_Photo.jpg') }}" alt="Sarah Johnson"
                                class="sfs-mi-av border-white object-cover" />
                            <img src="{{ asset('images/Personnes/bilal_photo.jpeg') }}" alt="Amy Liu"
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
                            <span x-text="$store.ui.t('homeSfsInboxesTitle')"></span>
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeSfsInboxesBody')"></span>
                        </div>
                    </div>
                </div>

                <!-- 4 AI Copilot -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c4">
                        <!-- Customer bubble -->
                        <div
                            style="position:absolute;top:18px;left:18px;right:18px;display:flex;align-items:flex-start;gap:9px;">
                            <div
                                style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;">
                                CL</div>
                            <div
                                style="background:rgba(255,255,255,0.85);border-radius:0 12px 12px 12px;padding:10px 13px;font-size:11px;color:#374151;line-height:1.5;box-shadow:0 4px 16px rgba(0,0,0,0.06);backdrop-filter:blur(6px);">
                                <span x-text="$store.ui.t('homeSfsPreviewAiCustomerMsg')"></span>
                            </div>
                        </div>
                        <!-- AI suggestion panel -->
                        <div
                            style="position:absolute;bottom:16px;left:18px;right:18px;background:rgba(255,255,255,0.88);border:1.5px solid #c7d2fe;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(99,102,241,0.12);backdrop-filter:blur(8px);">
                            <div
                                style="background:linear-gradient(90deg,#ede9fe,#e0e7ff);padding:8px 12px;display:flex;align-items:center;justify-content:space-between;">
                                <span style="font-size:10px;font-weight:700;color:#4f46e5;">✨ <span
                                        x-text="$store.ui.t('homeSfsPreviewAiSuggestion')"></span></span>
                                <div style="display:flex;gap:4px;">
                                    <span
                                        style="font-size:9px;background:#4f46e5;color:#fff;border-radius:5px;padding:2px 7px;"
                                        x-text="$store.ui.t('homeSfsPreviewFriendly')"></span>
                                    <span
                                        style="font-size:9px;background:#fff;color:#6366f1;border:1px solid #c7d2fe;border-radius:5px;padding:2px 7px;"
                                        x-text="$store.ui.t('homeSfsPreviewFormal')"></span>
                                </div>
                            </div>
                            <div style="padding:10px 12px;font-size:11px;color:#374151;line-height:1.55;">
                                Sorry about the delay! I've flagged order #4821 with logistics — expect an update within
                                <strong>24h</strong>. 📦
                            </div>
                            <div style="padding:2px 12px 10px;display:flex;gap:6px;">
                                <span
                                    style="font-size:10px;background:#4f46e5;color:#fff;border-radius:7px;padding:4px 12px;">Use
                                    <span x-text="$store.ui.t('homeSfsPreviewUseReply')"></span></span>
                                <span
                                    style="font-size:10px;background:rgba(0,0,0,0.04);color:#64748b;border-radius:7px;padding:4px 12px;"
                                    x-text="$store.ui.t('homeSfsPreviewRegenerate')"></span>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">AI
                            <span x-text="$store.ui.t('homeSfsAiTitle')"></span>
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeSfsAiBody')"></span>
                        </div>
                    </div>
                </div>

                <!-- 5 SLA Enforcement -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c1">
                        <!-- Main panel -->
                        <div
                            style="position:absolute;top:18px;left:18px;right:18px;background:rgba(255,255,255,0.72);border:1px solid rgba(255,255,255,0.65);border-radius:16px;padding:14px 16px;box-shadow:0 10px 24px rgba(110,90,140,0.08);backdrop-filter:blur(8px);">
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                <span style="font-size:11px;font-weight:700;color:#444;">Ticket Queue</span>
                                <span class="sfs-anim-pulse"
                                    style="font-size:9px;color:#dc2626;background:rgba(254,242,242,0.9);border-radius:6px;padding:3px 8px;font-weight:700;">2
                                    <span x-text="$store.ui.t('homeSfsPreviewBreached')"></span></span>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                <div style="background:rgba(254,242,242,0.8);border-radius:10px;padding:9px 12px;">
                                    <div style="font-size:11px;font-weight:600;color:#374151;">#1042 — <span
                                            x-text="$store.ui.t('homeSfsPreviewPaymentFailed')"></span>
                                    </div>
                                    <div style="font-size:10px;color:#dc2626;margin-top:2px;">⏱ <span
                                            x-text="$store.ui.t('homeSfsPreviewSlaBreachedUrgent')"></span>
                                    </div>
                                </div>
                                <div style="background:rgba(255,251,235,0.8);border-radius:10px;padding:9px 12px;">
                                    <div style="font-size:11px;font-weight:600;color:#374151;">#1039 — <span
                                            x-text="$store.ui.t('homeSfsPreviewCantLogin')"></span></div>
                                    <div style="font-size:10px;color:#b45309;margin-top:2px;">⚠ <span
                                            x-text="$store.ui.t('homeSfsPreviewMinutesLeftHigh')"></span></div>
                                </div>
                            </div>
                        </div>
                        <!-- SLA health bar -->
                        <div
                            style="position:absolute;bottom:22px;left:26px;right:26px;display:flex;gap:4px;align-items:center;">
                            <div style="flex:1;height:5px;background:#fecaca;border-radius:4px;"></div>
                            <div style="flex:2;height:5px;background:#fde68a;border-radius:4px;"></div>
                            <div style="flex:4;height:5px;background:#bbf7d0;border-radius:4px;"></div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">SLA
                            <span x-text="$store.ui.t('homeSlaCardTitle')"></span>
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeSlaCardBody')"></span>
                        </div>
                    </div>
                </div>

                <!-- 6 Knowledge Base -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c6">
                        <!-- Search bar -->
                        <div
                            style="position:absolute;top:18px;left:18px;right:18px;background:rgba(255,255,255,0.88);border-radius:12px;padding:9px 14px;display:flex;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(0,0,0,0.06);backdrop-filter:blur(6px);">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                                stroke-width="2.5">
                                <circle cx="11" cy="11" r="8" />
                                <path d="M21 21l-4.35-4.35" />
                            </svg>
                            <span style="font-size:11px;color:#94a3b8;"
                                x-text="$store.ui.t('homeSfsPreviewKbSearch')"></span>
                        </div>
                        <!-- Article cards -->
                        <div
                            style="position:absolute;top:62px;left:18px;right:18px;display:flex;flex-direction:column;gap:6px;">
                            <div
                                style="background:rgba(255,255,255,0.85);border-radius:11px;padding:10px 13px;box-shadow:0 3px 12px rgba(0,0,0,0.06);backdrop-filter:blur(6px);">
                                <div style="font-size:11px;font-weight:600;color:#374151;">📄 <span
                                        x-text="$store.ui.t('homeSfsPreviewKbArticle1')"></span></div>
                                <div style="font-size:10px;color:#94a3b8;margin-top:3px;"
                                    x-text="$store.ui.t('homeSfsPreviewKbMeta1')"></div>
                            </div>
                            <div
                                style="background:rgba(255,255,255,0.85);border-radius:11px;padding:10px 13px;box-shadow:0 3px 12px rgba(0,0,0,0.06);backdrop-filter:blur(6px);">
                                <div style="font-size:11px;font-weight:600;color:#374151;">📄 <span
                                        x-text="$store.ui.t('homeSfsPreviewKbArticle2')"></span></div>
                                <div style="font-size:10px;color:#94a3b8;margin-top:3px;"
                                    x-text="$store.ui.t('homeSfsPreviewKbMeta2')"></div>
                            </div>
                            <div
                                style="background:rgba(255,255,255,0.85);border-radius:11px;padding:10px 13px;box-shadow:0 3px 12px rgba(0,0,0,0.06);backdrop-filter:blur(6px);">
                                <div style="font-size:11px;font-weight:600;color:#374151;">📝 <span
                                        x-text="$store.ui.t('homeSfsPreviewKbArticle3')"></span></div>
                                <div style="font-size:10px;color:#94a3b8;margin-top:3px;"
                                    x-text="$store.ui.t('homeSfsPreviewKbMeta3')"></div>
                            </div>
                        </div>
                        <!-- Bottom badge -->
                        <div class="sfs-anim-float"
                            style="position:absolute;bottom:16px;left:50%;transform:translateX(-50%);background:#fff;border-radius:10px;padding:6px 14px;font-size:10px;color:#94a3b8;box-shadow:0 4px 14px rgba(0,0,0,0.08);white-space:nowrap;">
                            <span x-text="$store.ui.t('homeSfsPreviewKbBadge')"></span>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                            <span x-text="$store.ui.t('homeKnowledgeCardTitle')"></span>
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeKnowledgeCardBody')"></span>
                        </div>
                    </div>
                </div>

                <!-- 7 Reports -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c7">
                        <div class="sfs-rp-wrap">
                            <div class="sfs-rp-head">📊 <span x-text="$store.ui.t('homeSfsPreviewReportsHead')"></span>
                            </div>
                            <div class="sfs-rp-bars">
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b sfs-anim-bar-grow" style="height:40%"></div>
                                    <div class="sfs-rp-l">Mon</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b sfs-anim-bar-grow" style="height:65%;animation-delay:0.3s">
                                    </div>
                                    <div class="sfs-rp-l">Tue</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b hi sfs-anim-bar-grow" style="height:100%;animation-delay:0.6s">
                                    </div>
                                    <div class="sfs-rp-l">Wed</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b sfs-anim-bar-grow" style="height:72%;animation-delay:0.9s">
                                    </div>
                                    <div class="sfs-rp-l">Thu</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b sfs-anim-bar-grow" style="height:55%;animation-delay:1.2s">
                                    </div>
                                    <div class="sfs-rp-l">Fri</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b sfs-anim-bar-grow" style="height:30%;animation-delay:1.5s">
                                    </div>
                                    <div class="sfs-rp-l">Sat</div>
                                </div>
                                <div class="sfs-rp-bw">
                                    <div class="sfs-rp-b sfs-anim-bar-grow" style="height:20%;animation-delay:1.8s">
                                    </div>
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
                                    <div class="sfs-rp-key" x-text="$store.ui.t('homeSfsPreviewAvgReply')"></div>
                                </div>
                                <div class="sfs-rp-stat">
                                    <div class="sfs-rp-val">342</div>
                                    <div class="sfs-rp-key" x-text="$store.ui.t('homeSfsPreviewResolved')"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">
                            <span x-text="$store.ui.t('homeSfsReportsTitle')"></span>
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeSfsReportsBody')"></span>
                        </div>
                    </div>
                </div>

                <!-- 8 Smart Routing -->
                <div class="sfs-card" :class="$store.ui.darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white'">
                    <div class="sfs-card-preview sfs-c2">
                        <!-- Ticket badge -->
                        <div
                            style="position:absolute;top:18px;left:18px;right:18px;background:rgba(255,255,255,0.88);border-radius:14px;padding:12px 16px;box-shadow:0 6px 20px rgba(70,100,180,0.1);backdrop-filter:blur(8px);">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                                <span
                                    style="font-size:11px;font-weight:700;color:#1e40af;background:#dbeafe;border-radius:8px;padding:3px 10px;">🎫
                                    #1048</span>
                                <span
                                    style="font-size:10px;color:#dc2626;background:#fef2f2;border-radius:6px;padding:2px 8px;font-weight:600;"
                                    x-text="$store.ui.t('homeSfsPreviewUrgent')"></span>
                            </div>
                            <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;"
                                x-text="$store.ui.t('homeSfsPreviewAccount2fa')"></div>
                            <div style="font-size:10px;color:#94a3b8;"
                                x-text="$store.ui.t('homeSfsPreviewCategoryAccount')"></div>
                        </div>
                        <!-- Routing result -->
                        <div
                            style="position:absolute;bottom:18px;left:18px;right:18px;display:flex;flex-direction:column;gap:6px;">
                            <div
                                style="background:rgba(255,255,255,0.92);border-radius:12px;padding:10px 14px;box-shadow:0 4px 14px rgba(0,0,0,0.08);display:flex;align-items:center;gap:10px;backdrop-filter:blur(6px);">
                                <div
                                    style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;">
                                    SK</div>
                                <div>
                                    <div style="font-size:11px;font-weight:600;color:#374151;"
                                        x-text="$store.ui.t('homeSfsPreviewAssignedSara')"></div>
                                    <div style="font-size:10px;color:#94a3b8;"
                                        x-text="$store.ui.t('homeSfsPreviewAccountTeamTickets')"></div>
                                </div>
                                <span style="margin-left:auto;color:#219653;font-size:14px;">✓</span>
                            </div>
                            <div style="text-align:center;font-size:10px;color:#94a3b8;">⚡ <span
                                    x-text="$store.ui.t('homeSfsPreviewRoutedIn')"></span> <strong
                                    style="color:#6366f1;">0.3s</strong></div>
                        </div>
                    </div>
                    <div class="sfs-card-body">
                        <div class="sfs-card-title" :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'">Smart
                            <span x-text="$store.ui.t('homeSfsRoutingTitle')"></span>
                        </div>
                        <div class="sfs-card-desc" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'">
                            <span x-text="$store.ui.t('homeSfsRoutingBody')"></span>
                        </div>
                    </div>
                </div>

            </div><!-- /track -->
        </div><!-- /slider-outer -->
    </div>


    {{-- ═══════════════════════════════════════════════════════════════════
    BEACON 1— Embeddable support hub section
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="beaconSection1"
        class="w-full px-10 py-15 opacity-0 translate-y-8 blur-sm transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="$store.ui.darkMode ? 'bg-[#0b111b]' : 'bg-white'">
        <div id="beaconWrapper"
            class="mx-auto flex w-full max-w-[1060px] items-center justify-center gap-[72px] max-lg:flex-col max-lg:gap-12">

            <div class="w-full max-w-[320px] flex-none">
                <h1 class="mb-12 text-[2rem] leading-[1.25] font-bold"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-[#1c1c2e]'" x-text="$store.ui.t('beacon1Title')">
                </h1>

                <div class="flex flex-col" id="beaconTabs">
                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="0">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e0e0e0]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon1Tab1Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon1Tab1Body')"></div>
                        </div>
                    </div>

                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="1">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e0e0e0]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon1Tab2Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon1Tab2Body')"></div>
                        </div>
                    </div>

                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="2">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e0e0e0]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon1Tab3Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon1Tab3Body')"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="relative h-[580px] w-[420px] flex-none overflow-hidden rounded-[22px] bg-[linear-gradient(135deg,#1c1b3a_0%,#3b2a3a_50%,#1c1b3a_100%)] max-lg:h-[520px] max-lg:w-full max-lg:max-w-[420px]">
                <div class="beacon-panel absolute inset-0 flex items-center justify-center opacity-100 transition-all duration-300"
                    id="beaconPanel0">
                    <img class="beacon-panel-img absolute inset-0 z-10 hidden h-full w-full rounded-[22px] object-contain"
                        src="" alt="Tab 0 preview" data-tab-img="0" />

                    <div class="beacon-panel-mockup">
                        <div
                            class="flex max-h-[520px] w-[270px] flex-col overflow-hidden rounded-[18px] bg-white shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                            <div class="flex flex-none items-center justify-between bg-[#219653] px-[15px] py-[13px]">
                                <div class="flex items-center gap-[10px]">
                                    <div
                                        class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-white/25 text-[0.82rem] font-bold text-white">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#fff"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="10" rx="2" />
                                            <path d="M12 2v4M8 11V9a4 4 0 018 0v2" />
                                            <circle cx="9" cy="16" r="1" fill="#fff" />
                                            <circle cx="15" cy="16" r="1" fill="#fff" />
                                        </svg>
                                    </div>
                                    <span class="text-[0.9rem] font-semibold text-white"
                                        x-text="$store.ui.t('homeBeaconAiAssistant')"></span>
                                </div>
                                <button
                                    class="cursor-pointer rounded-[20px] border-[1.5px] border-white/65 bg-transparent px-[11px] py-1 text-[0.74rem] font-medium text-white"
                                    x-text="$store.ui.t('homeBeaconEndChat')"></button>
                            </div>
                            <div class="flex flex-1 flex-col gap-[9px] overflow-hidden bg-[#f9fafb] p-[14px]">
                                <div class="mb-px text-[0.68rem] text-[#6b7280]"
                                    x-text="$store.ui.t('homeBeaconAiAssistant')"></div>
                                <div
                                    class="max-w-[86%] self-start rounded-[13px] rounded-bl-[4px] border border-[#e5e7eb] bg-white px-[13px] py-[10px] text-[0.79rem] leading-[1.5] text-[#1c1c2e]">
                                    <span x-text="$store.ui.t('beacon1DemoMsg1')"></span>
                                </div>
                                <div
                                    class="max-w-[86%] self-end rounded-[13px] rounded-br-[4px] bg-[#219653] px-[13px] py-[10px] text-[0.79rem] leading-[1.5] text-white">
                                    <span x-text="$store.ui.t('beacon1DemoMsg2')"></span>
                                </div>
                                <div class="mt-1 mb-px text-[0.68rem] text-[#6b7280]"
                                    x-text="$store.ui.t('homeBeaconAiAssistant')"></div>
                                <div
                                    class="max-w-[86%] self-start rounded-[13px] rounded-bl-[4px] border border-[#e5e7eb] bg-white px-[13px] py-[10px] text-[0.79rem] leading-[1.5] text-[#1c1c2e]">
                                    <span x-text="$store.ui.t('beacon1DemoMsg3')"></span>
                                </div>
                            </div>
                            <div class="flex-none border-t border-[#e5e7eb] bg-white px-[14px] py-[11px]">
                                <input type="text" readonly :placeholder="$store.ui.t('homeBeaconAskQuestion')"
                                    class="w-full border-none bg-transparent text-[0.79rem] text-[#9ca3af] outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="beacon-panel absolute inset-0 hidden items-center justify-center opacity-0 transition-all duration-300"
                    id="beaconPanel1">
                    <img class="beacon-panel-img absolute inset-0 z-10 hidden h-full w-full rounded-[22px] object-contain"
                        src="" alt="Tab 1 preview" data-tab-img="1" />

                    <div class="beacon-panel-mockup">
                        <div
                            class="flex max-h-[520px] w-[270px] flex-col overflow-hidden rounded-[18px] bg-white shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                            <div class="flex flex-none items-center justify-between bg-[#219653] px-[15px] py-[13px]">
                                <div class="flex items-center gap-[10px]">
                                    <div
                                        class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-white/25 text-[0.82rem] font-bold text-white">
                                        N</div>
                                    <span class="text-[0.9rem] font-semibold text-white">Nikita</span>
                                </div>
                                <button
                                    class="cursor-pointer rounded-[20px] border-[1.5px] border-white/65 bg-transparent px-[11px] py-1 text-[0.74rem] font-medium text-white"
                                    x-text="$store.ui.t('homeBeaconEndChat')"></button>
                            </div>
                            <div class="flex flex-1 flex-col gap-[9px] overflow-hidden bg-[#f9fafb] p-[14px]">
                                <div class="mb-px text-[0.68rem] text-[#6b7280]">Nikita</div>
                                <div
                                    class="max-w-[86%] self-start rounded-[13px] rounded-bl-[4px] border border-[#e5e7eb] bg-white px-[13px] py-[10px] text-[0.79rem] leading-[1.5] text-[#1c1c2e]">
                                    <span x-text="$store.ui.t('beacon1DemoHuman1')"></span>
                                </div>
                                <div
                                    class="max-w-[86%] self-end rounded-[13px] rounded-br-[4px] bg-[#219653] px-[13px] py-[10px] text-[0.79rem] leading-[1.5] text-white">
                                    <span x-text="$store.ui.t('beacon1DemoHuman2')"></span>
                                </div>
                                <div
                                    class="max-w-[86%] self-end rounded-[13px] rounded-br-[4px] bg-[#219653] px-[13px] py-[10px] text-[0.79rem] leading-[1.5] text-white">
                                    <span x-text="$store.ui.t('beacon1DemoHuman3')"></span>
                                </div>
                                <div class="mt-[6px] mb-px text-[0.68rem] text-[#6b7280]">Nikita</div>
                                <div
                                    class="flex w-[58px] items-center gap-1 self-start rounded-[13px] rounded-bl-[4px] border border-[#e5e7eb] bg-white px-[14px] py-[10px]">
                                    <span
                                        class="h-[5px] w-[5px] rounded-full bg-[#9ca3af] animate-bounce [animation-duration:1.2s]"></span>
                                    <span
                                        class="h-[5px] w-[5px] rounded-full bg-[#9ca3af] animate-bounce [animation-duration:1.2s] [animation-delay:0.2s]"></span>
                                    <span
                                        class="h-[5px] w-[5px] rounded-full bg-[#9ca3af] animate-bounce [animation-duration:1.2s] [animation-delay:0.4s]"></span>
                                </div>
                            </div>
                            <div class="flex-none border-t border-[#e5e7eb] bg-white px-[14px] py-[11px]">
                                <input type="text" readonly :placeholder="$store.ui.t('homeBeaconAskQuestion')"
                                    class="w-full border-none bg-transparent text-[0.79rem] text-[#9ca3af] outline-none" />
                            </div>
                        </div>

                        <div
                            class="absolute bottom-[72px] right-12 z-[5] flex items-center gap-[7px] rounded-[11px] bg-white px-[13px] py-[9px] text-[0.74rem] font-semibold text-[#1c1c2e] shadow-[0_6px_20px_rgba(0,0,0,0.18)]">
                            <span class="h-[7px] w-[7px] flex-none rounded-full bg-[#219653]"></span>
                            <span x-text="$store.ui.t('homeBeaconAgentsOnline')"></span>
                        </div>
                    </div>
                </div>

                <div class="beacon-panel absolute inset-0 hidden items-center justify-center opacity-0 transition-all duration-300"
                    id="beaconPanel2">
                    <img class="beacon-panel-img absolute inset-0 z-10 hidden h-full w-full rounded-[22px] object-contain"
                        src="" alt="Tab 2 preview" data-tab-img="2" />

                    <div class="beacon-panel-mockup">
                        <div
                            class="flex max-h-[520px] w-[270px] flex-col overflow-hidden rounded-[18px] bg-[#f4f5f7] shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                            <div class="flex flex-none items-center gap-[10px] bg-[#219653] px-[15px] py-[13px]">
                                <div class="flex cursor-pointer items-center text-white">
                                    <svg viewBox="0 0 24 24"
                                        class="h-[17px] w-[17px] fill-none stroke-white stroke-[2.5] [stroke-linecap:round] [stroke-linejoin:round]">
                                        <polyline points="15 18 9 12 15 6" />
                                    </svg>
                                </div>
                                <div class="mr-5 flex-1 text-center text-[0.9rem] font-semibold text-white"
                                    x-text="$store.ui.t('homeBeaconPreviousConversations')"></div>
                            </div>
                            <div class="flex flex-col gap-[9px] overflow-hidden px-[11px] py-[13px]">
                                <div class="mb-[-3px] px-[3px] text-[0.67rem] font-medium text-[#9ca3af]"
                                    x-text="$store.ui.t('homeBeaconLastUpdatedToday')"></div>

                                <div
                                    class="flex flex-col gap-[5px] rounded-[11px] bg-white px-[13px] py-[11px] shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                                    <div class="flex items-center justify-between">
                                        <div class="text-[0.77rem] font-bold text-[#219653]">Live chat 28 Apr 2025</div>
                                        <div
                                            class="flex h-[17px] w-[17px] flex-none items-center justify-center rounded-full bg-[#219653] text-[0.62rem] font-bold text-white">
                                            2</div>
                                    </div>
                                    <div class="line-clamp-2 text-[0.72rem] leading-[1.4] text-[#6b7280]">Happy mixing,
                                        friend!</div>
                                    <div class="mt-[3px] flex items-center">
                                        <div class="flex">
                                            <div class="h-5 w-5 rounded-full border-2 border-white bg-[#f472b6]"></div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-col gap-[5px] rounded-[11px] bg-white px-[13px] py-[11px] shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                                    <div class="flex items-center justify-between">
                                        <div class="text-[0.77rem] font-bold text-[#219653]">Pro tumblers in stock?
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 text-[0.66rem] text-[#9ca3af]">
                                        <svg viewBox="0 0 24 24"
                                            class="h-[11px] w-[11px] fill-none stroke-[#219653] stroke-[2.5] [stroke-linecap:round] [stroke-linejoin:round]">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        Received. Waiting for a reply
                                    </div>
                                </div>

                                <div class="mt-[3px] mb-[-3px] px-[3px] text-[0.67rem] font-medium text-[#9ca3af]">Last
                                    updated 3 days ago</div>

                                <div
                                    class="flex flex-col gap-[5px] rounded-[11px] bg-white px-[13px] py-[11px] shadow-[0_1px_4px_rgba(0,0,0,0.06)]">
                                    <div class="flex items-center justify-between">
                                        <div class="text-[0.77rem] font-bold text-[#219653]">New Stock Purchase Order
                                        </div>
                                        <div
                                            class="relative flex h-[17px] w-[17px] flex-none items-center justify-center rounded-full bg-[#219653] text-[0.62rem] font-bold text-white">
                                            3
                                            <span
                                                class="absolute -right-[3px] -top-[3px] h-[6px] w-[6px] rounded-full border-[1.5px] border-white bg-[#219653]"></span>
                                        </div>
                                    </div>
                                    <div class="line-clamp-2 text-[0.72rem] leading-[1.4] text-[#6b7280]">Great news, we
                                        just received a huge shipment containing both those items! Would you like…</div>
                                    <div class="mt-[3px] flex items-center">
                                        <div class="flex">
                                            <div class="h-5 w-5 rounded-full border-2 border-white bg-[#f472b6]"></div>
                                            <div class="-ml-1 h-5 w-5 rounded-full border-2 border-white bg-[#f59e0b]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="absolute bottom-[22px] right-[22px] z-[5] flex h-[46px] w-[46px] cursor-pointer items-center justify-center rounded-full bg-[#219653] shadow-[0_6px_20px_rgba(0,0,0,0.25)]">
                    <svg viewBox="0 0 24 24"
                        class="h-[18px] w-[18px] fill-none stroke-white stroke-[2.5] [stroke-linecap:round] [stroke-linejoin:round]">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    BEACON 2 — Flipped layout with image-only preview
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="beaconSection2"
        class="w-full px-10 py-15 opacity-0 translate-y-8 blur-sm transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="$store.ui.darkMode ? 'bg-[#0b111b]' : 'bg-white'">
        <div id="beaconWrapper2"
            class="mx-auto flex w-full max-w-[1060px] items-center justify-center gap-[72px] max-lg:flex-col max-lg:gap-12">

            <div
                class="relative h-[580px] w-[420px] flex-none overflow-hidden rounded-[22px] bg-[linear-gradient(135deg,#1c1b3a_0%,#3b2a3a_50%,#1c1b3a_100%)] max-lg:h-[520px] max-lg:w-full max-lg:max-w-[420px]">
                <div class="beacon-panel absolute inset-0 flex items-center justify-center opacity-100 transition-all duration-300"
                    id="beacon2Panel0">
                    <img class="beacon-panel-img absolute inset-0 z-10 h-full w-full rounded-[22px] object-cover"
                        src="https://hs-marketing-contentful.imgix.net/https%3A%2F%2Fimages.ctfassets.net%2Fp15sglj92v6o%2F2g26mWCMfs8ziyuevskl1j%2F4c69ec22fab1742fbdad3770f6aa1e0f%2Frouting--tag-refund.jpg?ixlib=gatsbySourceUrl-2.1.3&auto=format%2C%20compress&q=75&w=1540&h=2055&s=b21c38fc44ebea63004bf6e8df0a86a1"
                        alt="Beacon 2 tab 1 preview" />
                </div>

                <div class="beacon-panel absolute inset-0 hidden items-center justify-center opacity-0 transition-all duration-300"
                    id="beacon2Panel1">
                    <img class="beacon-panel-img absolute inset-0 z-10 h-full w-full rounded-[22px] object-cover"
                        src="https://hs-marketing-contentful.imgix.net/https%3A%2F%2Fimages.ctfassets.net%2Fp15sglj92v6o%2F22nlFXco6s5kxPyIfasKFd%2F0436229639f8fa9cedd49b3c86ecf127%2Frouting--old-convos.jpg?ixlib=gatsbySourceUrl-2.1.3&auto=format%2C%20compress&q=75&w=1540&h=2055&s=e86c1cb0da08a273a094e81056919bdb"
                        alt="Beacon 2 tab 2 preview" />
                </div>

                <div class="beacon-panel absolute inset-0 hidden items-center justify-center opacity-0 transition-all duration-300"
                    id="beacon2Panel2">
                    <img class="beacon-panel-img absolute inset-0 z-10 h-full w-full rounded-[22px] object-cover"
                        src="https://hs-marketing-contentful.imgix.net/https%3A%2F%2Fimages.ctfassets.net%2Fp15sglj92v6o%2F3lfXWEDesrRWBPf3pXChVc%2F7cd366710258b43ebdec922d95815401%2Frouting--bug-notify.jpg?ixlib=gatsbySourceUrl-2.1.3&auto=format%2C%20compress&q=75&w=1540&h=2055&s=1af1596dfac5caf5467d3fddc047564d"
                        alt="Beacon 2 tab 3 preview" />
                </div>
            </div>

            <div class="w-full max-w-[420px] flex-none">
                <h2 class="mb-12 text-[2rem] leading-[1.25] font-bold"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-[#1c1c2e]'" x-text="$store.ui.t('beacon2Title')">
                </h2>

                <div class="flex flex-col">
                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="0">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e5e7eb]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon2Tab1Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon2Tab1Body')"></div>
                        </div>
                    </div>

                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="1">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e5e7eb]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon2Tab2Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon2Tab2Body')"></div>
                        </div>
                    </div>

                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="2">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e5e7eb]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon2Tab3Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon2Tab3Body')"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
    BEACON 3 — Everything in one place
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="beaconSection3"
        class="w-full px-10 py-15 opacity-0 translate-y-8 blur-sm transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="$store.ui.darkMode ? 'bg-[#0b111b]' : 'bg-white'">
        <div id="beaconWrapper3"
            class="mx-auto flex w-full max-w-[1060px] items-center justify-center gap-[72px] max-lg:flex-col max-lg:gap-12">

            <div class="w-full max-w-[360px] flex-none">
                <h2 class="mb-12 text-[2rem] leading-[1.25] font-bold"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-[#1c1c2e]'">
                    <span x-text="$store.ui.t('beacon3TitlePrefix')"></span>
                    - <span class="text-[#219653]" x-text="$store.ui.t('beacon3TitleEmphasis')"></span>
                </h2>

                <div class="flex flex-col">
                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="0">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e0e0e0]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon3Tab1Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon3Tab1Body')"></div>
                        </div>
                    </div>

                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="1">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e0e0e0]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon3Tab2Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon3Tab2Body')"></div>
                        </div>
                    </div>

                    <div class="beacon-tab flex cursor-pointer items-stretch gap-4 py-[18px]" data-tab="2">
                        <div class="relative min-h-10 w-[3px] flex-none overflow-hidden rounded-[3px] bg-[#e0e0e0]">
                            <div
                                class="beacon-tab-line-fill absolute left-0 top-0 h-0 w-full rounded-[3px] bg-[#219653]">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center pl-1">
                            <div class="beacon-tab-title text-[1.05rem] font-semibold text-[#9ca3af]"
                                x-text="$store.ui.t('beacon3Tab3Title')"></div>
                            <div class="beacon-tab-desc mt-0 max-h-0 overflow-hidden text-[0.875rem] leading-[1.55] text-[#6b7280] opacity-0 transition-all duration-300 ease-out"
                                x-text="$store.ui.t('beacon3Tab3Body')"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="relative h-[580px] w-[420px] flex-none overflow-hidden rounded-[22px] bg-[linear-gradient(135deg,#1c1b3a_0%,#3b2a3a_50%,#1c1b3a_100%)] max-lg:h-[520px] max-lg:w-full max-lg:max-w-[420px]">
                <div class="beacon-panel absolute inset-0 flex items-center justify-center opacity-100 transition-all duration-300"
                    id="beacon3Panel0">
                    <img class="beacon-panel-img absolute inset-0 z-10 hidden h-full w-full rounded-[22px] object-contain"
                        src="" alt="Beacon 3 tab 1 preview" />
                    <div class="beacon-panel-mockup">
                        <div
                            class="flex max-h-[520px] w-[310px] flex-col overflow-hidden rounded-[18px] bg-white shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                            <div
                                class="flex items-center justify-between border-b border-[#e5e7eb] bg-[#f9fafb] px-4 py-3">
                                <span class="text-[0.7rem] font-bold tracking-wide text-[#6b7280]">#TK-00847 ·
                                    Billing</span>
                                <span
                                    class="rounded-full bg-[#fef2f2] px-2 py-0.5 text-[0.62rem] font-bold uppercase text-[#dc2626]">Urgent</span>
                            </div>
                            <div class="flex items-center gap-2 border-b border-[#fecaca] bg-[#fef2f2] px-4 py-2">
                                <span class="text-[0.68rem] font-semibold text-[#dc2626]">SLA breach in 18 min - respond
                                    now</span>
                            </div>
                            <div class="flex flex-1 flex-col gap-2 bg-[#f9fafb] px-4 py-3">
                                <div class="text-[0.64rem] font-semibold text-[#6b7280]">Customer · Sarah M.</div>
                                <div
                                    class="rounded-[10px] rounded-bl-[3px] border border-[#e5e7eb] bg-white px-3 py-2 text-[0.75rem] leading-[1.45] text-[#1c1c2e]">
                                    Hi, I was charged twice for my subscription this month and I need this resolved
                                    ASAP.</div>
                            </div>
                            <div class="border-t border-[#e5e7eb] bg-white px-3 py-2.5">
                                <div class="mb-2 text-[0.65rem] font-bold uppercase tracking-wide text-[#219653]">AI
                                    Suggestion</div>
                                <div
                                    class="mb-2 rounded-[8px] border border-[#bbf7d0] bg-[#f0fdf4] px-2.5 py-2 text-[0.73rem] leading-[1.45] text-[#1c1c2e]">
                                    Hi Sarah, I'm sorry for the double charge. I've started a full refund for the
                                    duplicate payment and will confirm once complete.</div>
                                <div class="flex items-center gap-1">
                                    <span class="text-[0.62rem] text-[#6b7280]">Tone:</span>
                                    <span
                                        class="rounded-full bg-[#219653] px-2 py-0.5 text-[0.62rem] font-semibold text-white">Friendly</span>
                                    <span
                                        class="rounded-full border border-[#e5e7eb] px-2 py-0.5 text-[0.62rem] font-semibold text-[#6b7280]">Professional</span>
                                    <span
                                        class="rounded-full border border-[#e5e7eb] px-2 py-0.5 text-[0.62rem] font-semibold text-[#6b7280]">Formal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="beacon-panel absolute inset-0 hidden items-center justify-center opacity-0 transition-all duration-300"
                    id="beacon3Panel1">
                    <img class="beacon-panel-img absolute inset-0 z-10 hidden h-full w-full rounded-[22px] object-contain"
                        src="" alt="Beacon 3 tab 2 preview" />
                    <div class="beacon-panel-mockup">
                        <div
                            class="flex max-h-[520px] w-[310px] flex-col overflow-hidden rounded-[18px] bg-white shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                            <div class="flex items-center justify-between bg-[#219653] px-4 py-3">
                                <span class="text-[0.88rem] font-bold text-white">Ticket Queue</span>
                                <span
                                    class="rounded-full bg-white/20 px-2 py-0.5 text-[0.68rem] font-bold text-white">12
                                    open · 2 breached</span>
                            </div>
                            <div class="flex gap-1 border-b border-[#e5e7eb] bg-[#f9fafb] px-3 py-2">
                                <span
                                    class="rounded-full bg-[#219653] px-2 py-0.5 text-[0.62rem] font-semibold text-white">All</span>
                                <span
                                    class="rounded-full border border-[#e5e7eb] px-2 py-0.5 text-[0.62rem] font-semibold text-[#6b7280]">Urgent</span>
                                <span
                                    class="rounded-full border border-[#e5e7eb] px-2 py-0.5 text-[0.62rem] font-semibold text-[#6b7280]">Unassigned</span>
                            </div>
                            <div class="flex flex-col">
                                <div class="border-b border-[#f3f4f6] px-3 py-2.5">
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <div class="truncate text-[0.75rem] font-semibold text-[#1c1c2e]">Can't access
                                            my account after password reset</div>
                                        <span
                                            class="rounded-full bg-[#fef2f2] px-2 py-0.5 text-[0.62rem] font-bold uppercase text-[#dc2626]">Urgent</span>
                                    </div>
                                    <div class="text-[0.66rem] text-[#6b7280]">Auto-assigned · J. Rivera · Routed to
                                        Auth Specialists</div>
                                </div>
                                <div class="border-b border-[#f3f4f6] px-3 py-2.5">
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <div class="truncate text-[0.75rem] font-semibold text-[#1c1c2e]">Bulk order
                                            discount not applying</div>
                                        <span
                                            class="rounded-full bg-[#fff7ed] px-2 py-0.5 text-[0.62rem] font-bold uppercase text-[#ea580c]">High</span>
                                    </div>
                                    <div class="text-[0.66rem] text-[#6b7280]">Auto-assigned · A. Lee · 42 min left
                                    </div>
                                </div>
                                <div class="px-3 py-2.5">
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <div class="truncate text-[0.75rem] font-semibold text-[#1c1c2e]">How do I
                                            export my data as CSV?</div>
                                        <span
                                            class="rounded-full bg-[#fefce8] px-2 py-0.5 text-[0.62rem] font-bold uppercase text-[#ca8a04]">Medium</span>
                                    </div>
                                    <div class="text-[0.66rem] text-[#6b7280]">Auto-assigned · M. Khan · 3h 20m left
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="beacon-panel absolute inset-0 hidden items-center justify-center opacity-0 transition-all duration-300"
                    id="beacon3Panel2">
                    <img class="beacon-panel-img absolute inset-0 z-10 hidden h-full w-full rounded-[22px] object-contain"
                        src="" alt="Beacon 3 tab 3 preview" />
                    <div class="beacon-panel-mockup">
                        <div
                            class="flex max-h-[520px] w-[300px] flex-col overflow-hidden rounded-[18px] bg-white shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                            <div class="flex items-center gap-2 bg-[#219653] px-4 py-3">
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-[7px] bg-white/25 text-white">
                                    ◎</div>
                                <span class="text-[0.88rem] font-bold text-white">HelpDesk Support</span>
                            </div>
                            <div class="flex flex-col gap-3 px-4 py-4">
                                <div
                                    class="rounded-[10px] border border-[#bbf7d0] bg-[#f0fdf4] px-3 py-2 text-[0.72rem] font-semibold text-[#219653]">
                                    <span x-text="$store.ui.t('homeBeacon3NoAccountNeeded')"></span>
                                </div>
                                <div class="text-[0.68rem] text-[#6b7280]">Ticket #TK-00831 · Refund request - order
                                    #8823</div>
                                <div class="flex flex-col gap-2 text-[0.73rem]">
                                    <div class="text-[#16a34a]">● <span
                                            x-text="$store.ui.t('homeBeacon3StepSubmitted')"></span></div>
                                    <div class="text-[#16a34a]">● <span
                                            x-text="$store.ui.t('homeBeacon3StepAssigned')"></span></div>
                                    <div class="font-semibold text-[#1c1c2e]">◉ <span
                                            x-text="$store.ui.t('homeBeacon3StepInProgress')"></span></div>
                                    <div class="text-[#9ca3af]">○ Resolved</div>
                                </div>
                                <button class="rounded-[9px] bg-[#219653] py-2 text-[0.78rem] font-bold text-white"
                                    x-text="$store.ui.t('homeBeacon3ReplyToAgent')"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="absolute bottom-[22px] right-[22px] z-[5] flex h-[46px] w-[46px] items-center justify-center rounded-full bg-[#219653] shadow-[0_6px_20px_rgba(67,168,71,0.35)]">
                    <svg viewBox="0 0 24 24"
                        class="h-[18px] w-[18px] fill-none stroke-white stroke-[2.5] [stroke-linecap:round] [stroke-linejoin:round]">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
            </div>
        </div>
    </section>



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
                    :class="$store.ui.darkMode ?
                        'border-white/25 bg-white/5 text-white hover:bg-white hover:text-black' :
                        'border-gray-300 bg-gray-200 text-gray-900 hover:bg-gray-900 hover:text-white'"
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
                                    <img src="https://web-assets.zendesk.com/is/image/zendesk/Photo_Testimonial_Liberty?fmt=webp-alpha&qlt=65"
                                        alt="Liberty London" class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Inter,ui-sans-serif,system-ui,sans-serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "With Help desk AI, I'm seeing an exciting opportunity to streamline and be more
                                        efficient. That will allow our team to have more time to work on projects of
                                        importance to the business, be it driving revenue or new sales channels."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">Ian Hunt</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">Director of
                                            CX at Liberty London</span>
                                    </div>
                                    <a href="https://www.libertylondon.com/" target="_blank"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Visit
                                        official website →</a>
                                </div>
                            </div>
                        </article>

                        <article class="min-w-full">
                            <div
                                class="flex flex-col items-start gap-8 md:gap-10 lg:flex-row lg:items-center lg:gap-12">
                                <div class="relative h-75 w-full overflow-hidden rounded-2xl md:h-90 lg:h-105 lg:w-90 lg:shrink-0"
                                    :class="$store.ui.darkMode ? 'bg-white/10' : 'bg-[#d5cfc4]'">
                                    <img src="{{ asset('images/Personnes/young-business-owners-preparing-their-store.jpg') }}"
                                        alt="Khan Academy" class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Inter,ui-sans-serif,system-ui,sans-serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "Help desk has helped us scale our support without scaling our team
                                        proportionally. It's been a game-changer for delivering personalized help to
                                        millions of learners worldwide."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">Kristen DiCerbo</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">Chief
                                            Learning Officer at Khan Academy</span>
                                    </div>
                                    <a href="https://www.khanacademy.org/" target="_blank"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Visit
                                        official website →</a>
                                </div>
                            </div>
                        </article>

                        <article class="min-w-full">
                            <div
                                class="flex flex-col items-start gap-8 md:gap-10 lg:flex-row lg:items-center lg:gap-12">
                                <div class="relative h-75 w-full overflow-hidden rounded-2xl md:h-90 lg:h-105 lg:w-90 lg:shrink-0"
                                    :class="$store.ui.darkMode ? 'bg-white/10' : 'bg-[#d5cfc4]'">
                                    <img src="{{ asset('images/Personnes/enthusiastic-couple-pensioners-sitting-together-home-sofa-using-laptop-talking-video-call-with-family.jpg') }}"
                                        alt="ZeroFox" class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Inter,ui-sans-serif,system-ui,sans-serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "With Help desk, our support team now resolves tickets 40% faster. The
                                        automation
                                        features free us up to focus on high-priority threats that actually need human
                                        judgment and expertise."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">James Foster</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">CEO at
                                            ZeroFox</span>
                                    </div>
                                    <a href="https://www.zerofox.com/" target="_blank"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Visit
                                        official website →</a>
                                </div>
                            </div>
                        </article>

                        <article class="min-w-full">
                            <div
                                class="flex flex-col items-start gap-8 md:gap-10 lg:flex-row lg:items-center lg:gap-12">
                                <div class="relative h-75 w-full overflow-hidden rounded-2xl md:h-90 lg:h-105 lg:w-90 lg:shrink-0"
                                    :class="$store.ui.darkMode ? 'bg-white/10' : 'bg-[#d5cfc4]'">
                                    <img src="{{ asset('images/Personnes/businessman-standing-outside-office-building.jpg') }}"
                                        alt="Thrasio" class="gg-slide-image hidden h-full w-full object-cover" />
                                    <div class="gg-slide-placeholder absolute inset-0 flex items-center justify-center text-sm italic"
                                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-gray-500'">
                                        Add image
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col gap-5">
                                    <p
                                        class="font-[Inter,ui-sans-serif,system-ui,sans-serif] text-xl leading-relaxed sm:text-2xl lg:text-[1.72rem]">
                                        "Help desk allowed Thrasio to unify support across dozens of brands under one
                                        roof. Our CSAT scores jumped significantly within the first quarter of going
                                        live."
                                    </p>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold">Carlos Cashman</span>
                                        <span class="text-sm"
                                            :class="$store.ui.darkMode ? 'text-white/65' : 'text-gray-600'">Co-CEO at
                                            Thrasio</span>
                                    </div>
                                    <a href="https://www.thrasio.com/" target="_blank"
                                        class="w-fit text-sm font-medium underline underline-offset-4 transition hover:opacity-70">Visit
                                        official website →</a>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <button id="ggNextBtn"
                    class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full border text-sm transition md:inline-flex"
                    :class="$store.ui.darkMode ?
                        'border-white/25 bg-white/5 text-white hover:bg-white hover:text-black' :
                        'border-gray-300 bg-gray-200 text-gray-900 hover:bg-gray-900 hover:text-white'"
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
            :class="$store.ui.darkMode ? 'bg-[#219653]/10' : 'bg-[#219653]/55'"></div>
        <div class="pointer-events-none absolute -right-10 bottom-8 h-56 w-56 rounded-full blur-3xl"
            :class="$store.ui.darkMode ? 'bg-cyan-300/10' : 'bg-cyan-200/45'"></div>

        <div
            class="relative z-1 mx-auto flex max-w-275 flex-col items-center justify-center gap-12 rounded-3xl px-6 py-8 md:flex-row md:gap-20 md:px-10 md:py-10">

            {{-- Image --}}
            <div id="supportHeroesVisual" class="w-full max-w-[720px]">
                <img src="{{ asset('images/Personnes/Team_photoo.png') }}" alt="Support hero" class="w-full h-auto" />
            </div>

            {{-- Text --}}
            <div id="supportHeroesContent" class="max-w-115 text-center md:text-left">
                <span
                    class="mb-4 inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.2em]"
                    :class="$store.ui.darkMode ? 'border-white/15 bg-white/5 text-white/70' :
                        'border-[#219653]/20 bg-[#219653]/10 text-[#219653]'">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#219653]"></span>
                    SUPPORT HEROES
                </span>
                <h2 class="mb-5 text-4xl font-bold leading-[1.08] tracking-tight sm:text-[2.95rem]"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-gray-900'"
                    style="font-family: 'Montserrat', 'DM Sans', ui-sans-serif, system-ui, sans-serif !important;">
                    <span x-text="$store.ui.t('heroesTitle1')"></span><br>
                    <span x-text="$store.ui.t('heroesTitle2')"></span>
                </h2>
                <p class="mb-8 font-[DM_Sans,ui-sans-serif,system-ui,sans-serif] text-[1.05rem] leading-relaxed"
                    :class="$store.ui.darkMode ? 'text-gray-400' : 'text-gray-600'"
                    x-text="$store.ui.t('heroesDescription')"></p>
                <button type="button" id="supportHeroesChatBtn"
                    class="inline-flex items-center gap-2 rounded-xl px-8 py-3.5 text-base font-bold text-white shadow-[0_16px_30px_-16px_rgba(34,197,94,0.75)] transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.01]"
                    :class="$store.ui.darkMode ?
                        'bg-[#219653] hover:bg-[#1b7a44]' :
                        'bg-[#219653] hover:bg-[#1b7a44]'">
                    <span x-text="$store.ui.t('heroesCta')"></span>
                    <span aria-hidden="true">→</span>
                </button>
            </div>

        </div>
    </section>



    {{-- ═══════════════════════════════════════════════════════════════════
    GET STARTED — Animated hero block from gg.html
    ═══════════════════════════════════════════════════════════════════ --}}
    <section id="getStartedHeroSection"
        class="relative isolate overflow-hidden px-6 pb-32 pt-10 opacity-0 translate-y-8 blur-sm transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="$store.ui.darkMode ? 'bg-[#0a101c]' : 'bg-[#fdf0e8]'">
        <div class="absolute inset-0 z-0"
            :class="$store.ui.darkMode ? 'bg-[radial-gradient(ellipse_70%_60%_at_20%_40%,#1d3358_0%,transparent_55%),radial-gradient(ellipse_60%_70%_at_80%_30%,#102840_0%,transparent_55%),radial-gradient(ellipse_80%_80%_at_50%_80%,#1f2937_0%,transparent_60%),linear-gradient(135deg,#08111f_0%,#0f1f35_50%,#162a43_100%)]' : 'bg-[radial-gradient(ellipse_70%_60%_at_20%_40%,#d8a8cc_0%,transparent_55%),radial-gradient(ellipse_60%_70%_at_80%_30%,#f0b090_0%,transparent_55%),radial-gradient(ellipse_80%_80%_at_50%_80%,#f5c898_0%,transparent_60%),linear-gradient(135deg,#dbaac8_0%,#f0b888_50%,#f5a87a_100%)]'">
        </div>

        <div class="absolute right-0 top-0 z-1 h-65 w-65 bg-size-[18px_18px] mask-[radial-gradient(ellipse_80%_80%_at_90%_10%,black_40%,transparent_80%)]"
            :class="$store.ui.darkMode ? 'bg-[radial-gradient(circle,rgba(112,170,255,0.16)_1.5px,transparent_1.5px)]' : 'bg-[radial-gradient(circle,rgba(180,100,80,0.25)_1.5px,transparent_1.5px)]'">
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
                    class="block translate-y-15 font-[Barlow_Condensed,sans-serif] text-[clamp(72px,12vw,148px)] font-black uppercase leading-[0.92] tracking-[-1px] opacity-0 transition-[transform,opacity] duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-[#1a1020]'" data-get-started-reveal
                    x-text="$store.ui.t('homeGetStartedHeading')"></span>
            </div>

            <p class="mb-10 translate-y-15 text-[clamp(16px,2vw,22px)] leading-[1.65] opacity-0 transition-[transform,opacity] duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] delay-250"
                :class="$store.ui.darkMode ? 'text-white/85' : 'text-[#3a1e2e]'" data-get-started-reveal>
                <span x-text="$store.ui.t('homeGetStartedLine1')"></span><br>
                <span x-text="$store.ui.t('homeGetStartedLine2Prefix')"></span>
                <em class="font-[Lora,serif] italic font-normal"
                    x-text="$store.ui.t('homeGetStartedLine2Emphasis')"></em>
                <span x-text="$store.ui.t('homeGetStartedLine2Suffix')"></span>
            </p>

            <div class="flex translate-y-15 flex-wrap justify-center gap-3.5 opacity-0 transition-[transform,opacity] duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] delay-400"
                data-get-started-reveal>
                <a href="#"
                    class="rounded-lg bg-[#219653] px-8.5 py-3.75 text-base font-semibold text-white shadow-[0_6px_20px_-4px_rgba(45,157,90,0.55)] transition hover:-translate-y-0.5 hover:shadow-[0_10px_28px_-4px_rgba(255,255,255,0.65)] active:translate-y-0">
                    <span x-text="$store.ui.t('homeGetStartedPrimaryCta')"></span>
                </a>
                <a href="#"
                    class="rounded-lg border-[1.5px] border-white/90 bg-white/80 px-8.5 py-3.75 text-base font-semibold text-[#1a1020] shadow-[0_4px_14px_-4px_rgba(0,0,0,0.15)] backdrop-blur-[6px] transition hover:-translate-y-0.5 hover:bg-white active:translate-y-0">
                    <span x-text="$store.ui.t('homeGetStartedSecondaryCta')"></span>
                </a>
            </div>

            <div class="absolute -bottom-33 left-1/2 z-3 w-[min(560px,90vw)] -translate-x-1/2 translate-y-36 opacity-0 transition-[transform,opacity] duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] delay-600 md:left-[57%] lg:left-[60%]"
                data-get-started-reveal>
                <div
                    class="flex items-center gap-3 rounded-t-2xl bg-white px-6 py-4.5 shadow-[0_-8px_40px_rgba(0,0,0,0.12)]">
                    <span class="flex-1 text-[15px] font-medium text-[#333]">
                        App help?
                        <span
                            class="ml-1 inline-block rounded bg-[#e8f5e9] px-2 py-0.75 text-[11px] font-semibold text-[#219653]">vip</span>
                        <span
                            class="ml-1 inline-block rounded bg-[#fce4ec] px-2 py-0.75 text-[11px] font-semibold text-[#c62828]">sales-lead</span>
                    </span>
                    <div class="flex items-center">
                        <div
                            class="-ml-2.5 flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-[2.5px] border-[#f3e8ff] bg-linear-to-br from-[#a78bfa] to-[#7c3aed] text-lg first:ml-0">
                            <img src="{{ asset('images/Personnes/walid_photo.jpeg') }}" alt="Support agent"
                                class="h-full w-full rounded-full object-cover" />
                        </div>
                        <div
                            class="gs-younes-wiggle-red -ml-2.5 flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border-[2.5px] border-[#fff0ec] bg-linear-to-br from-[#fb923c] to-[#dc2626] text-lg first:ml-0">
                            <img src="{{ asset('images/Personnes/Younes_Photo.jpg') }}" alt="Support agent"
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
                const sfsViewport = sfsTrack.closest('.sfs-slider-outer');
                const sfsOriginalCards = Array.from(sfsTrack.querySelectorAll('.sfs-card'));
                const sfsTotal = sfsOriginalCards.length;
                const sfsAutoIntervalMs = 5000;
                const sfsTransitionValue = 'transform 0.65s cubic-bezier(0.65, 0, 0.35, 1)';
                let sfsCurrent = sfsTotal > 1 ? sfsTotal : 0;
                let sfsAutoTimer;
                let sfsIsTransitioning = false;
                let sfsTouchStartX = 0;

                if (sfsTotal > 1) {
                    const sfsAppendClones = sfsOriginalCards.map((card, index) => {
                        const clone = card.cloneNode(true);
                        clone.setAttribute('data-sfs-clone', 'append');
                        clone.setAttribute('data-sfs-origin-index', String(index));
                        return clone;
                    });

                    const sfsPrependClones = sfsOriginalCards.map((card, index) => {
                        const clone = card.cloneNode(true);
                        clone.setAttribute('data-sfs-clone', 'prepend');
                        clone.setAttribute('data-sfs-origin-index', String(index));
                        return clone;
                    });

                    sfsAppendClones.forEach((clone) => {
                        sfsTrack.appendChild(clone);
                    });

                    for (let i = sfsPrependClones.length - 1; i >= 0; i -= 1) {
                        sfsTrack.insertBefore(sfsPrependClones[i], sfsTrack.firstChild);
                    }

                    sfsTrack.style.width = 'max-content';
                }

                function sfsCards() {
                    return Array.from(sfsTrack.querySelectorAll('.sfs-card'));
                }

                function sfsClampIndex(index) {
                    const cards = sfsCards();
                    const maxIndex = Math.max(0, cards.length - 1);
                    return Math.max(0, Math.min(index, maxIndex));
                }

                function sfsTranslateToCurrent() {
                    const cards = sfsCards();
                    sfsCurrent = sfsClampIndex(sfsCurrent);
                    const target = cards[sfsCurrent] || cards[0];
                    const offset = target ? target.offsetLeft : 0;

                    console.log('index:', sfsCurrent);
                    console.log('offset:', offset);
                    console.log('total cards:', cards.length);

                    sfsTrack.style.transform = `translateX(-${offset}px)`;
                }

                function sfsJumpTo(index) {
                    sfsTrack.style.transition = 'none';
                    sfsCurrent = sfsClampIndex(index);
                    sfsTranslateToCurrent();

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            sfsTrack.style.transition = sfsTransitionValue;
                            console.log('[SFS] current index:', sfsCurrent);
                        });
                    });
                }

                function sfsMoveBy(step) {
                    if (sfsIsTransitioning || sfsTotal < 2) {
                        return;
                    }

                    sfsIsTransitioning = true;
                    sfsCurrent += step;
                    sfsCurrent = sfsClampIndex(sfsCurrent);
                    sfsTrack.style.transition = sfsTransitionValue;
                    sfsTranslateToCurrent();
                    console.log('[SFS] current index:', sfsCurrent);
                }

                function sfsStartAuto() {
                    if (sfsTotal < 2) {
                        return;
                    }

                    clearInterval(sfsAutoTimer);
                    sfsAutoTimer = setInterval(() => {
                        if (!document.hidden) {
                            sfsMoveBy(1);
                        }
                    }, sfsAutoIntervalMs);
                }

                function sfsStopAuto() {
                    clearInterval(sfsAutoTimer);
                }

                function sfsResetAuto() {
                    sfsStopAuto();
                    sfsStartAuto();
                }

                sfsTrack.addEventListener('transitionend', (event) => {
                    if (event.target !== sfsTrack || event.propertyName !== 'transform') {
                        return;
                    }

                    if (sfsCurrent >= sfsTotal * 2) {
                        console.log('[SFS] reset: appended set -> real set');
                        sfsJumpTo(sfsCurrent - sfsTotal);
                        sfsIsTransitioning = false;
                    } else if (sfsCurrent < sfsTotal) {
                        console.log('[SFS] reset: prepended set -> real set');
                        sfsJumpTo(sfsCurrent + sfsTotal);
                        sfsIsTransitioning = false;
                    } else {
                        sfsIsTransitioning = false;
                    }
                });

                sfsPrevBtn.addEventListener('click', () => {
                    sfsMoveBy(-1);
                    sfsResetAuto();
                });

                sfsNextBtn.addEventListener('click', () => {
                    sfsMoveBy(1);
                    sfsResetAuto();
                });

                const sfsSection = document.getElementById('sfsSection');
                const sfsHoverTarget = sfsSection || sfsViewport;

                if (sfsHoverTarget) {
                    sfsHoverTarget.addEventListener('mouseenter', sfsStopAuto);
                    sfsHoverTarget.addEventListener('mouseleave', sfsStartAuto);
                }

                if (sfsViewport) {
                    sfsViewport.addEventListener('touchstart', (event) => {
                        sfsTouchStartX = event.touches[0].clientX;
                        sfsStopAuto();
                    }, {
                        passive: true
                    });

                    sfsViewport.addEventListener('touchend', (event) => {
                        const deltaX = event.changedTouches[0].clientX - sfsTouchStartX;
                        if (Math.abs(deltaX) > 40) {
                            sfsMoveBy(deltaX < 0 ? 1 : -1);
                        }
                        sfsStartAuto();
                    }, {
                        passive: true
                    });
                }

                window.addEventListener('resize', () => {
                    sfsJumpTo(sfsCurrent);
                });

                sfsTrack.style.transition = sfsTransitionValue;
                sfsJumpTo(sfsTotal > 1 ? sfsTotal : 0);
                sfsStartAuto();

                /* ═══════════════════════════
                   C3 – Workflows rows animation
                ═══════════════════════════ */
                const sfsWfRows = ['sfsWfR1', 'sfsWfR2', 'sfsWfR3'].map(id => document.getElementById(id));

                function sfsAnimateWF() {
                    sfsWfRows.forEach(r => {
                        if (r) r.classList.remove('vis');
                    });
                    sfsWfRows.forEach((r, i) => {
                        if (r) setTimeout(() => r.classList.add('vis'), 350 + i * 520);
                    });
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
                    const newChip = {
                        label: '🟠 Refund',
                        bg: '#ffedd5',
                        color: '#9a3412'
                    };

                    // blink cursor
                    let sfsCursorVisible = true;
                    setInterval(() => {
                        sfsCursorVisible = !sfsCursorVisible;
                        sfsTagCursorEl.style.opacity = sfsCursorVisible ? '1' : '0';
                    }, 530);

                    function sfsWait(ms) {
                        return new Promise(r => setTimeout(r, ms));
                    }

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
                }, {
                    passive: true
                });

                ggViewport.addEventListener('touchend', (event) => {
                    const deltaX = event.changedTouches[0].clientX - ggTouchStartX;
                    if (Math.abs(deltaX) > 40) {
                        ggGoTo(deltaX < 0 ? ggCurrent + 1 : ggCurrent - 1);
                    }
                }, {
                    passive: true
                });

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
               Beacon tabs (embeddable support hub)
            ═══════════════════════════ */
            const initBeaconTabs = (wrapperId, panelIds) => {
                const beaconWrapper = document.getElementById(wrapperId);

                if (!beaconWrapper) {
                    return;
                }

                const BEACON_DURATION = 4000;
                const beaconTabs = Array.from(beaconWrapper.querySelectorAll('.beacon-tab'));
                const beaconPanels = panelIds.map((panelId) => document.getElementById(panelId)).filter(Boolean);
                const beaconPanelImages = Array.from(beaconWrapper.querySelectorAll('.beacon-panel-img'));

                if (beaconTabs.length === 0 || beaconPanels.length === 0) {
                    return;
                }

                const beaconApplyImgSwap = () => {
                    beaconPanelImages.forEach((image) => {
                        const mockup = image.nextElementSibling;
                        const hasImagePath = image.getAttribute('src')?.trim();

                        if (hasImagePath) {
                            image.classList.remove('hidden');
                            mockup?.classList.add('hidden');
                        } else {
                            image.classList.add('hidden');
                            mockup?.classList.remove('hidden');
                        }
                    });
                };

                beaconApplyImgSwap();

                let beaconCurrent = 0;
                let beaconPaused = false;
                let beaconElapsed = 0;
                let beaconLastTs = null;
                let beaconRafId = null;

                const beaconIsDarkMode = () => {
                    const alpineStore = window.Alpine?.store?.('ui');

                    if (alpineStore && typeof alpineStore.darkMode === 'boolean') {
                        return alpineStore.darkMode;
                    }

                    return document.body.classList.contains('bg-black');
                };

                const beaconSetActiveFillProgress = () => {
                    const progress = Math.max(0, Math.min(1, beaconElapsed / BEACON_DURATION));
                    const activeTab = beaconTabs[beaconCurrent];
                    const fill = activeTab?.querySelector('.beacon-tab-line-fill');

                    if (fill) {
                        fill.style.height = `${progress * 100}%`;
                    }
                };

                const beaconActivateTab = (index) => {
                    beaconCurrent = index;
                    beaconElapsed = 0;
                    beaconLastTs = beaconPaused ? null : performance.now();
                    const isDarkMode = beaconIsDarkMode();

                    beaconTabs.forEach((tab, tabIndex) => {
                        const isActive = tabIndex === beaconCurrent;
                        const title = tab.querySelector('.beacon-tab-title');
                        const desc = tab.querySelector('.beacon-tab-desc');
                        const fill = tab.querySelector('.beacon-tab-line-fill');

                        if (title) {
                            title.classList.toggle('text-white', isActive && isDarkMode);
                            title.classList.toggle('text-[#1c1c2e]', isActive && !isDarkMode);
                            title.classList.toggle('text-[#9ca3af]', !isActive);
                        }

                        if (desc) {
                            desc.classList.toggle('max-h-20', isActive);
                            desc.classList.toggle('opacity-100', isActive);
                            desc.classList.toggle('mt-[7px]', isActive);

                            desc.classList.toggle('max-h-0', !isActive);
                            desc.classList.toggle('opacity-0', !isActive);
                            desc.classList.toggle('mt-0', !isActive);
                        }

                        if (fill) {
                            fill.style.height = '0%';
                        }
                    });

                    beaconPanels.forEach((panel, panelIndex) => {
                        const isActive = panelIndex === beaconCurrent;
                        panel.classList.toggle('hidden', !isActive);
                        panel.classList.toggle('flex', isActive);
                        panel.classList.toggle('opacity-0', !isActive);
                        panel.classList.toggle('opacity-100', isActive);
                    });

                    beaconSetActiveFillProgress();
                };

                const beaconTick = (ts) => {
                    if (beaconPaused) {
                        return;
                    }

                    if (beaconLastTs !== null) {
                        beaconElapsed += ts - beaconLastTs;
                    }

                    beaconLastTs = ts;

                    if (beaconElapsed >= BEACON_DURATION) {
                        beaconActivateTab((beaconCurrent + 1) % beaconTabs.length);
                    } else {
                        beaconSetActiveFillProgress();
                    }

                    beaconRafId = requestAnimationFrame(beaconTick);
                };

                const beaconPauseAuto = () => {
                    beaconPaused = true;

                    if (beaconRafId) {
                        cancelAnimationFrame(beaconRafId);
                    }

                    beaconRafId = null;
                    beaconLastTs = null;
                };

                const beaconResumeAuto = () => {
                    beaconPaused = false;
                    beaconLastTs = performance.now();
                    beaconRafId = requestAnimationFrame(beaconTick);
                };

                beaconTabs.forEach((tab, tabIndex) => {
                    tab.addEventListener('mouseenter', beaconPauseAuto);
                    tab.addEventListener('mouseleave', beaconResumeAuto);

                    tab.addEventListener('click', () => {
                        if (beaconRafId) {
                            cancelAnimationFrame(beaconRafId);
                        }

                        beaconActivateTab(tabIndex);

                        if (!beaconPaused) {
                            beaconLastTs = performance.now();
                            beaconRafId = requestAnimationFrame(beaconTick);
                        }
                    });
                });

                beaconActivateTab(0);
                beaconRafId = requestAnimationFrame(beaconTick);
            };

            initBeaconTabs('beaconWrapper', ['beaconPanel0', 'beaconPanel1', 'beaconPanel2']);
            initBeaconTabs('beaconWrapper2', ['beacon2Panel0', 'beacon2Panel1', 'beacon2Panel2']);
            initBeaconTabs('beaconWrapper3', ['beacon3Panel0', 'beacon3Panel1', 'beacon3Panel2']);

            const beaconSections = [
                document.getElementById('beaconSection1'),
                document.getElementById('beaconSection2'),
                document.getElementById('beaconSection3'),
            ].filter(Boolean);

            const showBeaconSection = (section) => {
                section.classList.remove('opacity-0', 'translate-y-8', 'blur-sm');
                section.classList.add('opacity-100', 'translate-y-0', 'blur-0');
            };

            if (!('IntersectionObserver' in window)) {
                beaconSections.forEach(showBeaconSection);
            } else {
                const beaconSectionObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            showBeaconSection(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.18,
                    rootMargin: '0px 0px -6% 0px',
                });

                beaconSections.forEach((section) => {
                    beaconSectionObserver.observe(section);
                });
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
                        const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / (window
                            .innerHeight + rect.height)));
                        const visualShift = (0.5 - progress) * 20;
                        const contentShift = (0.5 - progress) * 12;

                        if (supportHeroesVisual) {
                            supportHeroesVisual.style.transform = `translateY(${visualShift}px)`;
                        }

                        if (supportHeroesContent) {
                            supportHeroesContent.style.transform = `translateY(${contentShift}px)`;
                        }
                    };

                    window.addEventListener('scroll', onSupportHeroesScroll, {
                        passive: true
                    });
                    onSupportHeroesScroll();
                }
            }

            if (supportHeroesChatBtn) {
                supportHeroesChatBtn.addEventListener('click', () => {
                    const chatbotWidget = document.getElementById('chatbot-widget');
                    chatbotWidget?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'end'
                    });
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
                        const progress = Math.max(0, Math.min(1, (window.innerHeight - rect.top) / (window
                            .innerHeight + rect.height)));
                        const translateY = (0.5 - progress) * 14;
                        discoverVisualPanel.style.transform = `translateY(${translateY}px)`;
                    };

                    window.addEventListener('scroll', onDiscoverScroll, {
                        passive: true
                    });
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
                        item.classList.remove('opacity-0', 'translate-y-15', 'translate-y-24',
                            'translate-y-36');
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
                    }, {
                        threshold: 0.2
                    });

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

                window.addEventListener('scroll', onGetStartedScroll, {
                    passive: true
                });
            }
        });
    </script>
</body>

</html>