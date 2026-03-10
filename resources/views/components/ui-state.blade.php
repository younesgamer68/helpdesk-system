{{-- =====================================================================
Global UI State — Alpine.store('ui') for shared darkMode, lang, t()
Wrap your page content with <x-ui-state> ... </x-ui-state>
===================================================================== --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('ui', {
            darkMode: false,
            lang: 'English',

            /* -- i18n translation map -- */
            t(key) {
                const dict = {
                    English: {
                        /* Navbar */
                        products: 'Products',
                        solutions: 'Solutions',
                        resources: 'Resources',
                        pricing: 'Pricing',
                        tryFree: 'Try for free',
                        viewDemo: 'View demo',
                        dashboard: 'Dashboard',
                        platform: 'Platform',
                        platformOverview: 'Platform overview',
                        integrations: 'Integrations',
                        latestInnovations: 'Latest innovations',
                        appMarketplace: 'App marketplace',
                        developers: 'Developers',
                        trending: 'Trending',
                        trendItem1: 'HD AI Virtual Summit 2026',
                        trendDesc1: 'A global event for leaders in CX, IT, HR.',
                        trendItem2: 'Hidden costs of complexity',
                        trendDesc2: 'Uncover the IT complexity tax costing your business.',
                        trendItem3: 'HD partners with top teams',
                        trendDesc3: 'Discover how HD is powering global partnerships.',
                        byTeam: 'By Team',
                        customerService: 'Customer Service',
                        itSupport: 'IT Support',
                        hrTeams: 'HR Teams',
                        salesTeams: 'Sales Teams',
                        bySize: 'By Company Size',
                        enterprise: 'Enterprise',
                        midMarket: 'Mid-market',
                        smallBusiness: 'Small Business',
                        startups: 'Startups',
                        learn: 'Learn',
                        blog: 'Blog',
                        documentation: 'Documentation',
                        webinars: 'Webinars',
                        academy: 'Academy',
                        connect: 'Connect',
                        community: 'Community',
                        events: 'Events',
                        partnerProgram: 'Partner program',
                        support: 'Support',

                        /* Footer */
                        footerTagline: 'Modern helpdesk for modern teams.',
                        footerProducts: 'Products',
                        footerPlatformOverview: 'Platform Overview',
                        footerIntegrations: 'Integrations',
                        footerMarketplace: 'App Marketplace',
                        footerDevelopers: 'Developers',
                        footerSolutions: 'Solutions',
                        footerCustomerService: 'Customer Service',
                        footerItSupport: 'IT Support',
                        footerHrTeams: 'HR Teams',
                        footerSalesTeams: 'Sales Teams',
                        footerResources: 'Resources',
                        footerBlog: 'Blog',
                        footerDocumentation: 'Documentation',
                        footerWebinars: 'Webinars',
                        footerCommunity: 'Community',
                        footerCompany: 'Company',
                        footerAbout: 'About Us',
                        footerCareers: 'Careers',
                        footerContact: 'Contact',
                        footerPrivacy: 'Privacy Policy',
                        footerRights: 'All rights reserved.',
                    },
                    French: {
                        /* Navbar */
                        products: 'Produits',
                        solutions: 'Solutions',
                        resources: 'Ressources',
                        pricing: 'Tarifs',
                        tryFree: 'Essai gratuit',
                        viewDemo: 'Voir la d\u00e9mo',
                        dashboard: 'Tableau de bord',
                        platform: 'Plateforme',
                        platformOverview: 'Aper\u00e7u de la plateforme',
                        integrations: 'Int\u00e9grations',
                        latestInnovations: 'Derni\u00e8res innovations',
                        appMarketplace: 'Marketplace d\'apps',
                        developers: 'D\u00e9veloppeurs',
                        trending: 'Tendances',
                        trendItem1: 'Sommet virtuel HD AI 2026',
                        trendDesc1: 'Un \u00e9v\u00e9nement mondial pour les leaders CX, IT, RH.',
                        trendItem2: 'Co\u00fbts cach\u00e9s de la complexit\u00e9',
                        trendDesc2: 'D\u00e9couvrez la taxe de complexit\u00e9 IT qui co\u00fbte cher.',
                        trendItem3: 'HD s\'associe aux meilleures \u00e9quipes',
                        trendDesc3: 'D\u00e9couvrez comment HD alimente des partenariats mondiaux.',
                        byTeam: 'Par \u00e9quipe',
                        customerService: 'Service client',
                        itSupport: 'Support IT',
                        hrTeams: '\u00c9quipes RH',
                        salesTeams: '\u00c9quipes commerciales',
                        bySize: 'Par taille d\'entreprise',
                        enterprise: 'Entreprise',
                        midMarket: 'March\u00e9 interm\u00e9diaire',
                        smallBusiness: 'Petite entreprise',
                        startups: 'Startups',
                        learn: 'Apprendre',
                        blog: 'Blog',
                        documentation: 'Documentation',
                        webinars: 'Webinaires',
                        academy: 'Acad\u00e9mie',
                        connect: 'Connecter',
                        community: 'Communaut\u00e9',
                        events: '\u00c9v\u00e9nements',
                        partnerProgram: 'Programme partenaire',
                        support: 'Assistance',

                        /* Footer */
                        footerTagline: 'Un helpdesk moderne pour des \u00e9quipes modernes.',
                        footerProducts: 'Produits',
                        footerPlatformOverview: 'Aper\u00e7u de la plateforme',
                        footerIntegrations: 'Int\u00e9grations',
                        footerMarketplace: 'Marketplace d\'apps',
                        footerDevelopers: 'D\u00e9veloppeurs',
                        footerSolutions: 'Solutions',
                        footerCustomerService: 'Service client',
                        footerItSupport: 'Support IT',
                        footerHrTeams: '\u00c9quipes RH',
                        footerSalesTeams: '\u00c9quipes commerciales',
                        footerResources: 'Ressources',
                        footerBlog: 'Blog',
                        footerDocumentation: 'Documentation',
                        footerWebinars: 'Webinaires',
                        footerCommunity: 'Communaut\u00e9',
                        footerCompany: 'Entreprise',
                        footerAbout: '\u00c0 propos',
                        footerCareers: 'Carri\u00e8res',
                        footerContact: 'Contact',
                        footerPrivacy: 'Politique de confidentialit\u00e9',
                        footerRights: 'Tous droits r\u00e9serv\u00e9s.',
                    },
                };
                return dict[this.lang]?.[key] ?? key;
            },

            init() {
                /* Sync dark class on <html> whenever darkMode changes */
                this._watchDark();
            },

            _watchDark() {
                /* Alpine.effect runs reactively whenever darkMode changes */
                Alpine.effect(() => {
                    document.documentElement.classList.toggle('dark', this.darkMode);
                });
            },
        });
    });
</script>

{{ $slot }}