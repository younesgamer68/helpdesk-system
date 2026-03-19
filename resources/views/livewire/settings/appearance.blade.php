<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance Settings') }}</flux:heading>

    <x-app.settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div class="space-y-8">
            {{-- Theme toggle --}}
            <div>
                <flux:subheading>{{ __('Theme') }}</flux:subheading>
                <div class="mt-3">
                    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                        <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                        <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                        <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                    </flux:radio.group>
                </div>
            </div>

            {{-- Accent color --}}
            @if (Auth::user()->isAdmin())
                <div>
                    <flux:subheading>{{ __('Brand Accent Color') }}</flux:subheading>
                    <flux:text size="sm" class="mt-1 mb-3 text-zinc-500">
                        {{ __('Used for buttons, badges, and brand highlights across the interface.') }}</flux:text>
                    <form wire:submit="saveAccentColor" class="flex items-end gap-4">
                        <div class="flex items-center gap-3">
                            <input wire:model="accentColor" type="color"
                                class="h-10 w-10 cursor-pointer rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent p-0.5" />
                            <flux:input wire:model="accentColor" type="text" maxlength="7" placeholder="#0B4F4A"
                                class="w-28 font-mono uppercase" />
                        </div>
                        <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                        <x-ui.action-message on="accent-color-saved">{{ __('Saved.') }}</x-ui.action-message>
                    </form>
                    @error('accentColor')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Logo in widget/portal --}}
                <div>
                    <flux:subheading>{{ __('Logo in Widget & Portal') }}</flux:subheading>
                    <flux:text size="sm" class="mt-1 mb-3 text-zinc-500">
                        {{ __('Your logo appears in the chat widget and knowledge base portal. Upload it under Company Profile.') }}
                    </flux:text>
                    @php $logo = Auth::user()->company->logo; @endphp
                    @if ($logo)
                        <div class="flex items-center gap-4">
                            <img src="{{ Storage::url($logo) }}" alt="{{ Auth::user()->company->name }}"
                                class="h-12 rounded-lg object-contain border border-zinc-200 dark:border-zinc-700 p-1">
                            <flux:button :href="route('company.profile', Auth::user()->company->slug)" wire:navigate
                                variant="ghost" size="sm" icon="pencil">
                                {{ __('Change Logo') }}
                            </flux:button>
                        </div>
                    @else
                        <flux:button :href="route('company.profile', Auth::user()->company->slug)" wire:navigate
                            variant="outline" size="sm" icon="photo">
                            {{ __('Upload Logo') }}
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </x-app.settings.layout>
</section>
