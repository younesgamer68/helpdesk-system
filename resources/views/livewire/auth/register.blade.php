<x-layouts::auth>
    <div class="min-h-screen bg-white dark:bg-zinc-950 flex flex-col">

        {{-- Top bar --}}
        <header class="flex items-center justify-between px-6 sm:px-10 py-4 border-b border-zinc-100 dark:border-zinc-800/60 shrink-0">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline">
                <img src="{{ asset('images/logodm.png') }}" alt="Helpdesk" class="w-8 h-8">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">Helpdesk</span>
            </a>
            <a href="{{ route('login') }}"
                class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors no-underline">
                Already have an account?&nbsp;<span class="font-semibold text-teal-600 dark:text-teal-400">Log in</span>
            </a>
        </header>

        {{-- Main --}}
        <main class="flex flex-1 items-center justify-center py-12 px-4">
            <div class="w-full max-w-sm">

                {{-- Heading --}}
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">Create an account</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Get started — it's free</p>
                </div>

                {{-- Flash errors --}}
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-950/30 px-4 py-3">
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Google OAuth --}}
                <a href="{{ route('auth.google') }}"
                    class="flex w-full items-center justify-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-200 shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors no-underline">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22z" fill="#FBBC05" />
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                    </svg>
                    Sign up with Google
                </a>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-zinc-200 dark:border-zinc-800"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white dark:bg-zinc-950 px-3 text-zinc-400 dark:text-zinc-500">or sign up with email</span>
                    </div>
                </div>

                {{-- Register form --}}
                <form method="POST" action="{{ route('register.quick') }}" id="signup-form" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="emailInput" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Business email</label>
                        <input id="emailInput" name="email" type="email" autocomplete="email"
                            placeholder="you@company.com" value="{{ old('email') }}"
                            class="block w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                        @error('email')
                            <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p id="emailError" class="text-red-500 dark:text-red-400 text-xs mt-1 hidden"></p>
                    </div>

                    <button type="submit" id="signupBtn"
                        class="mt-2 w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-950 transition-colors flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span id="btnText">Sign up with email</span>
                        <svg id="btnSpinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-zinc-500 dark:text-zinc-400">
                    By signing up, you agree to our
                    <span class="text-teal-600 dark:text-teal-400">Terms of Service</span>
                    and
                    <span class="text-teal-600 dark:text-teal-400">Privacy Policy</span>.
                </p>

                <p class="mt-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-teal-600 dark:text-teal-400 hover:underline no-underline">
                        Log in
                    </a>
                </p>

            </div>
        </main>

    </div>

    <script>
        document.getElementById('signup-form').addEventListener('submit', function (e) {
            const email = document.getElementById('emailInput').value.trim();
            const error = document.getElementById('emailError');
            const btn   = document.getElementById('signupBtn');
            const text  = document.getElementById('btnText');
            const spin  = document.getElementById('btnSpinner');

            if (!email) {
                e.preventDefault();
                error.textContent = 'Please enter your email address.';
                error.classList.remove('hidden');
                return;
            }

            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(email)) {
                e.preventDefault();
                error.textContent = 'Please enter a valid email address.';
                error.classList.remove('hidden');
                return;
            }

            error.classList.add('hidden');
            btn.disabled = true;
            text.textContent = 'Creating account\u2026';
            spin.classList.remove('hidden');
        });
    </script>
</x-layouts::auth>
