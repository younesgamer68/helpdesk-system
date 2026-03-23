<section class="w-full">
    @include('partials.settings-heading')

    <x-app.settings.layout :heading="__('Email Configuration')" :subheading="__('Configure your SMTP server and customise the emails sent to customers')" maxWidth="max-w-2xl">
        <form wire:submit="save" class="my-6 w-full space-y-8">

            {{-- SMTP Settings --}}
            <div class="space-y-5">
                <div>
                    <flux:heading size="sm">{{ __('SMTP Server') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('Leave blank to use the system default mailer.') }}</flux:text>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="smtpHost" :label="__('SMTP Host')" type="text"
                            placeholder="smtp.example.com" />
                    </div>
                    <flux:input wire:model="smtpPort" :label="__('Port')" type="number" placeholder="587" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="smtpUsername" :label="__('Username')" type="text"
                        placeholder="your@email.com" />
                    <flux:input wire:model="smtpPassword" :label="__('Password')" type="password"
                        placeholder="{{ __('Leave blank to keep current') }}" />
                </div>

                <div>
                    <flux:label>{{ __('Encryption') }}</flux:label>
                    <flux:dropdown>
                        <button type="button" class="mt-1 w-full flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-800 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <span class="truncate">
                                @php
                                    $encryptionLabels = [
                                        'tls' => 'TLS (recommended)',
                                        'ssl' => 'SSL',
                                        'starttls' => 'STARTTLS',
                                        'none' => 'None',
                                    ];
                                @endphp
                                {{ $encryptionLabels[$smtpEncryption] ?? 'TLS (recommended)' }}
                            </span>
                            <svg class="h-4 w-4 ml-2 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <flux:menu class="w-[250px]">
                            <flux:menu.radio.group wire:model="smtpEncryption">
                                <flux:menu.radio value="tls" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">TLS (recommended)</flux:menu.radio>
                                <flux:menu.radio value="ssl" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">SSL</flux:menu.radio>
                                <flux:menu.radio value="starttls" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">STARTTLS</flux:menu.radio>
                                <flux:menu.radio value="none" class="text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">None</flux:menu.radio>
                            </flux:menu.radio.group>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>

            <flux:separator />

            {{-- Sender Identity --}}
            <div class="space-y-5">
                <div>
                    <flux:heading size="sm">{{ __('Sender Identity') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('The name and address customers see in their inbox.') }}</flux:text>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="fromName" :label="__('From Name')" type="text"
                        placeholder="{{ Auth::user()->company->name }}" />
                    <flux:input wire:model="fromEmail" :label="__('From Email')" type="email"
                        placeholder="{{ Auth::user()->company->email }}" />
                </div>
            </div>

            <flux:separator />

            {{-- Email Template --}}
            <div class="space-y-5">
                <div>
                    <flux:heading size="sm">{{ __('Email Template') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('Customise how notification emails appear to customers.') }}</flux:text>
                </div>

                <flux:input wire:model="mailSubjectPrefix" :label="__('Subject Prefix')" type="text"
                    placeholder="[Support]"
                    description="{{ __('Prepended to every outgoing email subject line.') }}" />

                <div>
                    <flux:label>{{ __('Email Footer Text') }}</flux:label>
                    <textarea wire:model="mailFooterText" rows="3"
                        placeholder="{{ __('e.g. © 2026 Acme Inc. · 123 Main St, Suite 100') }}"
                        class="mt-1 w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    @error('mailFooterText')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <flux:text size="sm" class="mt-1 text-zinc-500">
                        {{ __('Appears at the bottom of every customer notification email.') }}</flux:text>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <x-ui.action-message on="email-config-saved">{{ __('Saved.') }}</x-ui.action-message>
            </div>
        </form>
    </x-app.settings.layout>
</section>
