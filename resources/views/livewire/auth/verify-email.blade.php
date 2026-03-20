<div class="min-h-screen bg-white flex flex-col">
    {{-- Back to login link --}}
    <div class="flex justify-end p-4">
        <a href="{{ route('login') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
            Back to login
        </a>
    </div>

    {{-- Main content --}}
    <div class="flex-1 flex items-center justify-center px-4">
        <div class="w-full max-w-md text-center">
            {{-- Title --}}
            <h1 class="text-3xl font-semibold text-gray-900 mb-8">Confirm your email</h1>

            {{-- Open Gmail button --}}
            <a href="https://mail.google.com" target="_blank"
                class="w-full inline-flex items-center justify-center gap-3 px-6 py-3 border border-gray-300 rounded-md bg-white text-gray-700 font-medium hover:bg-gray-50 transition mb-6">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Open Gmail
            </a>

            {{-- Divider --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="flex-1 border-t border-gray-300"></div>
                <span class="text-gray-500 text-sm">or</span>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>

            {{-- Email sent message --}}
            <p class="text-gray-600 mb-2">
                We sent a verification link to
            </p>
            <p class="font-semibold text-gray-900 mb-6">
                {{ auth()->user()->email }}
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-3 bg-green-50 border border-green-200 rounded-md">
                    <p class="text-green-700 text-sm">
                        A new verification link has been sent!
                    </p>
                </div>
            @endif

            {{-- Resend link --}}
            <p class="text-gray-600 text-sm mb-2">
                Didn't get an email? Check your <strong>spam folder</strong>
            </p>
            <form method="POST" action="{{ route('verification.send') }}" class="mb-8">
                @csrf
                <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium hover:underline">
                    or get a new verification link.
                </button>
            </form>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                    Log out
                </button>
            </form>
        </div>
    </div>

    {{-- Footer --}}
    <div class="p-6 text-center">
        <p class="text-gray-400 text-xs">
            powered by <span class="font-bold text-gray-600">helpdesk</span>
        </p>
    </div>
</div>
