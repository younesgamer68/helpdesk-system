<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Contact Us - Helpdesk System</title>

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
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data
    class="flex min-h-screen flex-col bg-white font-[Instrument_Sans,ui-sans-serif,system-ui,sans-serif] text-[#17494D] antialiased transition-colors duration-300"
    :class="$store.ui.darkMode ? 'bg-black text-white' : 'bg-white text-[#17494D]'">



    <x-nav-bar />
    <x-loading-overlay />

    <main class="flex-1">
        <section class="relative overflow-hidden px-6 pb-10 pt-10 sm:pt-14"
            :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">
            <div class="particles-container" id="contact-hero-particles"></div>
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_15%_0%,rgba(94,219,86,0.22),transparent_34%),radial-gradient(circle_at_92%_85%,rgba(34,151,83,0.32),transparent_42%),radial-gradient(circle_at_55%_40%,rgba(120,255,170,0.14),transparent_40%)]'
                    : 'bg-[radial-gradient(circle_at_15%_0%,rgba(94,219,86,0.18),transparent_34%),radial-gradient(circle_at_92%_85%,rgba(24,136,141,0.14),transparent_42%)]'">
            </div>

            <div class="relative mx-auto max-w-6xl">
                <p id="contact-typewriter" class="text-xs font-semibold uppercase tracking-[0.22em]"
                    :class="$store.ui.darkMode ? 'text-white/55' : 'text-[#2f6d71]'"></p>
                <h1 class="mt-3 max-w-3xl text-4xl font-semibold leading-tight sm:text-5xl"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                    x-text="$store.ui.t('contactHeroTitle')"></h1>
                <p class="mt-5 max-w-3xl text-base leading-relaxed sm:text-lg"
                    :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#4e767a]'"
                    x-text="$store.ui.t('contactHeroDescription')">
                </p>
            </div>
        </section>

        <section class="relative overflow-hidden px-6 pb-18 pt-8" :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'"
            x-data="{ submitted: false }">
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_10%_18%,rgba(82,220,130,0.12),transparent_34%),radial-gradient(circle_at_85%_80%,rgba(64,198,111,0.16),transparent_40%)]'
                    : 'bg-[radial-gradient(circle_at_10%_18%,rgba(82,220,130,0.08),transparent_34%),radial-gradient(circle_at_85%_80%,rgba(64,198,111,0.08),transparent_40%)]'">
            </div>
            <div class="mx-auto grid max-w-6xl gap-7 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="scroll-reveal rounded-3xl border p-6 sm:p-8" style="position:relative"
                    :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                    <div class="glow-trail-container" id="contact-glow-trail"></div>
                    <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                        x-text="$store.ui.t('contactFormTitle')"></h2>
                    <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                        x-text="$store.ui.t('contactFormRequiredNote')">
                    </p>

                    <p x-show="submitted" x-transition aria-live="polite"
                        class="mt-4 rounded-xl border px-4 py-3 text-sm"
                        :class="$store.ui.darkMode ? 'border-emerald-300/25 bg-emerald-500/10 text-emerald-200' : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
                        style="display:none">
                        <span x-text="$store.ui.t('contactSuccessMessage')"></span>
                    </p>

                    <form class="mt-6 grid gap-4" @submit.prevent="submitted = true" novalidate>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="contact_name" class="mb-1.5 block text-sm font-medium"
                                    :class="$store.ui.darkMode ? 'text-white/90' : 'text-[#17494D]'"
                                    x-text="$store.ui.t('contactFieldName')"></label>
                                <input id="contact_name" name="name" type="text" required autocomplete="name"
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition"
                                    :class="$store.ui.darkMode
                                        ? 'border-white/20 bg-black/30 text-white placeholder-white/40 focus:border-white/40 focus:ring-1 focus:ring-white/20'
                                        : 'border-[#cde5e6] bg-white text-[#173f42] placeholder-[#7a9da0] focus:border-[#4ea4a8] focus:ring-1 focus:ring-[#b4e5e7]'"
                                    :placeholder="$store.ui.t('contactFieldNamePlaceholder')" />
                            </div>
                            <div>
                                <label for="contact_email" class="mb-1.5 block text-sm font-medium"
                                    :class="$store.ui.darkMode ? 'text-white/90' : 'text-[#17494D]'"
                                    x-text="$store.ui.t('contactFieldEmail')"></label>
                                <input id="contact_email" name="email" type="email" required autocomplete="email"
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition"
                                    :class="$store.ui.darkMode
                                        ? 'border-white/20 bg-black/30 text-white placeholder-white/40 focus:border-white/40 focus:ring-1 focus:ring-white/20'
                                        : 'border-[#cde5e6] bg-white text-[#173f42] placeholder-[#7a9da0] focus:border-[#4ea4a8] focus:ring-1 focus:ring-[#b4e5e7]'"
                                    :placeholder="$store.ui.t('contactFieldEmailPlaceholder')" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="contact_company" class="mb-1.5 block text-sm font-medium"
                                    :class="$store.ui.darkMode ? 'text-white/90' : 'text-[#17494D]'"
                                    x-text="$store.ui.t('contactFieldCompany')"></label>
                                <input id="contact_company" name="company" type="text" required
                                    autocomplete="organization"
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition"
                                    :class="$store.ui.darkMode
                                        ? 'border-white/20 bg-black/30 text-white placeholder-white/40 focus:border-white/40 focus:ring-1 focus:ring-white/20'
                                        : 'border-[#cde5e6] bg-white text-[#173f42] placeholder-[#7a9da0] focus:border-[#4ea4a8] focus:ring-1 focus:ring-[#b4e5e7]'"
                                    :placeholder="$store.ui.t('contactFieldCompanyPlaceholder')" />
                            </div>
                            <div>
                                <label for="contact_team_size" class="mb-1.5 block text-sm font-medium"
                                    :class="$store.ui.darkMode ? 'text-white/90' : 'text-[#17494D]'"
                                    x-text="$store.ui.t('contactFieldTeamSize')"></label>
                                <select id="contact_team_size" name="team_size"
                                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition"
                                    :class="$store.ui.darkMode
                                        ? 'border-white/20 bg-black/30 text-white focus:border-white/40 focus:ring-1 focus:ring-white/20'
                                        : 'border-[#cde5e6] bg-white text-[#173f42] focus:border-[#4ea4a8] focus:ring-1 focus:ring-[#b4e5e7]'">
                                    <option value="" x-text="$store.ui.t('contactTeamSizeOption0')"></option>
                                    <option value="1-5" x-text="$store.ui.t('contactTeamSizeOption1')"></option>
                                    <option value="6-20" x-text="$store.ui.t('contactTeamSizeOption2')"></option>
                                    <option value="21-50" x-text="$store.ui.t('contactTeamSizeOption3')"></option>
                                    <option value="50+" x-text="$store.ui.t('contactTeamSizeOption4')"></option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="contact_topic" class="mb-1.5 block text-sm font-medium"
                                :class="$store.ui.darkMode ? 'text-white/90' : 'text-[#17494D]'"
                                x-text="$store.ui.t('contactFieldTopic')"></label>
                            <select id="contact_topic" name="topic" required
                                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition"
                                :class="$store.ui.darkMode
                                    ? 'border-white/20 bg-black/30 text-white focus:border-white/40 focus:ring-1 focus:ring-white/20'
                                    : 'border-[#cde5e6] bg-white text-[#173f42] focus:border-[#4ea4a8] focus:ring-1 focus:ring-[#b4e5e7]'">
                                <option value="" x-text="$store.ui.t('contactTopicOption0')"></option>
                                <option value="demo" x-text="$store.ui.t('contactTopicOption1')"></option>
                                <option value="migration" x-text="$store.ui.t('contactTopicOption2')"></option>
                                <option value="pricing" x-text="$store.ui.t('contactTopicOption3')"></option>
                                <option value="technical" x-text="$store.ui.t('contactTopicOption4')"></option>
                            </select>
                        </div>

                        <div>
                            <label for="contact_message" class="mb-1.5 block text-sm font-medium"
                                :class="$store.ui.darkMode ? 'text-white/90' : 'text-[#17494D]'"
                                x-text="$store.ui.t('contactFieldMessage')"></label>
                            <textarea id="contact_message" name="message" rows="5" required
                                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition"
                                :class="$store.ui.darkMode
                                    ? 'border-white/20 bg-black/30 text-white placeholder-white/40 focus:border-white/40 focus:ring-1 focus:ring-white/20'
                                    : 'border-[#cde5e6] bg-white text-[#173f42] placeholder-[#7a9da0] focus:border-[#4ea4a8] focus:ring-1 focus:ring-[#b4e5e7]'"
                                :placeholder="$store.ui.t('contactFieldMessagePlaceholder')"></textarea>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                            <p class="text-xs" :class="$store.ui.darkMode ? 'text-white/50' : 'text-[#638c90]'"
                                x-text="$store.ui.t('contactResponseNote')">
                            </p>
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-[#219653] px-7 py-3 text-sm font-semibold text-white transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                                :class="$store.ui.darkMode ? 'focus-visible:ring-emerald-300 focus-visible:ring-offset-black' : 'focus-visible:ring-emerald-500 focus-visible:ring-offset-white'">
                                <span x-text="$store.ui.t('contactSubmit')"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="scroll-stagger space-y-5">
                    <div class="rounded-3xl border p-6"
                        :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                        <h3 class="text-lg font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('contactSalesTitle')"></h3>
                        <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                            x-text="$store.ui.t('contactSalesBody')">
                        </p>
                        <a href="mailto:sales@helpdesk-system.test"
                            class="mt-4 inline-block text-sm font-semibold underline underline-offset-4"
                            :class="$store.ui.darkMode ? 'text-emerald-300' : 'text-[#0f8d73]'">
                            sales@helpdesk-system.test
                        </a>
                    </div>

                    <div class="rounded-3xl border p-6"
                        :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                        <h3 class="text-lg font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('contactSupportTitle')"></h3>
                        <p class="mt-2 text-sm" :class="$store.ui.darkMode ? 'text-white/65' : 'text-[#567d81]'"
                            x-text="$store.ui.t('contactSupportBody')">
                        </p>
                        <a href="mailto:support@helpdesk-system.test"
                            class="mt-4 inline-block text-sm font-semibold underline underline-offset-4"
                            :class="$store.ui.darkMode ? 'text-emerald-300' : 'text-[#0f8d73]'">
                            support@helpdesk-system.test
                        </a>
                    </div>

                    <div class="rounded-3xl border p-6"
                        :class="$store.ui.darkMode ? 'border-white/10 bg-white/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                        <h3 class="text-lg font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#17494D]'"
                            x-text="$store.ui.t('contactChecklistTitle')"></h3>
                        <ul class="mt-3 space-y-2 text-sm"
                            :class="$store.ui.darkMode ? 'text-white/70' : 'text-[#547d80]'">
                            <li x-text="$store.ui.t('contactChecklist1')"></li>
                            <li x-text="$store.ui.t('contactChecklist2')"></li>
                            <li x-text="$store.ui.t('contactChecklist3')"></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>



        <section class="scroll-reveal relative w-full overflow-hidden border-b px-6 py-12 sm:py-14"
            :class="$store.ui.darkMode ? 'border-emerald-300/15 bg-black' : 'border-[#e2ecee] bg-[#f9fcfc]'"
            aria-label="HelpDesk office locations">
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_20%_20%,rgba(84,219,129,0.12),transparent_36%),radial-gradient(circle_at_88%_82%,rgba(39,176,95,0.18),transparent_40%)]'
                    : 'bg-[radial-gradient(circle_at_20%_20%,rgba(84,219,129,0.06),transparent_36%),radial-gradient(circle_at_88%_82%,rgba(39,176,95,0.08),transparent_40%)]'">
            </div>
            <div class="mx-auto max-w-7xl">
                <h2 class="text-center text-4xl font-semibold tracking-tight"
                    :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'"
                    x-text="$store.ui.t('contactOfficesTitle')">
                </h2>

                <div class="scroll-stagger mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Global
                            HQ -
                            MAP</h3>
                        <p>181 Market Street</p>
                        <p>San Francisco, CA 94105</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">
                            Australia -
                            MAP</h3>
                        <p>Level 13, 550 Bourke Street</p>
                        <p>Melbourne, Victoria 3000</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/au</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Brazil -
                            MAP
                        </h3>
                        <p>Av. Paulista 920, 14th floor</p>
                        <p>Sao Paulo, SP 04583-110</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com.br</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Canada -
                            MAP
                        </h3>
                        <p>385 Av. Viger O</p>
                        <p>Montreal, QC H2Z 1M9</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/ca</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Denmark
                            -
                            MAP</h3>
                        <p>Njalsgade 72C, 2</p>
                        <p>2300 Kobenhavn S</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/dk</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">France -
                            MAP
                        </h3>
                        <p>32 Rue de Trevise</p>
                        <p>75009 Paris</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.fr</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Germany
                            -
                            MAP</h3>
                        <p>Paul-Lincke-Ufer 39/40, Hof 4</p>
                        <p>10999 Berlin</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.de</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">India,
                            Bangalore - MAP</h3>
                        <p>62/53 Church Street</p>
                        <p>Bengaluru, Karnataka 560001</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/in</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">India,
                            Pune
                            - MAP</h3>
                        <p>North Main Road, Koregaon Park</p>
                        <p>Pune, Maharashtra 411001</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/in</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Ireland
                            -
                            MAP</h3>
                        <p>55 Charlemont Place</p>
                        <p>Dublin D02 F985</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.ie</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Italy -
                            MAP
                        </h3>
                        <p>Via San Marco 21</p>
                        <p>20121 Milano</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/it</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Japan -
                            MAP
                        </h3>
                        <p>1-2-1 Kyobashi, Chuo City</p>
                        <p>Tokyo 104-0031</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.co.jp</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Mexico -
                            MAP
                        </h3>
                        <p>Paseo de la Reforma 250</p>
                        <p>Cuauhtemoc, CDMX 06600</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/mx</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">
                            Netherlands - MAP
                        </h3>
                        <p>Herengracht 420</p>
                        <p>1017 BZ Amsterdam</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.nl</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Nigeria
                            - MAP
                        </h3>
                        <p>15A Ozumba Mbadiwe Road</p>
                        <p>Victoria Island, Lagos</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/ng</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Norway -
                            MAP
                        </h3>
                        <p>Dronning Eufemias gate 16</p>
                        <p>0191 Oslo</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.no</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Poland -
                            MAP
                        </h3>
                        <p>Rondo Daszynskiego 1</p>
                        <p>00-843 Warsaw</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.pl</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Portugal
                            - MAP
                        </h3>
                        <p>Av. da Liberdade 245</p>
                        <p>1250-143 Lisbon</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.pt</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">
                            Singapore - MAP
                        </h3>
                        <p>80 Robinson Road</p>
                        <p>Singapore 068898</p>
                        <a href="#"
                            class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.com/sg</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">South
                            Africa - MAP
                        </h3>
                        <p>11 Alice Lane</p>
                        <p>Sandton, Johannesburg 2196</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.co.za</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">South
                            Korea - MAP
                        </h3>
                        <p>152 Teheran-ro, Gangnam-gu</p>
                        <p>Seoul 06236</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.kr</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">Spain -
                            MAP
                        </h3>
                        <p>Paseo de la Castellana 95</p>
                        <p>28046 Madrid</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.es</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">UAE -
                            MAP
                        </h3>
                        <p>Sheikh Zayed Road, Index Tower</p>
                        <p>Dubai, UAE</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.ae</a>
                    </article>

                    <article class="space-y-1.5 text-sm">
                        <h3 class="font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-[#0f2230]'">United
                            Kingdom - MAP
                        </h3>
                        <p>1 Canada Square, Canary Wharf</p>
                        <p>London E14 5AB</p>
                        <a href="#" class="font-semibold text-[#2d8a56] underline underline-offset-2">helpdesk.co.uk</a>
                    </article>
                </div>
            </div>
        </section>

        <section class="scroll-reveal relative overflow-hidden px-6 py-14"
            :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">
            <div class="pointer-events-none absolute inset-0"
                :class="$store.ui.darkMode
                    ? 'bg-[radial-gradient(circle_at_12%_10%,rgba(90,233,139,0.14),transparent_36%),radial-gradient(circle_at_82%_82%,rgba(48,188,106,0.18),transparent_42%)]'
                    : 'bg-[radial-gradient(circle_at_12%_10%,rgba(90,233,139,0.08),transparent_36%),radial-gradient(circle_at_82%_82%,rgba(48,188,106,0.10),transparent_42%)]'">
            </div>

            <div class="relative mx-auto max-w-7xl">
                <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <article class="rounded-3xl border p-6 sm:p-8"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d4ebec] bg-[#f8ffff]'">
                        <h2 class="text-2xl font-semibold"
                            :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                            x-text="$store.ui.t('contactWhyTitle')">
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-emerald-50/80' : 'text-[#547d80]'"
                            x-text="$store.ui.t('contactWhyBody1')">
                        </p>
                        <p class="mt-4 text-sm leading-relaxed sm:text-base"
                            :class="$store.ui.darkMode ? 'text-emerald-50/80' : 'text-[#547d80]'"
                            x-text="$store.ui.t('contactWhyBody2')">
                        </p>
                    </article>

                    <div class="scroll-stagger grid grid-cols-2 gap-4">
                        <img src="https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=900&q=80"
                            alt="Customer success onboarding session" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1553484771-371a605b060b?auto=format&fit=crop&w=900&q=80"
                            alt="Team support strategy workshop" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=900&q=80"
                            alt="Customer operations meeting table" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                        <img src="https://images.unsplash.com/photo-1526628953301-3e589a6a8b74?auto=format&fit=crop&w=900&q=80"
                            alt="Live support dashboard review" class="h-44 w-full rounded-2xl object-cover"
                            loading="lazy" />
                    </div>
                </div>
            </div>
        </section>

        <section class="scroll-reveal px-6 pb-16" :class="$store.ui.darkMode ? 'bg-black' : 'bg-white'">
            <div class="mx-auto max-w-7xl rounded-3xl border p-6 sm:p-8"
                :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-emerald-500/5' : 'border-[#d4ebec] bg-white'">
                <h2 class="text-2xl font-semibold" :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                    x-text="$store.ui.t('contactAfterTitle')">
                </h2>
                <div class="scroll-stagger mt-6 grid gap-5 md:grid-cols-3">
                    <article class="rounded-2xl border p-5"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d8eced] bg-[#fbffff]'">
                        <h3 class="text-base font-semibold"
                            :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                            x-text="$store.ui.t('contactAfter1Title')"></h3>
                        <p class="mt-2 text-sm leading-relaxed"
                            :class="$store.ui.darkMode ? 'text-emerald-50/75' : 'text-[#547d80]'"
                            x-text="$store.ui.t('contactAfter1Body')">
                        </p>
                    </article>
                    <article class="rounded-2xl border p-5"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d8eced] bg-[#fbffff]'">
                        <h3 class="text-base font-semibold"
                            :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                            x-text="$store.ui.t('contactAfter2Title')"></h3>
                        <p class="mt-2 text-sm leading-relaxed"
                            :class="$store.ui.darkMode ? 'text-emerald-50/75' : 'text-[#547d80]'"
                            x-text="$store.ui.t('contactAfter2Body')">
                        </p>
                    </article>
                    <article class="rounded-2xl border p-5"
                        :class="$store.ui.darkMode ? 'border-emerald-300/20 bg-black/25' : 'border-[#d8eced] bg-[#fbffff]'">
                        <h3 class="text-base font-semibold"
                            :class="$store.ui.darkMode ? 'text-emerald-100' : 'text-[#17494D]'"
                            x-text="$store.ui.t('contactAfter3Title')"></h3>
                        <p class="mt-2 text-sm leading-relaxed"
                            :class="$store.ui.darkMode ? 'text-emerald-50/75' : 'text-[#547d80]'"
                            x-text="$store.ui.t('contactAfter3Body')">
                        </p>
                    </article>
                </div>
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
            var tw = document.getElementById('contact-typewriter');
            if (tw) {
                var text = Alpine.store('ui').t('contactTypewriter');
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
            var pb = document.getElementById('contact-hero-particles');
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

            /* ── Glow trail on form container ── */
            var glowBox = document.getElementById('contact-glow-trail');
            var formWrap = glowBox ? glowBox.parentElement : null;
            if (formWrap && glowBox) {
                formWrap.addEventListener('mousemove', function (e) {
                    var rect = formWrap.getBoundingClientRect();
                    var x = e.clientX - rect.left;
                    var y = e.clientY - rect.top;
                    var glow = document.createElement('span');
                    glow.style.cssText = 'position:absolute;width:120px;height:120px;border-radius:50%;pointer-events:none;'
                        + 'background:radial-gradient(circle,rgba(94,219,86,0.18),transparent 70%);'
                        + 'left:' + (x - 60) + 'px;top:' + (y - 60) + 'px;'
                        + 'transition:opacity 0.8s ease;opacity:1;';
                    glowBox.appendChild(glow);
                    setTimeout(function () { glow.style.opacity = '0'; }, 50);
                    setTimeout(function () { if (glow.parentNode) glow.parentNode.removeChild(glow); }, 900);
                });
            }
        });
    </script>
</body>

</html>