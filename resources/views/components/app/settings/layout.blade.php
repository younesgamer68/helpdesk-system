<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.item :href="route('profile.edit', ['company' => Auth::user()->company?->slug ?? 'default'])"
                wire:navigate icon="user">{{ __('Profile') }}</flux:navlist.item>

            <flux:navlist.item
                :href="route('appearance.edit', ['company' => Auth::user()->company?->slug ?? 'default'])" wire:navigate
                icon="paint-brush">{{ __('Appearance') }}</flux:navlist.item>

            @if (Auth::user()->isAdmin())
                <flux:navlist.item
                    :href="route('company.profile', ['company' => Auth::user()->company?->slug ?? 'default'])"
                    wire:navigate icon="building-office-2">{{ __('Company Profile') }}</flux:navlist.item>

                <flux:navlist.item
                    :href="route('settings.ai-copilot', ['company' => Auth::user()->company?->slug ?? 'default'])"
                    wire:navigate icon="sparkles">{{ __('AI Copilot') }}</flux:navlist.item>
            @endif

            <flux:navlist.item
                :href="route('settings.security', ['company' => Auth::user()->company?->slug ?? 'default'])"
                wire:navigate icon="shield-check">{{ __('Security') }}</flux:navlist.item>

            <flux:navlist.item
                :href="route('notifications.preferences', ['company' => Auth::user()->company?->slug ?? 'default'])"
                wire:navigate icon="bell">{{ __('Notifications') }}</flux:navlist.item>

            @if (Auth::user()->isAdmin())
                <flux:navlist.item
                    :href="route('settings.danger', ['company' => Auth::user()->company?->slug ?? 'default'])"
                    wire:navigate icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400">
                    {{ __('Danger Zone') }}
                </flux:navlist.item>
            @endif
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div {{ $attributes->merge([
            'class' => 'mt-5 w-full ' . ($maxWidth ?? 'max-w-lg'),
        ]) }}>
            {{ $slot }}
        </div>
    </div>
</div>
