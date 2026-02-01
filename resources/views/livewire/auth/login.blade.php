<x-layouts::auth>

    <div class="min-h-screen flex items-center justify-center px-4" style="background-color: #0A170F;">

        <!-- Header -->
        <div class="absolute top-0 left-0 right-0 px-6 py-4 flex justify-between items-center border-b border-white">

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 hover:opacity-80 transition">
                <img src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo" class="w-10 h-10 object-contain">
                <span class="text-white font-semibold text-xs">
                    HelpDesk
                </span>
            </a>

            <!-- Sign up button -->
            <a href="{{ route('register') }}"
                class="bg-white text-slate-900 px-6 py-2 rounded font-semibold text-sm hover:bg-gray-100 transition">
                Sign up
            </a>
        </div>

        <!-- Main Content -->
        <div class="w-full max-w-md text-center">

            <!-- Title -->
            <h1 class="text-white text-4xl font-light mb-12 mt-32">
                Get started It's free !
            </h1>

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- Business Email -->
                <div class="text-left">
                    <label class="text-white text-sm font-medium block mb-3">
                        Business email
                    </label>
                    <input 
                        name="email" 
                        type="email" 
                        required
                        autocomplete="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your business email"
                        class="w-full bg-transparent border border-white/30 text-white placeholder-white/40 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition"
                    >
                    @error('email')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="text-left">
                    <label class="text-white text-sm font-medium block mb-3">
                        Password
                    </label>
                    <input 
                        name="password" 
                        type="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full bg-transparent border border-white/30 text-white placeholder-white/40 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition"
                    >
                    @error('password')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full text-white font-semibold py-3 px-4 rounded-lg transition"
                    style="background-color:#0F766E;" 
                    onmouseover="this.style.backgroundColor='#0d6963'"
                    onmouseout="this.style.backgroundColor='#0F766E'"
                >
                    Log in with email
                </button>

            </form>

            <!-- Sign up Link -->
            <p class="text-white/60 text-sm mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-teal-400 hover:text-teal-300 font-semibold">
                    Sign up
                </a>
            </p>
        </div>

        <!-- Bottom Icon -->
        <div class="absolute bottom-6 right-6 bg-white p-2 rounded-lg shadow-lg">
            <svg class="w-6 h-6 text-slate-900" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-5 9h10v2H7z" />
            </svg>
        </div>

    </div>
</x-layouts::auth>