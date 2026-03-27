<section class="w-full">
    @include('partials.settings-heading')

    <x-app.settings.layout :heading="__('Company Profile')" :subheading="__('Manage your company\'s core settings and logo')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <flux:input wire:model="companyName" :label="__('Company Name')" type="text" required />

            <div>
                <flux:input wire:model="companySlug" :label="__('Company Slug')" type="text"
                    placeholder="yourcompany" />
                <flux:text size="sm" class="mt-1 text-zinc-500">
                    {{ __('You can type any company name here. We auto-format it for URL safety and keep it unique. URL: yourslug.') }}{{ config('app.domain') }}{{ __(' — changing this will log you out') }}
                </flux:text>
                @error('companySlug')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <flux:input wire:model="maxTicketsPerAgent" :label="__('Max Tickets Per Agent')" type="number"
                    min="1" max="100" />
                <flux:text size="sm" class="mt-1 text-zinc-500">
                    {{ __('Maximum number of tickets that can be assigned to a single agent at a time (1–100).') }}
                </flux:text>
                @error('maxTicketsPerAgent')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <flux:label>{{ __('Logo') }}</flux:label>

                @php $company = Auth::user()->company; @endphp

                @if ($logo)
                    <div class="mt-2 mb-3">
                        <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview"
                            class="h-16 rounded-lg object-contain">
                    </div>
                @elseif ($company->logo)
                    <div class="mt-2 mb-3">
                        <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}"
                            class="h-16 rounded-lg object-contain">
                    </div>
                @endif

                <input type="file" wire:model="logo" accept="image/jpeg,image/png,image/gif,image/webp"
                    class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300 dark:hover:file:bg-zinc-600" />
                @if ($logo || $company->logo)
                    <button type="button" wire:click="resetLogo"
                        class="mt-2 inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20">
                        {{ __('Reset Logo') }}
                    </button>
                @endif
                @error('logo')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-ui.action-message class="me-3" on="company-profile-updated">
                    {{ __('Saved.') }}
                </x-ui.action-message>
            </div>
        </form>
    </x-app.settings.layout>
</section>
