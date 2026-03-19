<div class="space-y-6 pl-8 pt-3">
    <x-ui.flash-message />
    <div>
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">AI</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Configure AI copilot, training data, and
            view usage analytics.</p>
    </div>

    <div class="flex items-start max-md:flex-col">
        <div class="me-10 w-full pb-4 md:w-55">
            <flux:navlist aria-label="{{ __('AI') }}">
                <flux:navlist.item :href="route('ai.training', ['company' => Auth::user()->company->slug])"
                    :current="request()->routeIs('ai.training')" wire:navigate>{{ __('Reply Training') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('ai.chat-history', ['company' => Auth::user()->company->slug])"
                    :current="request()->routeIs('ai.chat-history')" wire:navigate>{{ __('Chat History') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('ai.stats', ['company' => Auth::user()->company->slug])"
                    :current="request()->routeIs('ai.stats')" wire:navigate>{{ __('Usage Stats') }}
                </flux:navlist.item>
            </flux:navlist>
        </div>

        <flux:separator class="md:hidden" />

        <div class="flex-1 self-stretch max-md:pt-6">
            <flux:heading>{{ $heading ?? '' }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

            <div {{ $attributes->merge(['class' => 'mt-5 w-full ' . ($maxWidth ?? 'max-w-6xl')]) }}>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
