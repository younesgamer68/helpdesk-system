<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Knowledge Base</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Organize internal help content, categories, and media
            assets.</p>
    </div>

    <div class="flex items-start max-md:flex-col">
        <div class="me-10 w-full pb-4 md:w-55">
            <flux:navlist aria-label="{{ __('Knowledge Base') }}">
                <flux:navlist.item :href="route('kb.articles', ['company' => Auth::user()->company->slug])"
                    :current="request()->routeIs('kb.articles', 'kb.articles.create', 'kb.articles.edit')"
                    wire:navigate>{{ __('Articles') }}</flux:navlist.item>
                <flux:navlist.item :href="route('kb.categories', ['company' => Auth::user()->company->slug])"
                    :current="request()->routeIs('kb.categories')" wire:navigate>{{ __('Categories') }}
                </flux:navlist.item>
            </flux:navlist>
        </div>

        <flux:separator class="md:hidden" />

        <div class="flex-1 self-stretch max-md:pt-6">
            <flux:heading>{{ $heading ?? '' }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

            <div
                {{ $attributes->merge([
                    'class' => 'mt-5 w-full ' . ($maxWidth ?? 'max-w-6xl'),
                ]) }}>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
