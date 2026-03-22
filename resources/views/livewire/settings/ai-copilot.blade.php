<section class="w-full">
    @include('partials.settings-heading')

    <x-app.settings.layout :heading="__('AI Copilot')" :subheading="__('Configure AI suggestions, summary, and model selection')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-5 space-y-3">
                <div>
                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">AI Model</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Choose which model powers AI suggestions,
                        and summaries.</p>
                </div>
                @php($modelOptions = $this->modelOptions())
                <flux:dropdown position="bottom start">
                    <button type="button" class="w-full max-w-sm flex items-center justify-between rounded border border-zinc-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-0">
                        <span class="truncate">
                            <?php
                                $selectedOption = collect($modelOptions)->firstWhere(function ($opt, $val) use ($ai_model) {
                                    return $val === $ai_model;
                                });
                            ?>
                            {{ $selectedOption ? $selectedOption['label'] : 'Select a model' }}
                        </span>
                        <svg class="h-4 w-4 ml-2 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <flux:menu class="w-full max-w-sm">
                        <flux:menu.radio.group wire:model="ai_model">
                            @foreach ($modelOptions as $value => $option)
                                <flux:menu.radio value="{{ $value }}" :disabled="!$option['enabled']" class="{{ $option['enabled'] ? 'text-zinc-600 dark:text-zinc-300 hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white' : 'text-zinc-400 dark:text-zinc-500' }}">
                                    {{ $option['label'] }}{{ $option['enabled'] ? '' : ' (Unavailable - missing API key)' }}
                                </flux:menu.radio>
                            @endforeach
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Unavailable models are disabled until their provider
                    API key is configured.</p>
                @error('ai_model')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg divide-y divide-zinc-200 dark:divide-zinc-800">
                <div class="flex items-center justify-between p-5">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Suggested Replies</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">AI generates reply suggestions for
                            agents in the ticket view.</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="ai_suggestions_enabled"
                            class="w-4 h-4 rounded checked:bg-emerald-600 dark:checked:bg-emerald-600 checked:border-emerald-600 dark:checked:border-emerald-600 border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-600 accent-emerald-600">
                    </label>
                </div>

                <div class="flex items-center justify-between p-5">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">AI Ticket Summary</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Show an AI-generated summary on the
                            ticket detail page.</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="ai_summary_enabled"
                            class="w-4 h-4 rounded checked:bg-emerald-600 dark:checked:bg-emerald-600 checked:border-emerald-600 dark:checked:border-emerald-600 border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-600 accent-emerald-600">
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <x-ui.action-message on="ai-copilot-updated">{{ __('Saved.') }}</x-ui.action-message>
            </div>
        </form>
    </x-app.settings.layout>
</section>
