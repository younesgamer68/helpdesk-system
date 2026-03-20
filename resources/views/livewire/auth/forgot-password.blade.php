<x-layouts.split-auth title="Reset Password">

    <!-- Header -->
    <div class="mb-8">
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900 border-none">Reset your password</h2>
        <p class="mt-2 text-sm text-gray-600">
            Enter your email address and we'll send you a link to reset your password.
        </p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-4 rounded-lg border border-green-200 flex items-center gap-2">
            <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        
        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" required autofocus
                value="{{ old('email') }}"
                class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm">
            @error('email')
                <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" :disabled="loading" class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed transform active:scale-[0.98]">
            <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-text="loading ? 'Sending link...' : 'Send reset link'">Send reset link</span>
        </button>

        <!-- Back to Login -->
        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-green-600 transition-colors group">
                <svg class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to log in
            </a>
        </div>
    </form>

</x-layouts.split-auth>
