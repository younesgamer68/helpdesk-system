{{-- =====================================================================
Global UI State — Alpine.store('ui') for shared darkMode, lang, t()
Wrap your page content with <x-ui-state> ... </x-ui-state>
===================================================================== --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('ui', {
            darkMode: false,
            lang: 'English',
            loading: false,

            showLoading(ms = 800) {
                this.loading = true;
                setTimeout(() => { this.loading = false; }, ms);
            },

            /* -- i18n translation map -- */
            t(key) {
                const dict = {
                    English: {
                        /* Hero */
                        heroHeadline1: 'Deliver beautifully simple',
                        heroHeadline2: 'service with HelpDesk',
                        heroSubtitle: 'The preferred help desk for customer-first companies.',
                        heroPlaceholder: 'Enter your email',
                        heroTryFree: 'Try for free',
                        heroPrivacy: 'By submitting, I agree to HelpDesk\'s',
                        heroPrivacyLink: 'Privacy Notice',
                        heroInvalidEmail: 'Please enter a valid email address.',
                        heroThankYou: 'Thank you! Check your email.',
                        heroDashboard: 'Go to Dashboard',

                        /* Discover */
                        discoverBadge1: 'Users Love Us',
                        discoverBadge2: 'Happiest Users 2023',
                        discoverTitle: 'Discover HelpDesk',
                        discoverTab_ticketList: 'Ticket list',
                        discoverTab_ticketView: 'Ticket view',
                        discoverTab_automations: 'Automations',
                        discoverTab_reports: 'Reports',
                        discoverCta: 'Sign up free',

                        /* Support Heroes */
                        heroesTitle1: 'We\'re here for you',
                        heroesTitle2: '24/7/365',
                        heroesDescription: 'When you need assistance, you can count on our Support Heroes. They\'re strong, they\'re swift, and they\'ll help you no matter the time!',
                        heroesCta: 'Chat with us \uD83D\uDE0A',

                        /* Navbar */
                        products: 'Products',
                        solutions: 'Solutions',
                        resources: 'Resources',
                        pricing: 'Pricing',
                        tryFree: 'Sign up free',
                        viewDemo: 'Login',
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

                        /* Utility Bar */
                        utilitySignIn: 'Sign in',
                        utilityLogout: 'Logout',
                        utilityHelpCenter: 'Help Desk center',
                        utilityCompany: 'Company',
                        utilityContactUs: 'Contact us',

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
                        /* Hero */
                        heroHeadline1: 'Offrez un service',
                        heroHeadline2: 'simple et élégant avec HelpDesk',
                        heroSubtitle: 'Le helpdesk préféré des entreprises centrées sur le client.',
                        heroPlaceholder: 'Entrez votre e-mail',
                        heroTryFree: 'Essayer gratuitement',
                        heroPrivacy: 'En soumettant, j\'accepte la',
                        heroPrivacyLink: 'Politique de confidentialité',
                        heroInvalidEmail: 'Veuillez entrer une adresse e-mail valide.',
                        heroThankYou: 'Merci ! Vérifiez votre e-mail.',
                        heroDashboard: 'Aller au tableau de bord',

                        /* Discover */
                        discoverBadge1: 'Les utilisateurs nous adorent',
                        discoverBadge2: 'Utilisateurs les plus heureux 2023',
                        discoverTitle: 'Découvrez HelpDesk',
                        discoverTab_ticketList: 'Liste des tickets',
                        discoverTab_ticketView: 'Vue du ticket',
                        discoverTab_automations: 'Automatisations',
                        discoverTab_reports: 'Rapports',
                        discoverCta: 'Inscription gratuite',

                        /* Support Heroes */
                        heroesTitle1: 'Nous sommes l\u00e0 pour vous',
                        heroesTitle2: '24h/24, 7j/7, 365j/an',
                        heroesDescription: 'Quand vous avez besoin d\u2019aide, comptez sur nos H\u00e9ros du Support. Ils sont forts, rapides et vous aideront \u00e0 tout moment\u00a0!',
                        heroesCta: 'Discutez avec nous \uD83D\uDE0A',

                        /* Navbar */
                        products: 'Produits',
                        solutions: 'Solutions',
                        resources: 'Ressources',
                        pricing: 'Tarifs',
                        tryFree: 'Inscription gratuite',
                        viewDemo: 'Connexion',
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

                        /* Utility Bar */
                        utilitySignIn: 'Se connecter',
                        utilityLogout: 'Se déconnecter',
                        utilityHelpCenter: 'Centre d\'aide',
                        utilityCompany: 'Entreprise',
                        utilityContactUs: 'Nous contacter',

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