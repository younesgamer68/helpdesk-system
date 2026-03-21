<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'HelpDesk') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @livewireStyles
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-float-delayed {
            animation: float 8s ease-in-out infinite reverse;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 h-full" x-data="{ show: true }">
    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- Left Side: Branding & Illustration -->
        <div class="hidden lg:flex lg:w-1/2 bg-gray-50 dark:bg-gray-100 items-center justify-center p-12 relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute inset-0 bg-grid-gray-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:bg-grid-gray-700/25 dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]"></div>
            
            <!-- Abstract Shapes (Animated) -->
            <div 
                class="absolute -top-24 -right-24 w-96 h-96 bg-green-200/40 rounded-full blur-3xl animate-float" 
                x-show="show"
                x-transition:enter="transition ease-out duration-[2000ms]"
                x-transition:enter-start="opacity-0 scale-50"
                x-transition:enter-end="opacity-100 scale-100"
            ></div>
            <div 
                class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-200/40 rounded-full blur-3xl animate-float-delayed"
                x-show="show"
                x-transition:enter="transition ease-out duration-[2000ms] delay-300"
                x-transition:enter-start="opacity-0 scale-50"
                x-transition:enter-end="opacity-100 scale-100"
            ></div>

            <div 
                class="relative z-10 w-full max-w-lg text-center lg:text-left flex flex-col h-full justify-between"
                x-show="show"
                x-transition:enter="transition ease-out duration-1000"
                x-transition:enter-start="opacity-0 -translate-x-10"
                x-transition:enter-end="opacity-100 translate-x-0"
            >
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logolm.png') }}" class="h-10 w-auto" alt="HelpDesk Logo">
                    <span class="text-xl font-bold tracking-tight text-gray-900">HelpDesk</span>
                </div>

                <!-- Hero Content -->
                <div class="my-auto">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl mb-6">
                        Support customers <br>
                        <span class="text-green-600">better & faster.</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Streamline your support workflow with our modern helpdesk solution. 
                        Join thousands of companies delivering exceptional customer service.
                    </p>
                    
                    <!-- Feature Points -->
                    <ul class="space-y-4 text-gray-600 hidden xl:block">
                        <li class="flex items-center gap-3 group">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors duration-300 transform group-hover:scale-110">
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="group-hover:text-gray-900 transition-colors duration-300">AI-powered automated responses</span>
                        </li>
                        <li class="flex items-center gap-3 group">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors duration-300 transform group-hover:scale-110">
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="group-hover:text-gray-900 transition-colors duration-300">Real-time chat & collaboration</span>
                        </li>
                        <li class="flex items-center gap-3 group">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors duration-300 transform group-hover:scale-110">
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="group-hover:text-gray-900 transition-colors duration-300">Detailed analytics & insights</span>
                        </li>
                    </ul>
                </div>

                <!-- Copyright -->
                <div class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} HelpDesk Inc. All rights reserved.
                </div>
            </div>
            
        </div>

        <!-- Right Side: Auth Form -->
        <div class="flex-1 flex flex-col justify-center px-4 py-12 sm:px-6 lg:px-20 xl:px-24 bg-white relative">
            <!-- Mobile Header Logo -->
            <div class="lg:hidden absolute top-6 left-6 flex items-center gap-2">
                <img src="{{ asset('images/logolm.png') }}" class="h-8 w-auto" alt="HelpDesk Logo">
                <span class="text-lg font-bold tracking-tight text-gray-900">HelpDesk</span>
            </div>

            <div 
                class="mx-auto w-full max-w-sm lg:w-96"
                x-show="show"
                x-transition:enter="transition ease-out duration-700 delay-150"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                {{ $slot }}
            </div>
        </div>
    </div>

    <script>
        function togglePassword(button) {
            const container = button.closest('.relative');
            const input = container.querySelector('input');
            const showIcon = button.querySelector('.icon-show');
            const hideIcon = button.querySelector('.icon-hide');
            
            if (input.type === 'password') {
                input.type = 'text';
                showIcon.style.display = 'none';
                hideIcon.style.display = 'block';
            } else {
                input.type = 'password';
                showIcon.style.display = 'block';
                hideIcon.style.display = 'none';
            }
        }
    </script>
    
    @livewireScripts
</body>
</html>
