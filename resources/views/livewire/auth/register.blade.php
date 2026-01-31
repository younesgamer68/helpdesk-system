<x-layouts::auth>
        {{-- ÉTAPE 1 : Formulaire Email uniquement --}}
        {{-- <div class="min-h-screen flex items-center justify-center px-4" style="background-color: #0A170F;">

            <!-- Header -->
            <div class="absolute top-0 left-0 right-0 px-6 py-4 flex justify-between items-center border-b border-white">
                
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 hover:opacity-80 transition">
                    <img 
                        src="{{ asset('images/Logo.png') }}" 
                        alt="HelpDesk Logo" 
                        class="w-10 h-10 object-contain"
                    >
                    <span class="text-white font-semibold text-sm">
                        HelpDesk
                    </span>
                </a>

                <!-- Login button -->
                <a href="{{ route('login') }}"
                   class="bg-white text-slate-900 px-6 py-2 rounded font-semibold text-sm hover:bg-gray-100 transition">
                    Log in 
                </a>
            </div>

            <!-- Main Content -->
            <div class="w-full max-w-md text-center">

                <!-- Title -->
                <h1 class="text-white text-4xl font-light mb-12 mt-32">
                    Get started It's free !
                </h1>

                <!-- Google Button -->
                <button type="button"
                    class="w-full bg-white text-gray-800 py-3 px-4 rounded-lg font-semibold flex items-center justify-center gap-3 mb-8 hover:bg-gray-50 transition shadow-md">

                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>

                    Sign up with Google
                </button>

                <!-- Divider -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="flex-1 h-px bg-white/40"></div>
                    <span class="text-white/60 text-sm">or</span>
                    <div class="flex-1 h-px bg-white/40"></div>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="submitEmail" class="space-y-6">

                    <div class="text-left">
                        <label class="text-white text-sm font-medium block mb-3">
                            Business email
                        </label>

                        <input 
                            type="email"
                            wire:model="email"
                            placeholder="Enter your business email"
                            class="w-full bg-transparent border border-white/30 text-white placeholder-white/40 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition"
                        >
                        @error('email')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <button 
                        type="submit"
                        class="w-full text-white font-semibold py-3 px-4 rounded-lg transition"
                        style="background-color:#0F766E;"
                        onmouseover="this.style.backgroundColor='#0d6963'"
                        onmouseout="this.style.backgroundColor='#0F766E'">
                        Sign up with email
                    </button>

                </form>

                <!-- Login -->
                <p class="text-white/60 text-sm mt-6">
                    Already have an account?
                    <a href="{{ route('register') }}" class="text-teal-400 hover:text-teal-300 font-semibold">
                        Log in
                    </a>
                </p>
            </div>

            <!-- Bottom Icon -->
            <div class="absolute bottom-6 right-6 bg-white p-2 rounded-lg shadow-lg">
                <svg class="w-6 h-6 text-slate-900" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-5 9h10v2H7z"/>
                </svg>
            </div>

        </div> --}}

        {{-- ÉTAPE 2 : Formulaire complet avec email pré-rempli --}}
        <div class="min-h-screen bg-[#0A170F] flex items-center justify-center px-4">
            <!-- Header avec Logo et Login Button -->
            <div class="absolute top-0 left-0 right-0 px-6 py-4 flex justify-between items-center border-b border-white">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 hover:opacity-80 transition">
                    <!-- LOGO IMAGE -->
                    <img 
                        src="{{ asset('images/logodm.png') }}" 
                        alt="HelpDesk Logo"g
                        class="w-10 h-10 object-contain"
                    >
                    <span class="text-white font-semibold text-sm">Helpdesk</span>
                </a>
                <a href="{{ route('login') }}" class="bg-white text-[#0A170F] px-6 py-2 rounded font-semibold text-sm hover:bg-gray-100 transition">
                    Log in
                </a>
            </div>

            <!-- Main Content -->
            <div class="w-full max-w-md text-center">
                <!-- Heading -->
                <h1 class="text-white text-4xl font-light mb-12 mt-32">
                    Get started It's free !
                </h1>

                

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf
                    <!-- Company Name -->
                    <div class="text-left">
                        <label class="text-gray-300 text-sm font-medium block mb-2">
                            Company Name
                        </label>
                        <input 
                            name="name"
                            :label="__('Name')"
                            :value="old('name')"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Acme"
                            class="w-full bg-[#0A170F] border border-slate-700 text-white placeholder-gray-500 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                        >
                        @error('company_name')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Business Email (pré-rempli et désactivé) -->
                    <div class="text-left">
                        <label class="text-gray-300 text-sm font-medium block mb-2">
                            Business email
                        </label>
                        <input 
                           name="email"
                        :label="__('Email address')"
                        :value="old('email')"
                        type="email"
                        required
                        autocomplete="email"
                            class="w-full bg-[#0A170F] border border-slate-700 text-gray-400 py-3 px-4 rounded-lg cursor-not-allowed opacity-70"
                        >
                        <p class="text-gray-500 text-xs mt-1">Email cannot be changed</p>
                        @error('email')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="text-left">
                        <label class="text-gray-300 text-sm font-medium block mb-2">
                            Password
                        </label>
                        <input 
                                    name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full bg-[#0A170F] border border-slate-700 text-white placeholder-gray-500 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                        >
                        @error('password')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="text-left">
                        <label class="text-gray-300 text-sm font-medium block mb-2">
                            Confirm Password
                        </label>
                        <input 
                            name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full bg-[#0A170F] border border-slate-700 text-white placeholder-gray-500 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                        >
                        @error('password_confirmation')
                            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 mt-6"
                    >
                        Create Account +
                    </button>
                </form>

                <!-- Login Link -->
                <p class="text-gray-400 text-sm mt-6">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="text-teal-400 hover:text-teal-300 font-semibold">
                        Log in
                    </a>
                </p>
            </div>

            <!-- Bottom Right Icon -->
            <div class="absolute bottom-6 right-6 bg-white p-2 rounded-lg shadow-lg">
                <svg class="w-6 h-6 text-[#0A170F]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5-9h10v2H7z"/>
                </svg>
            </div>
        </div>
    
</x-layouts::auth>