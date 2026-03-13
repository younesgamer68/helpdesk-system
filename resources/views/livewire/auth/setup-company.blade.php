<div class="min-h-screen bg-white dark:bg-zinc-900 flex items-center justify-center px-4">

    <!-- ====== HEADER ====== -->
    <div
        class="absolute top-0 left-0 right-0 px-6 py-4 flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700">
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 hover:opacity-80 transition">
            <img src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo" class="w-10 h-10 object-contain">
            <span class="text-zinc-900 dark:text-zinc-100 font-semibold text-sm">HelpDesk</span>
        </a>
    </div>

    <!-- ====== MAIN CONTENT ====== -->
    <div class="w-full max-w-md text-center">

        <h1 class="text-zinc-900 dark:text-zinc-100 text-4xl font-light mb-4 mt-32">
            Almost there!
        </h1>

        <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-8">
            Complete your profile to get started.
        </p>

        <!-- Company Setup Form -->
        <form wire:submit="submit" class="space-y-6">

            <!-- Full Name -->
            <div class="text-left">
                <label class="text-zinc-900 dark:text-zinc-100 text-sm font-medium block mb-3">
                    Full Name
                </label>
                <input wire:model="name" type="text" required autofocus autocomplete="name"
                    placeholder="Enter your full name"
                    class="w-full bg-transparent border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 dark:placeholder-zinc-400 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition">
                @error('name')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Company Name -->
            <div class="text-left">
                <label class="text-zinc-900 dark:text-zinc-100 text-sm font-medium block mb-3">
                    Company Name
                </label>
                <input wire:model="companyName" type="text" required autocomplete="organization"
                    placeholder="Enter your company name"
                    class="w-full bg-transparent border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 dark:placeholder-zinc-400 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition">
                @error('companyName')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full text-zinc-900 dark:text-zinc-100 font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2"
                style="background-color:#0F766E;" onmouseover="this.style.backgroundColor='#0d6963'"
                onmouseout="this.style.backgroundColor='#0F766E'" wire:loading.attr="disabled"
                wire:loading.class="opacity-75 cursor-not-allowed">
                <span wire:loading.remove>Continue to Dashboard</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-zinc-400 dark:text-zinc-500"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Setting up...
                </span>
            </button>
        </form>

        <!-- Welcome message -->
        <p class="text-zinc-500 dark:text-zinc-400 text-xs mt-8">
            Welcome, {{ auth()->user()->name }}! You're signed in with {{ auth()->user()->email }}
        </p>
    </div>
</div>
