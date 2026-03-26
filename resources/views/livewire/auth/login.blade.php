<x-layouts.split-auth title="Sign in">
    
    <!-- Header -->
    <div class="mb-8">
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900 border-none">Welcome back</h2>
        <p class="mt-2 text-sm text-gray-600">
            Don't have an account? 
            <a href="{{ route('register') }}" class="font-medium text-green-600 hover:text-green-500 transition-colors underline">Create your account</a>
        </p>
    </div>

    <!-- Social Login -->
    <div class="space-y-4">
        <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-gray-200 rounded-lg shadow-sm bg-white hover:bg-gray-100 text-gray-700 font-medium transition-all duration-200 group">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            <span class="group-hover:text-gray-900">Sign in with Google</span>
        </a>
    </div>

    <!-- Divider -->
    <div class="relative my-8">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="px-2 bg-white text-sm text-gray-400 font-medium">OR</span>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        
        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" required autofocus
                value="{{ old('email') }}"
                class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm pr-10">
                <button type="button" 
                    onclick="togglePassword(this)"
                    class="absolute inset-y-0 right-0 z-50 flex items-center pr-3 text-gray-400 hover:text-green-600 transition-colors focus:outline-none cursor-pointer"
                    title="Toggle password visibility">
                    <!-- Icon for "Show Password" (Eye) - Visible when password is HIDDEN -->
                    <svg class="h-5 w-5 pointer-events-none icon-show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <!-- Icon for "Hide Password" (Eye Off) - Hidden by default -->
                    <svg class="h-5 w-5 pointer-events-none icon-hide hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            @if (Route::has('password.request'))
                <div class="mt-2 text-right">
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-green-600 hover:text-green-500 transition-colors">
                        Forgot password?
                    </a>
                </div>
            @endif
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

 

        <!-- Submit Button -->
        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
            Sign in
        </button>
    </form>

</x-layouts.split-auth>
