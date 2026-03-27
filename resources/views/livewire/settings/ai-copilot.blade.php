<section class="w-full">
    @include('partials.settings-heading')

    <x-app.settings.layout :heading="__('AI Copilot')" :subheading="__('Configure AI suggestions, summary, and model selection')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
          

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
