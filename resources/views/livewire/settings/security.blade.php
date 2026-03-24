<section class="w-full">
    @include('partials.settings-heading')

    <x-app.settings.layout :heading="__('Security')" :subheading="__('Manage your password and two-factor authentication')">
        <div class="space-y-10">

            {{-- Change Password --}}
            <div>
                <flux:subheading class="mb-4">{{ __('Change Password') }}</flux:subheading>
                <form wire:submit="updatePassword" class="space-y-6">
                    @if (Auth::user()->password)
                        <flux:input wire:model="current_password" :label="__('Current Password')" type="password" required
                            autocomplete="current-password" />
                    @endif
                    <flux:input wire:model="password" :label="__('New Password')" type="password" required
                        autocomplete="new-password" />
                    <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password"
                        required autocomplete="new-password" />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Update Password') }}</flux:button>
                        <x-ui.action-message on="password-updated">{{ __('Saved.') }}</x-ui.action-message>
                    </div>
                </form>
            </div>

            <flux:separator />

            {{-- Two-Factor Authentication --}}
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
                <div wire:cloak>
                    <flux:subheading class="mb-1">{{ __('Two-Factor Authentication') }}</flux:subheading>
                    <flux:text size="sm" class="mb-4 text-zinc-500">
                        {{ __('Add an extra layer of security to your account by requiring a TOTP code (e.g. Google Authenticator) when you log in.') }}
                    </flux:text>

                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <flux:badge color="green">{{ __('Enabled') }}</flux:badge>
                            </div>
                            <flux:text>
                                {{ __('Two-factor authentication is enabled. You will be asked for a TOTP code when logging in.') }}
                            </flux:text>
                            <livewire:settings.two-factor.recovery-codes :requiresConfirmation="$requiresConfirmation" />
                            <div>
                                <flux:button variant="primary" class="!bg-emerald-500 hover:!bg-emerald-600" icon="shield-exclamation" icon:variant="outline"
                                    wire:click="disable">
                                    {{ __('Disable 2FA') }}
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <flux:badge color="red">{{ __('Disabled') }}</flux:badge>
                            </div>
                            <flux:text variant="subtle">
                                {{ __('Enable two-factor authentication to add a TOTP code requirement from apps like Google Authenticator or Authy.') }}
                            </flux:text>
                            <flux:button variant="primary" icon="shield-check" icon:variant="outline"
                                wire:click="enable">
                                {{ __('Enable 2FA') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-app.settings.layout>

    {{-- 2FA Setup Modal --}}
    <flux:modal name="two-factor-setup-modal" class="max-w-md md:min-w-md" @close="closeModal" wire:model="showModal">
        <div class="space-y-6">
            <div class="flex flex-col items-center space-y-4">
                <div
                    class="p-0.5 w-auto rounded-full border border-stone-100 dark:border-stone-600 bg-white dark:bg-stone-800 shadow-sm">
                    <div
                        class="p-2.5 rounded-full border border-stone-200 dark:border-stone-600 overflow-hidden bg-stone-100 dark:bg-stone-200 relative">
                        <div
                            class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                            @for ($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>
                        <div
                            class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                            @for ($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>
                        <flux:icon.qr-code class="relative z-20 dark:text-accent-foreground" />
                    </div>
                </div>
                <div class="space-y-2 text-center">
                    <flux:heading size="lg">{{ __('Set up Authenticator App') }}</flux:heading>
                    <flux:text>{{ __('Scan the QR code below with Google Authenticator, Authy, or any TOTP app.') }}
                    </flux:text>
                </div>
            </div>

            @if ($showVerificationStep)
                <div class="space-y-6">
                    <div class="flex flex-col items-center space-y-3 justify-center">
                        <flux:otp name="code" wire:model="code" length="6" label="OTP Code" label:sr-only
                            class="mx-auto" />
                    </div>
                    <div class="flex items-center space-x-3">
                        <flux:button variant="outline" class="flex-1" wire:click="resetVerification">
                            {{ __('Back') }}</flux:button>
                        <flux:button variant="primary" class="flex-1" wire:click="confirmTwoFactor"
                            x-bind:disabled="$wire.code.length < 6">{{ __('Confirm') }}</flux:button>
                    </div>
                </div>
            @else
                @error('setupData')
                    <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
                @enderror

                <div class="flex justify-center">
                    <div
                        class="relative w-64 overflow-hidden border rounded-lg border-stone-200 dark:border-stone-700 aspect-square">
                    @empty($qrCodeSvg)
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-white dark:bg-stone-700 animate-pulse">
                            <flux:icon.loading />
                        </div>
                    @else
                        <div x-data class="flex items-center justify-center h-full p-4">
                            <div class="bg-white p-3 rounded"
                                :style="($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)) ?
                                'filter: invert(1) brightness(1.5)' : ''">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                    @endempty
                </div>
            </div>

            <div>
                <flux:button :disabled="$errors->has('setupData')" variant="primary" class="w-full"
                    wire:click="showVerificationIfNecessary">
                    {{ __('Next') }}
                </flux:button>
            </div>

            <div class="space-y-4">
                <div class="relative flex items-center justify-center w-full">
                    <div class="absolute inset-0 w-full h-px top-1/2 bg-stone-200 dark:bg-stone-600"></div>
                    <span
                        class="relative px-2 text-sm bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                        {{ __('or, enter the code manually') }}
                    </span>
                </div>
                <div class="flex items-center space-x-2" x-data="{
                    copied: false,
                    async copy() {
                        try {
                            await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                            this.copied = true;
                            setTimeout(() => this.copied = false, 1500);
                        } catch (e) {}
                    }
                }">
                    <div class="flex items-stretch w-full border rounded-xl dark:border-stone-700">
                    @empty($manualSetupKey)
                        <div class="flex-1 h-10 rounded-xl animate-pulse bg-white dark:bg-stone-700"></div>
                    @else
                        <p class="flex-1 font-mono text-sm px-3 py-2 tracking-wider break-all">
                            {{ $manualSetupKey }}</p>
                        <button type="button" @click="copy"
                            class="flex items-center justify-center border-l dark:border-stone-700 px-3 hover:bg-stone-50 dark:hover:bg-stone-700 rounded-r-xl transition-colors">
                            <flux:icon x-show="!copied" name="clipboard-document" class="size-4 text-stone-500" />
                            <flux:icon x-show="copied" name="check" class="size-4 text-green-500" />
                        </button>
                    @endempty
                </div>
            </div>
        </div>
    @endif
</div>
</flux:modal>
</section>
