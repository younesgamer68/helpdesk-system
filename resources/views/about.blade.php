<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>About Us - Helpdesk System</title>

    <link rel="icon" href="{{ asset('images/Logos/logos without text DM.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/Logos/logos without text DM.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|righteous:400" rel="stylesheet" />

    @vite(['resources/css/welcome.css'])

    <x-ui-state />
</head>

<body x-data
    class="flex min-h-screen flex-col bg-white font-[Instrument_Sans,ui-sans-serif,system-ui,sans-serif] text-[#17494D] antialiased transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-black text-white' : 'bg-white text-[#17494D]'">

    <x-nav-bar />
    <x-loading-overlay />

    <main class="flex-1">
        <section class="relative overflow-hidden px-6 pb-16 pt-12 sm:pt-16"
            :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">
            <div class="particles-container" id="about-hero-particles"></div>
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_14%_0%,rgba(94,219,86,0.20),transparent_40%),radial-gradient(circle_at_85%_100%,rgba(33,150,83,0.30),transparent_40%),radial-gradient(circle_at_55%_45%,rgba(125,255,175,0.12),transparent_42%)]'
                    : 'bg-[radial-gradient(circle_at_14%_0%,rgba(94,219,86,0.2),transparent_40%),radial-gradient(circle_at_85%_100%,rgba(33,150,83,0.1),transparent_40%)]'">
            </div>

            <div class="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <p id="about-typewriter" class="text-xs font-semibold uppercase tracking-[0.24em]"
                        :class="$store.ui.darkMode ? 'text-white/55' : 'text-[#2f6d71]'"></p>
                    <h1 class="mt-3 max-w-3xl text-4xl font-semibold leading-tight sm:text-5xl"
                        :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                        x-text="$store.ui.t('aboutHeroTitle')">
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-relaxed sm:text-lg"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4d757a]'"
                        x-text="$store.ui.t('aboutHeroDescription')">
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('contact') }}"
                            class="inline-flex items-center justify-center rounded-full bg-[#219653] px-6 py-3 text-sm font-semibold text-white transition"
                            x-text="$store.ui.t('aboutHeroCtaPrimary')">
                        </a>
                        <a href="{{ route('help-center') }}"
                            class="inline-flex items-center justify-center rounded-full border px-6 py-3 text-sm font-semibold transition"
                            :class="$store.ui.darkMode ? 'border-white/25 text-white' : 'border-[#9cd0d2] text-[#17494D]'"
                            x-text="$store.ui.t('aboutHeroCtaSecondary')">
                        </a>
                    </div>
                </div>

                <div class="scroll-reveal scroll-reveal-delay-2 rounded-3xl border p-3"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d3eaeb] bg-[#f7ffff]'">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80"
                        alt="Support team collaborating at modern office"
                        class="img-parallax h-[360px] w-full rounded-2xl object-cover" loading="lazy" />
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden px-6 pb-18" :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_10%_22%,rgba(80,220,120,0.12),transparent_36%),radial-gradient(circle_at_82%_76%,rgba(55,191,104,0.16),transparent_40%)]'
                    : 'bg-[radial-gradient(circle_at_10%_22%,rgba(80,220,120,0.07),transparent_36%),radial-gradient(circle_at_82%_76%,rgba(55,191,104,0.08),transparent_40%)]'">
            </div>
            <div class="mx-auto max-w-7xl">
                <div class="scroll-stagger grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-2xl border p-6"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d8eced] bg-[#fbffff]'">
                        <p class="text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('aboutPillar1Title')"></p>
                        <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                            x-text="$store.ui.t('aboutPillar1Body')"></p>
                    </article>
                    <article class="rounded-2xl border p-6"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d8eced] bg-[#fbffff]'">
                        <p class="text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('aboutPillar2Title')"></p>
                        <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                            x-text="$store.ui.t('aboutPillar2Body')"></p>
                    </article>
                    <article class="rounded-2xl border p-6"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d8eced] bg-[#fbffff]'">
                        <p class="text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('aboutPillar3Title')"></p>
                        <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                            x-text="$store.ui.t('aboutPillar3Body')"></p>
                    </article>
                    <article class="rounded-2xl border p-6"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d8eced] bg-[#fbffff]'">
                        <p class="text-lg font-bold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('aboutPillar4Title')"></p>
                        <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                            x-text="$store.ui.t('aboutPillar4Body')"></p>
                    </article>
                </div>

                <div class="scroll-reveal mt-14 grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div>
                        <h2 class="text-3xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('aboutStoryTitle')"></h2>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4e767a]'"
                            x-text="$store.ui.t('aboutStoryBody1')">
                        </p>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4e767a]'"
                            x-text="$store.ui.t('aboutStoryBody2')">
                        </p>
                    </div>
                    <div class="scroll-stagger grid grid-cols-2 gap-4">
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=80"
                            alt="City office building where support technology teams operate"
                            class="h-44 w-full rounded-2xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=900&q=80"
                            alt="Product planning workspace with laptop and notes"
                            class="h-44 w-full rounded-2xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=900&q=80"
                            alt="Customer success team discussing support strategy"
                            class="h-44 w-full rounded-2xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80"
                            alt="Agents collaborating in open office" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                    </div>
                </div>

                <section class="scroll-reveal mt-14 rounded-3xl border p-6 sm:p-8"
                    :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                    <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                        x-text="$store.ui.t('aboutDifferenceTitle')"></h2>
                    <div class="scroll-stagger mt-6 grid gap-5 md:grid-cols-2">
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d6ecee] bg-white'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutDifference1Title')"></h3>
                            <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#557d81]'"
                                x-text="$store.ui.t('aboutDifference1Body')"></p>
                        </article>
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d6ecee] bg-white'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutDifference2Title')"></h3>
                            <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#557d81]'"
                                x-text="$store.ui.t('aboutDifference2Body')"></p>
                        </article>
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d6ecee] bg-white'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutDifference3Title')"></h3>
                            <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#557d81]'"
                                x-text="$store.ui.t('aboutDifference3Body')"></p>
                        </article>
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d6ecee] bg-white'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutDifference4Title')"></h3>
                            <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#557d81]'"
                                x-text="$store.ui.t('aboutDifference4Body')"></p>
                        </article>
                    </div>
                </section>

                <section class="scroll-reveal mt-14">
                    <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                        x-text="$store.ui.t('aboutGrowthTitle')"></h2>
                    <div class="scroll-stagger mt-6 space-y-4">
                        <article class="rounded-2xl border p-6"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d8eced] bg-[#fbffff]'">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em]"
                                :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#5d8a8d]'"
                                x-text="$store.ui.t('aboutGrowthPhase1Label')"></p>
                            <h3 class="mt-2 text-lg font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutGrowthPhase1Title')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#547c80]'"
                                x-text="$store.ui.t('aboutGrowthPhase1Body')"></p>
                        </article>
                        <article class="rounded-2xl border p-6"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d8eced] bg-[#fbffff]'">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em]"
                                :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#5d8a8d]'"
                                x-text="$store.ui.t('aboutGrowthPhase2Label')"></p>
                            <h3 class="mt-2 text-lg font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutGrowthPhase2Title')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#547c80]'"
                                x-text="$store.ui.t('aboutGrowthPhase2Body')"></p>
                        </article>
                        <article class="rounded-2xl border p-6"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d8eced] bg-[#fbffff]'">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em]"
                                :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#5d8a8d]'"
                                x-text="$store.ui.t('aboutGrowthPhase3Label')"></p>
                            <h3 class="mt-2 text-lg font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutGrowthPhase3Title')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#547c80]'"
                                x-text="$store.ui.t('aboutGrowthPhase3Body')"></p>
                        </article>
                    </div>
                </section>

                <section class="scroll-reveal mt-14 grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <div class="rounded-3xl border p-6 sm:p-8"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                        <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('aboutNoteTitle')"></h2>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f787b]'"
                            x-text="$store.ui.t('aboutNoteBody1')">
                        </p>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f787b]'"
                            x-text="$store.ui.t('aboutNoteBody2')">
                        </p>
                    </div>
                    <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1400&q=80"
                        alt="Global support operations team in meeting room"
                        class="h-[320px] w-full rounded-3xl object-cover" loading="lazy" />
                </section>

                <section class="scroll-reveal mt-16 grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
                    <article class="rounded-3xl border p-6 sm:p-8"
                        :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-white'">
                        <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('aboutVisionTitle')">
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f777b]'"
                            x-text="$store.ui.t('aboutVisionBody1')">
                        </p>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f777b]'"
                            x-text="$store.ui.t('aboutVisionBody2')">
                        </p>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f777b]'"
                            x-text="$store.ui.t('aboutVisionBody3')">
                        </p>
                    </article>

                    <div class="scroll-stagger space-y-4">
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=1200&q=80"
                            alt="Leadership team discussing support operations"
                            class="h-48 w-full rounded-3xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1573497620053-ea5300f94f21?auto=format&fit=crop&w=1200&q=80"
                            alt="Support manager planning performance targets"
                            class="h-48 w-full rounded-3xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1200&q=80"
                            alt="Agent workspace with customer conversation dashboard"
                            class="h-48 w-full rounded-3xl object-cover" loading="lazy" />
                    </div>
                </section>

                <section class="scroll-reveal mt-16 rounded-3xl border p-6 sm:p-8"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                    <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                        x-text="$store.ui.t('aboutInfrastructureTitle')">
                    </h2>
                    <div class="scroll-stagger mt-6 grid gap-5 md:grid-cols-3">
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-white/10 bg-black/20' : 'border-[#d7edef] bg-white'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutInfrastructure1Title')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#557d81]'"
                                x-text="$store.ui.t('aboutInfrastructure1Body')">
                            </p>
                        </article>
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-white/10 bg-black/20' : 'border-[#d7edef] bg-white'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutInfrastructure2Title')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#557d81]'"
                                x-text="$store.ui.t('aboutInfrastructure2Body')">
                            </p>
                        </article>
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-white/10 bg-black/20' : 'border-[#d7edef] bg-white'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('aboutInfrastructure3Title')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#557d81]'"
                                x-text="$store.ui.t('aboutInfrastructure3Body')">
                            </p>
                        </article>
                    </div>
                </section>

                <section class="scroll-reveal mt-16">
                    <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                        x-text="$store.ui.t('aboutCultureTitle')"></h2>
                    <p class="mt-3 max-w-4xl text-sm leading-relaxed sm:text-base"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f787b]'"
                        x-text="$store.ui.t('aboutCultureBody')">
                    </p>
                    <div class="scroll-stagger mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80"
                            alt="Team collaboration in project room" class="h-56 w-full rounded-2xl object-cover"
                            loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=900&q=80"
                            alt="Modern office hallway and collaboration space"
                            class="h-56 w-full rounded-2xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=900&q=80"
                            alt="Cross-functional support and product planning meeting"
                            class="h-56 w-full rounded-2xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=900&q=80"
                            alt="Customer success floor with active support desks"
                            class="h-56 w-full rounded-2xl object-cover" loading="lazy" />
                    </div>
                </section>

                <section class="scroll-reveal mt-16 rounded-3xl border p-6 sm:p-8"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-white'">
                    <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                        x-text="$store.ui.t('aboutCommitmentTitle')"></h2>
                    <div class="mt-4 space-y-4 text-sm leading-relaxed sm:text-base"
                        :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f787b]'">
                        <p x-text="$store.ui.t('aboutCommitmentBody1')"></p>
                        <p x-text="$store.ui.t('aboutCommitmentBody2')"></p>
                        <p x-text="$store.ui.t('aboutCommitmentBody3')"></p>
                    </div>
                </section>
            </div>
        </section>
    </main>

    <x-footer />
    <livewire:ai-chat-widget />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* ── Scroll-reveal via IntersectionObserver ── */
            const revealElements = document.querySelectorAll('.scroll-reveal, .scroll-stagger');
            const revealObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            revealElements.forEach(function (el) { revealObserver.observe(el); });

            /* ── Typewriter on hero label ── */
            var typeTarget = document.getElementById('about-typewriter');
            if (typeTarget) {
                var text = Alpine.store('ui').t('aboutTypewriter');
                var i = 0;
                typeTarget.classList.add('typing-cursor');
                function typeChar() {
                    if (i < text.length) {
                        typeTarget.textContent += text.charAt(i);
                        i++;
                        setTimeout(typeChar, 90);
                    } else {
                        typeTarget.classList.remove('typing-cursor');
                    }
                }
                typeChar();
            }

            /* ── Floating particles in hero ── */
            var particleBox = document.getElementById('about-hero-particles');
            if (particleBox) {
                for (var p = 0; p < 18; p++) {
                    var dot = document.createElement('span');
                    dot.className = 'particle';
                    var size = Math.random() * 6 + 3;
                    dot.style.width = size + 'px';
                    dot.style.height = size + 'px';
                    dot.style.left = Math.random() * 100 + '%';
                    dot.style.top = Math.random() * 100 + '%';
                    dot.style.setProperty('--dur', (Math.random() * 6 + 4) + 's');
                    dot.style.setProperty('--delay', (Math.random() * 5) + 's');
                    particleBox.appendChild(dot);
                }
            }

            /* ── Parallax on images ── */
            var parallaxImages = document.querySelectorAll('.img-parallax');
            if (parallaxImages.length) {
                window.addEventListener('scroll', function () {
                    var scrollY = window.scrollY;
                    parallaxImages.forEach(function (img) {
                        var rect = img.getBoundingClientRect();
                        var offset = (rect.top - window.innerHeight / 2) * 0.04;
                        img.style.transform = 'translateY(' + offset + 'px)';
                    });
                }, { passive: true });
            }
        });
    </script>
</body>

</html>