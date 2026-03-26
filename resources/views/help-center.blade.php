<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Help Center - Helpdesk System</title>

    <link rel="icon" href="{{ asset('images/Logos/logos without text DM.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/Logos/logos without text DM.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|righteous:400" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Montserrat:wght@400;500;600;700&family=Raleway:wght@400;500;600&family=Poppins:wght@600;700&family=Sora:wght@600;700&family=DM+Sans:wght@500;700&family=Inter:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/welcome.css'])

    <x-ui-state />
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.8/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>

<body x-data
    class="flex min-h-screen flex-col bg-white font-[Instrument_Sans,ui-sans-serif,system-ui,sans-serif] text-[#17494D] antialiased transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-black text-white' : 'bg-white text-[#17494D]'">

    <x-nav-bar />
    <x-loading-overlay />

    <main class="flex-1">
        <section class="relative overflow-hidden px-6 pb-14 pt-10 sm:pt-14"
            :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">
            <div class="particles-container" id="help-hero-particles"></div>
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_8%_0%,rgba(94,219,86,0.20),transparent_36%),radial-gradient(circle_at_85%_90%,rgba(36,150,83,0.30),transparent_44%),radial-gradient(circle_at_55%_40%,rgba(110,255,160,0.14),transparent_40%)]'
                    : 'bg-[radial-gradient(circle_at_10%_0%,rgba(94,219,86,0.18),transparent_36%),radial-gradient(circle_at_90%_85%,rgba(36,150,83,0.10),transparent_44%)]'">
            </div>

            <div class="relative mx-auto max-w-6xl">
                <p id="help-typewriter" class="text-xs font-semibold uppercase tracking-[0.22em]"
                    :class="$store.ui.darkMode ? 'text-white/55' : 'text-[#2f6d71]'"></p>
                <h1 class="mt-3 max-w-4xl text-4xl font-semibold leading-tight sm:text-5xl"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'" x-text="$store.ui.t('helpHeroTitle')">
                </h1>
                <p class="mt-5 max-w-3xl text-base leading-relaxed sm:text-lg"
                    :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4e767a]'"
                    x-text="$store.ui.t('helpHeroDescription')">
                </p>
            </div>
        </section>

        <section class="relative overflow-hidden px-6 pb-18" :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'"
            x-data="{
                query: '',
                openIndex: 0,
                showAllFaqs: false,
                initialFaqCount: 10,
                faqs: [
                    {
                        q: 'helpFaq1Q',
                        a: 'helpFaq1A',
                        mediaSrc: 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Customer support operator reviewing conversation flow'
                    },
                    {
                        q: 'helpFaq2Q',
                        a: 'helpFaq2A',
                        mediaSrc: 'https://images.unsplash.com/photo-1552581234-26160f608093?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Operations dashboard for ticket assignment'
                    },
                    {
                        q: 'helpFaq3Q',
                        a: 'helpFaq3A',
                        mediaSrc: 'https://media.giphy.com/media/l0MYt5jPR6QX5pnqM/giphy.gif',
                        mediaAlt: 'Animated representation of AI-assisted workflow'
                    },
                    {
                        q: 'helpFaq4Q',
                        a: 'helpFaq4A',
                        mediaSrc: 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Time tracking and SLA performance board'
                    },
                    {
                        q: 'helpFaq5Q',
                        a: 'helpFaq5A',
                        mediaSrc: 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Automation configuration interface concept'
                    },
                    {
                        q: 'helpFaq6Q',
                        a: 'helpFaq6A',
                        mediaSrc: 'https://media.giphy.com/media/3o7aD2saalBwwftBIY/giphy.gif',
                        mediaAlt: 'Animated lifecycle sequence'
                    },
                    {
                        q: 'helpFaq7Q',
                        a: 'helpFaq7A',
                        mediaSrc: 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Knowledge base planning workspace'
                    },
                    {
                        q: 'helpFaq8Q',
                        a: 'helpFaq8A',
                        mediaSrc: 'https://images.unsplash.com/photo-1518773553398-650c184e0bb3?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Website widget integration setup'
                    },
                    {
                        q: 'helpFaq9Q',
                        a: 'helpFaq9A',
                        mediaSrc: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Secure server infrastructure and data isolation'
                    },
                    {
                        q: 'helpFaq10Q',
                        a: 'helpFaq10A',
                        mediaSrc: 'https://images.unsplash.com/photo-1551281044-8b54f0f6b5f3?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Reporting dashboard with charts and trend lines'
                    },
                    {
                        q: 'helpFaq11Q',
                        a: 'helpFaq11A',
                        mediaSrc: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Bulk operations and queue controls in dashboard'
                    },
                    {
                        q: 'helpFaq12Q',
                        a: 'helpFaq12A',
                        mediaSrc: 'https://images.unsplash.com/photo-1526628953301-3e589a6a8b74?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Saved filter views for operations teams'
                    },
                    {
                        q: 'helpFaq13Q',
                        a: 'helpFaq13A',
                        mediaSrc: 'https://media.giphy.com/media/xT9IgG50Fb7Mi0prBC/giphy.gif',
                        mediaAlt: 'Animated collaborative team note workflow'
                    },
                    {
                        q: 'helpFaq14Q',
                        a: 'helpFaq14A',
                        mediaSrc: 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Role-based permission and access setup'
                    },
                    {
                        q: 'helpFaq15Q',
                        a: 'helpFaq15A',
                        mediaSrc: 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Priority support workflow for VIP customers'
                    },
                    {
                        q: 'helpFaq16Q',
                        a: 'helpFaq16A',
                        mediaSrc: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Custom form field configuration for support widget'
                    },
                    {
                        q: 'helpFaq17Q',
                        a: 'helpFaq17A',
                        mediaSrc: 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Alert-based fallback process for unassigned tickets'
                    },
                    {
                        q: 'helpFaq18Q',
                        a: 'helpFaq18A',
                        mediaSrc: 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Notification settings and alerts panel'
                    },
                    {
                        q: 'helpFaq19Q',
                        a: 'helpFaq19A',
                        mediaSrc: 'https://images.unsplash.com/photo-1543286386-713bdd548da4?auto=format&fit=crop&w=1200&q=80',
                        mediaAlt: 'Data export and reporting for executive reviews'
                    },
                    {
                        q: 'helpFaq20Q',
                        a: 'helpFaq20A',
                        mediaSrc: 'https://media.giphy.com/media/26AHONQ79FdWZhAI0/giphy.gif',
                        mediaAlt: 'Animated chatbot-to-ticket handoff flow'
                    }
                ],
                matches(faq) {
                    if (!this.query.trim()) {
                        return true;
                    }

                    const term = this.query.toLowerCase();
                    return this.$store.ui.t(faq.q).toLowerCase().includes(term) || this.$store.ui.t(faq.a).toLowerCase().includes(term);
                }
            }">
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_15%_20%,rgba(80,220,120,0.12),transparent_36%),radial-gradient(circle_at_82%_70%,rgba(61,196,108,0.16),transparent_40%)]'
                    : 'bg-[radial-gradient(circle_at_15%_20%,rgba(80,220,120,0.08),transparent_36%),radial-gradient(circle_at_82%_70%,rgba(61,196,108,0.08),transparent_40%)]'">
            </div>
            <div class="mx-auto max-w-6xl">
                <div class="scroll-reveal rounded-3xl border p-6 sm:p-8"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                    <label for="help_center_search" class="mb-2 block text-sm font-medium"
                        :class="$store.ui.darkMode ? 'text-white/90' : 'text-[#17494D]'"
                        x-text="$store.ui.t('helpSearchLabel')"></label>
                    <input id="help_center_search" type="text" x-model="query"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition"
                        :class="$store.ui.darkMode
                            ? 'border-white/20 bg-black/30 text-white placeholder-white/40 focus:border-white/40 focus:ring-1 focus:ring-white/20'
                            : 'border-[#cde5e6] bg-white text-[#173f42] placeholder-[#7a9da0] focus:border-[#4ea4a8] focus:ring-1 focus:ring-[#b4e5e7]'"
                        :placeholder="$store.ui.t('helpSearchPlaceholder')" />
                </div>

                <div class="mt-7 grid gap-7 lg:grid-cols-[1.1fr_0.9fr]">
                    <section class="scroll-reveal rounded-3xl border p-6 sm:p-8"
                        :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-white'"
                        aria-label="Frequently asked questions">
                        <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('helpPopularAnswersTitle')"></h2>

                        <div class="mt-6 space-y-3">
                            <template x-for="(faq, index) in faqs" :key="faq.q">
                                <article
                                    x-show="matches(faq) && (showAllFaqs || index < initialFaqCount || query.trim())"
                                    x-transition class="rounded-2xl border px-4 py-3"
                                    :class="$store.ui.darkMode ? 'border-white/15 bg-white/5' : 'border-[#d7ecee] bg-[#fcffff]'">
                                    <button type="button"
                                        class="flex w-full items-start justify-between gap-4 text-left"
                                        :aria-expanded="openIndex === index" :aria-controls="`faq-panel-${index}`"
                                        @click="openIndex = openIndex === index ? null : index">
                                        <span class="text-sm font-semibold sm:text-base"
                                            :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                            x-text="$store.ui.t(faq.q)"></span>
                                        <span class="mt-1 text-xs"
                                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4e7c80]'"
                                            x-text="openIndex === index ? '−' : '+'"></span>
                                    </button>
                                    <div x-show="openIndex === index" x-collapse :id="`faq-panel-${index}`"
                                        class="pt-3">
                                        <p class="text-sm leading-relaxed"
                                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4f777b]'"
                                            x-text="$store.ui.t(faq.a)"></p>
                                        <template x-if="faq.mediaSrc">
                                            <img :src="faq.mediaSrc" :alt="faq.mediaAlt"
                                                class="mt-4 h-44 w-full rounded-xl border object-cover"
                                                :class="$store.ui.darkMode ? 'border-emerald-300/25' : 'border-[#d7ecee]'"
                                                loading="lazy" />
                                        </template>
                                    </div>
                                </article>
                            </template>
                        </div>

                        <div class="mt-5" x-show="!showAllFaqs && !query.trim()">
                            <button type="button" @click="showAllFaqs = true"
                                class="inline-flex items-center justify-center rounded-full bg-[#219653] px-5 py-2.5 text-sm font-semibold text-white transition"
                                x-text="$store.ui.t('helpShowMoreAnswers')">
                            </button>
                        </div>
                    </section>

                    <aside class="scroll-stagger space-y-5" aria-label="Quick guides">
                        <div class="rounded-3xl border p-6"
                            :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                            <h3 class="text-lg font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('helpJourneyTitle')"></h3>
                            <ol class="mt-3 space-y-2 text-sm"
                                :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#547d80]'">
                                <li x-text="$store.ui.t('helpJourney1')"></li>
                                <li x-text="$store.ui.t('helpJourney2')"></li>
                                <li x-text="$store.ui.t('helpJourney3')"></li>
                                <li x-text="$store.ui.t('helpJourney4')"></li>
                            </ol>
                        </div>

                        <div class="rounded-3xl border p-6"
                            :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                            <h3 class="text-lg font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('helpRoleWorkflowsTitle')"></h3>
                            <ul class="mt-3 space-y-2 text-sm"
                                :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#547d80]'">
                                <li x-text="$store.ui.t('helpRoleAdmin')"></li>
                                <li x-text="$store.ui.t('helpRoleAgent')"></li>
                                <li x-text="$store.ui.t('helpRoleCustomer')"></li>
                            </ul>
                        </div>

                        <div class="rounded-3xl border p-6"
                            :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                            <h3 class="text-lg font-semibold"
                                :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                                x-text="$store.ui.t('helpAssistanceTitle')"></h3>
                            <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                                x-text="$store.ui.t('helpAssistanceBody')">
                            </p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="{{ route('contact') }}"
                                    class="inline-flex items-center justify-center rounded-full bg-[#219653] px-5 py-2.5 text-sm font-semibold text-white transition"
                                    x-text="$store.ui.t('helpAssistancePrimaryCta')">
                                </a>
                                <a href="mailto:support@helpdesk-system.test"
                                    class="inline-flex items-center justify-center rounded-full border px-5 py-2.5 text-sm font-semibold transition"
                                    :class="$store.ui.darkMode ? 'border-white/25 text-white' : 'border-[#9fd2d4] text-[#17494D]'"
                                    x-text="$store.ui.t('helpAssistanceSecondaryCta')">
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>

                <section class="scroll-reveal mt-12 grid gap-6 lg:grid-cols-[1fr_1fr] lg:items-center">
                    <article class="rounded-3xl border p-6 sm:p-8"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-400/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                        <h2 class="text-2xl font-semibold"
                            :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                            x-text="$store.ui.t('helpLifecycleTitle')">
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-emerald-50/80' : 'text-[#4f787b]'"
                            x-text="$store.ui.t('helpLifecycleBody1')">
                        </p>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-emerald-50/80' : 'text-[#4f787b]'"
                            x-text="$store.ui.t('helpLifecycleBody2')">
                        </p>
                    </article>

                    <div class="scroll-stagger grid grid-cols-2 gap-4">
                        <img src="https://images.unsplash.com/photo-1552581234-26160f608093?auto=format&fit=crop&w=1000&q=80"
                            alt="Support operations command center dashboards"
                            class="h-44 w-full rounded-2xl object-cover" loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=1000&q=80"
                            alt="Agent handling customer ticket queue" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80"
                            alt="Analytics board showing support metrics" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1485217988980-11786ced9454?auto=format&fit=crop&w=1000&q=80"
                            alt="Customer success call and team planning" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                    </div>
                </section>

                <section class="scroll-reveal mt-12 rounded-3xl border p-6 sm:p-8"
                    :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d4ebec] bg-white'">
                    <h2 class="text-2xl font-semibold"
                        :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                        x-text="$store.ui.t('helpPlaybooksTitle')">
                    </h2>
                    <div class="scroll-stagger mt-6 grid gap-5 md:grid-cols-3">
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d7ecee] bg-[#fcffff]'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                                x-text="$store.ui.t('helpPlaybookAdminTitle')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-emerald-50/75' : 'text-[#547d80]'"
                                x-text="$store.ui.t('helpPlaybookAdminBody')">
                            </p>
                        </article>
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d7ecee] bg-[#fcffff]'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                                x-text="$store.ui.t('helpPlaybookAgentTitle')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-emerald-50/75' : 'text-[#547d80]'"
                                x-text="$store.ui.t('helpPlaybookAgentBody')">
                            </p>
                        </article>
                        <article class="rounded-2xl border p-5"
                            :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d7ecee] bg-[#fcffff]'">
                            <h3 class="text-base font-semibold"
                                :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                                x-text="$store.ui.t('helpPlaybookCustomerTitle')"></h3>
                            <p class="mt-2 text-sm leading-relaxed"
                                :class="$store.ui.darkMode ? 'text-emerald-50/75' : 'text-[#547d80]'"
                                x-text="$store.ui.t('helpPlaybookCustomerBody')">
                            </p>
                        </article>
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
            var revealEls = document.querySelectorAll('.scroll-reveal, .scroll-stagger');
            var revealObs = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) { entry.target.classList.add('is-visible'); }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            revealEls.forEach(function (el) { revealObs.observe(el); });

            /* ── Typewriter on hero label ── */
            var tw = document.getElementById('help-typewriter');
            if (tw) {
                var text = Alpine.store('ui').t('helpTypewriter');
                var i = 0;
                tw.classList.add('typing-cursor');
                (function type() {
                    if (i < text.length) {
                        tw.textContent += text.charAt(i);
                        i++;
                        setTimeout(type, 80);
                    } else {
                        tw.classList.remove('typing-cursor');
                    }
                })();
            }

            /* ── Floating particles in hero ── */
            var pb = document.getElementById('help-hero-particles');
            if (pb) {
                for (var p = 0; p < 16; p++) {
                    var d = document.createElement('span');
                    d.className = 'particle';
                    var s = Math.random() * 5 + 3;
                    d.style.width = s + 'px';
                    d.style.height = s + 'px';
                    d.style.left = Math.random() * 100 + '%';
                    d.style.top = Math.random() * 100 + '%';
                    d.style.setProperty('--dur', (Math.random() * 6 + 4) + 's');
                    d.style.setProperty('--delay', (Math.random() * 5) + 's');
                    pb.appendChild(d);
                }
            }

            /* ── Animate FAQ items with sequential slide-in ── */
            var faqCards = document.querySelectorAll('[x-show="matches(faq)"]');
            if (faqCards.length) {
                var faqObs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateX(0)';
                        }
                    });
                }, { threshold: 0.08 });
                faqCards.forEach(function (card, idx) {
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-18px)';
                    card.style.transition = 'opacity 0.5s ease ' + (idx * 0.06) + 's, transform 0.5s ease ' + (idx * 0.06) + 's';
                    faqObs.observe(card);
                });
            }
        });
    </script>
</body>

</html>