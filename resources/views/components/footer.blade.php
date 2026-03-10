{{-- =====================================================================
Footer — reads darkMode / lang / t() from Alpine.store('ui')
===================================================================== --}}
<footer x-data :class="$store.ui.darkMode ? 'bg-gray-950 border-gray-800' : 'bg-[#17494D] border-[#17494D] text-white'"
    class="w-full border-t transition-colors duration-300">

    {{-- Main footer content --}}
    <div class="mx-auto max-w-7xl px-6 py-14">
        <div class="grid grid-cols-2 gap-10 md:grid-cols-5">

            {{-- Brand column --}}
            <div class="col-span-2 md:col-span-1">
                <a href="/" class="mb-4 inline-block">
                    <img x-show="!$store.ui.darkMode" src="/images/Logos/logo%20with%20text%20LM.png" alt="HD Logo"
                        style="height: 48px; width: auto;" class="transition-opacity duration-300" />
                    <img x-show="$store.ui.darkMode" src="/images/Logos/Logo%20with%20text%20DM.png" alt="HD Logo"
                        style="height: 48px; width: auto; display: none;" class="transition-opacity duration-300" />
                </a>
                <p class="mt-3 text-sm leading-relaxed" :class="$store.ui.darkMode ? 'text-gray-400' : 'text-white/70'"
                    x-text="$store.ui.t('footerTagline')"></p>

                {{-- Social icons --}}
                <div class="mt-5 flex items-center gap-3">
                    {{-- X / Twitter --}}
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-500 hover:text-white' : 'text-white/70 hover:text-white'"
                        class="transition-colors duration-200" aria-label="Twitter">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    {{-- LinkedIn --}}
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-500 hover:text-white' : 'text-white/70 hover:text-white'"
                        class="transition-colors duration-200" aria-label="LinkedIn">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                    {{-- GitHub --}}
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-500 hover:text-white' : 'text-white/70 hover:text-white'"
                        class="transition-colors duration-200" aria-label="GitHub">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" />
                        </svg>
                    </a>
                    {{-- YouTube --}}
                    <a href="#"
                        :class="$store.ui.darkMode ? 'text-gray-500 hover:text-white' : 'text-white/70 hover:text-white'"
                        class="transition-colors duration-200" aria-label="YouTube">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Products column --}}
            <div>
                <h4 class="mb-4 text-sm font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-white'"
                    x-text="$store.ui.t('footerProducts')"></h4>
                <ul class="space-y-2.5">
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150"
                            x-text="$store.ui.t('footerPlatformOverview')"></a></li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150"
                            x-text="$store.ui.t('footerIntegrations')"></a></li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150"
                            x-text="$store.ui.t('footerMarketplace')"></a></li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerDevelopers')"></a>
                    </li>
                </ul>
            </div>

            {{-- Solutions column --}}
            <div>
                <h4 class="mb-4 text-sm font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-white'"
                    x-text="$store.ui.t('footerSolutions')"></h4>
                <ul class="space-y-2.5">
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150"
                            x-text="$store.ui.t('footerCustomerService')"></a></li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerItSupport')"></a>
                    </li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerHrTeams')"></a>
                    </li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerSalesTeams')"></a>
                    </li>
                </ul>
            </div>

            {{-- Resources column --}}
            <div>
                <h4 class="mb-4 text-sm font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-white'"
                    x-text="$store.ui.t('footerResources')"></h4>
                <ul class="space-y-2.5">
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerBlog')"></a></li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150"
                            x-text="$store.ui.t('footerDocumentation')"></a></li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerWebinars')"></a>
                    </li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerCommunity')"></a>
                    </li>
                </ul>
            </div>

            {{-- Company column --}}
            <div>
                <h4 class="mb-4 text-sm font-semibold" :class="$store.ui.darkMode ? 'text-white' : 'text-white'"
                    x-text="$store.ui.t('footerCompany')"></h4>
                <ul class="space-y-2.5">
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerAbout')"></a></li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerCareers')"></a>
                    </li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerContact')"></a>
                    </li>
                    <li><a href="#"
                            :class="$store.ui.darkMode ? 'text-gray-400 hover:text-white' : 'text-white/70 hover:text-white'"
                            class="text-sm transition-colors duration-150" x-text="$store.ui.t('footerPrivacy')"></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t transition-colors duration-300"
        :class="$store.ui.darkMode ? 'border-gray-800' : 'border-[#FFF0E5]/10'">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-6 py-5 sm:flex-row">
            <p class="text-xs" :class="$store.ui.darkMode ? 'text-gray-500' : 'text-white/50'">
                &copy; {{ date('Y') }} Helpdesk System.
                <span x-text="$store.ui.t('footerRights')"></span>
            </p>
            <div class="flex items-center gap-4">
                <a href="#"
                    :class="$store.ui.darkMode ? 'text-gray-500 hover:text-gray-300' : 'text-white/50 hover:text-white'"
                    class="text-xs transition-colors duration-150" x-text="$store.ui.t('footerPrivacy')"></a>
                <span :class="$store.ui.darkMode ? 'text-gray-700' : 'text-white/30'" class="text-xs">&middot;</span>
                <a href="#"
                    :class="$store.ui.darkMode ? 'text-gray-500 hover:text-gray-300' : 'text-white/50 hover:text-white'"
                    class="text-xs transition-colors duration-150">Terms</a>
            </div>
        </div>
    </div>
</footer>