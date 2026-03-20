<div>
    
    <!-- Header -->
    <div class="mb-8">
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900 border-none">Almost there!</h2>
        <p class="mt-2 text-sm text-gray-600">
            Complete your profile to get started.
        </p>
    </div>

    <!-- Form -->
    <form wire:submit="submit" class="space-y-5">
        
        <!-- Full Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input wire:model="name" type="text" id="name" required autofocus autocomplete="name"
                placeholder="Enter your full name"
                class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Company Name -->
        <div>
            <label for="companyName" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
            <input wire:model="companyName" type="text" id="companyName" required autocomplete="organization"
                placeholder="Enter your company name"
                class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm">
            @error('companyName')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" 
            wire:loading.attr="disabled"
            wire:loading.class="opacity-75 cursor-not-allowed"
            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
            <span wire:loading.remove>Continue to Dashboard</span>
            <span wire:loading>Setting up...</span>
        </button>

        <!-- Footer Info -->
        <div class="text-center mt-4 text-xs text-gray-400">
            Welcome, {{ auth()->user()->name }}! You're signed in with {{ auth()->user()->email }}
        </div>
    </form>

</div>
