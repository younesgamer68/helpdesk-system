<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

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