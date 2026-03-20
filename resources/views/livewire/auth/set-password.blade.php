<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900 border-none">Finish Account Setup</h2>
        <p class="mt-2 text-sm text-gray-600">
            You've been invited to join the team! Please set a secure password to activate your account.
        </p>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="space-y-5">
        
        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <div x-data="{ show: false }" class="relative">
                <input wire:model="password" id="password" :type="show ? 'text' : 'password'" required placeholder="••••••••"
                    class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm pr-10">
                
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 z-50 flex items-center pr-3 text-gray-400 hover:text-green-600 transition-colors focus:outline-none cursor-pointer"
                    title="Toggle password visibility">
                    <svg x-show="!show" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <div x-data="{ show: false }" class="relative">
                <input wire:model="password_confirmation" id="password_confirmation" :type="show ? 'text' : 'password'" required placeholder="••••••••"
                    class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm pr-10">
                
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 z-50 flex items-center pr-3 text-gray-400 hover:text-green-600 transition-colors focus:outline-none cursor-pointer"
                    title="Toggle password visibility">
                    <svg x-show="!show" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Specialties -->
        @if ($this->isOperator && $this->categories->count() > 0)
             <div class="text-left" x-data="{ selected: @entangle('selectedSpecialties').live }">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Your Specialties (optional)
                </label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($this->categories as $category)
                        <label wire:key="cat-{{ $category->id }}"
                            class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition select-none"
                            :class="selected.includes({{ $category->id }}) || selected.includes('{{ $category->id }}') ?
                                'border-green-600 bg-green-50 ring-1 ring-green-600' :
                                'border-gray-200 bg-white hover:border-green-400 hover:bg-green-50/30'">

                            <input type="checkbox" value="{{ $category->id }}" x-model="selected"
                                class="sr-only">

                            <span class="flex items-center justify-center w-5 h-5 rounded border transition"
                                :class="selected.includes({{ $category->id }}) || selected.includes('{{ $category->id }}') ?
                                        'bg-green-600 border-green-600' :
                                        'border-gray-300 bg-white'">
                                <svg x-show="selected.includes({{ $category->id }}) || selected.includes('{{ $category->id }}')"
                                    class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium"
                                :class="selected.includes({{ $category->id }}) || selected.includes('{{ $category->id }}') ? 'text-green-800' : 'text-gray-700'">
                                {{ $category->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                 <p class="mt-2 text-xs text-gray-500">
                    Select the categories you specialize in. Matching tickets will be automatically assigned to you.
                </p>
                @error('selectedSpecialties')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif


        <!-- Submit Button -->
        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
             <span wire:loading.remove wire:target="save">Set Password & Login</span>
            <span wire:loading wire:target="save" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Activating...
            </span>
        </button>
    </form>
</div>
