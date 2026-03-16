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
        <!-- Sign In button -->
        <a href="{{ route('login') }}"
            class="bg-white text-slate-900 px-6 py-2 rounded font-semibold text-sm hover:bg-gray-100 transition">
            Sign In
        </a>
    </div>

    <!-- Main Content -->
    <div class="w-full max-w-md text-center">
        <!-- Title -->
        <h1 class="text-white text-3xl font-light mb-4 mt-32">
            Finish Account Setup
        </h1>
        <p class="text-white/70 mb-10">
            You've been invited to join the team! Please set a secure password to activate your account.
        </p>

        <!-- Form -->
        <form wire:submit="save" class="space-y-6">
            <!-- Password -->
            <div class="text-left">
                <label class="text-white text-sm font-medium block mb-3">
                    New Password
                </label>
                <input wire:model="password" type="password" required placeholder="••••••••"
                    class="w-full bg-transparent border border-white/30 text-white placeholder-white/40 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition">
                @error('password')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="text-left">
                <label class="text-white text-sm font-medium block mb-3">
                    Confirm Password
                </label>
                <input wire:model="password_confirmation" type="password" required placeholder="••••••••"
                    class="w-full bg-transparent border border-white/30 text-white placeholder-white/40 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition">
            </div>

            <!-- Specialty Selection (for operators only) -->
            @if ($this->isOperator && $this->categories->count() > 0)
                <div class="text-left">
                    <label class="text-white text-sm font-medium block mb-3">
                        Your Specialties (optional)
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($this->categories as $category)
                            <label wire:key="cat-{{ $category->id }}"
                                class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition
                                        {{ in_array($category->id, $selectedSpecialties) ? 'border-teal-400 bg-teal-400/10' : 'border-white/20 bg-white/5 hover:border-white/40' }}">
                                <input type="checkbox" value="{{ $category->id }}" wire:model="selectedSpecialties"
                                    class="sr-only">
                                <span
                                    class="flex items-center justify-center w-5 h-5 rounded border transition
                                        {{ in_array($category->id, $selectedSpecialties) ? 'bg-teal-500 border-teal-500' : 'border-white/40 bg-transparent' }}">
                                    @if (in_array($category->id, $selectedSpecialties))
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </span>
                                <span class="text-sm text-white font-medium">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-white/50 text-xs mt-2">
                        Select the categories you specialize in. Matching tickets will be automatically assigned to you.
                    </p>
                    @error('selectedSpecialties')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Submit Button -->
            <button type="submit" class="w-full text-white font-semibold py-3 px-4 rounded-lg transition"
                style="background-color:#0F766E;" onmouseover="this.style.backgroundColor='#0d6963'"
                onmouseout="this.style.backgroundColor='#0F766E'">
                <span wire:loading.remove wire:target="save">Set Password & Login</span>
                <span wire:loading wire:target="save">Activating...</span>
            </button>
        </form>
    </div>

    <!-- Bottom Icon -->
    <div class="absolute bottom-6 right-6 bg-white p-2 rounded-lg shadow-lg">
        <svg class="w-6 h-6 text-slate-900" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-5 9h10v2H7z" />
        </svg>
    </div>

</div>
</div>
