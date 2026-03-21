<x-layouts.split-auth title="Two Factor Authentication">
    <div x-data="{
        showRecoveryInput: @js($errors->has('recovery_code')),
        code: '',
        recovery_code: '',
        toggleInput() {
            this.showRecoveryInput = !this.showRecoveryInput;
            this.code = '';
            this.recovery_code = '';
            $dispatch('clear-2fa-auth-code');
            $nextTick(() => {
                this.showRecoveryInput
                    ? this.$refs.recovery_code?.focus()
                    : this.$refs.code?.focus();
            });
        }
    }">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900 border-none">
                <span x-show="!showRecoveryInput">{{ __('Enter Verification Code') }}</span>
                <span x-show="showRecoveryInput">{{ __('Recovery Code') }}</span>
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                <span x-show="!showRecoveryInput">{{ __('Enter the authentication code provided by your authenticator application.') }}</span>
                <span x-show="showRecoveryInput">{{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}</span>
            </p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-6">
            @csrf

            <!-- OTP Input -->
             <div x-show="!showRecoveryInput" class="space-y-4">
                <div>
                     <label for="code" class="block text-sm font-medium text-gray-700 mb-1 text-center">
                        {{ __('Authentication Code') }}
                    </label>
                    <input
                        id="code"
                        type="text"
                        inputmode="numeric"
                        x-ref="code"
                        x-model="code"
                        name="code"
                        class="appearance-none block w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all text-center tracking-[1em] text-xl font-mono"
                        placeholder="••••••"
                        autocomplete="one-time-code"
                        maxlength="6"
                        autofocus
                    />
                </div>
                 
                 <!-- Resend Code Link -->
                 <div class="text-sm text-center pt-2">
                    <span class="text-gray-500">Didn't receive code?</span>
                    <a href="#" class="font-medium text-green-600 hover:text-green-500 transition-colors ml-1">
                        {{ __('Resend Code') }}
                    </a>
                 </div>
            </div>

            <!-- Recovery Code Input -->
            <div x-show="showRecoveryInput">
                <label for="recovery_code" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Recovery Code') }}</label>
                <input
                    id="recovery_code"
                    type="text"
                    name="recovery_code"
                    x-ref="recovery_code"
                    x-bind:required="showRecoveryInput"
                    autocomplete="one-time-code"
                    x-model="recovery_code"
                    class="appearance-none block w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 transition-all sm:text-sm"
                />
                 @error('recovery_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Verify Button -->
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                {{ __('Verify Code') }}
            </button>

             <!-- Toggle Link for Recovery Code -->
            <div class="mt-4 text-center">
                 <button type="button" @click="toggleInput()" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors bg-transparent border-none cursor-pointer">
                    <span x-show="!showRecoveryInput">{{ __('Use recovery code') }}</span>
                    <span x-show="showRecoveryInput">{{ __('Use authentication code') }}</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.split-auth>
