<x-layouts::app :title="$filterMode === 'assignment' ? __('Assignment Rules') : __('Ticket Rules')">
    <section class="w-full">
        <flux:separator class="mb-5 border-b border-zinc-200 dark:border-zinc-700" />

        <flux:heading class="sr-only">{{ __('Automation Settings') }}</flux:heading>

        <x-automation.layout :heading="$filterMode === 'assignment' ? __('Assignment Rules') : __('Ticket Rules')" :subheading="__('Configure automatic actions for your tickets')">
        <div class="mb-4 flex justify-end">
            <button 
                onclick="Livewire.dispatch('openCreateModal')"
                class="px-4 py-2 bg-teal-500 text-white text-sm font-medium rounded-lg flex items-center gap-2 hover:bg-teal-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Rule
            </button>
        </div>
        
        <livewire:dashboard.automation-rules-table :filterMode="$filterMode" />
        </x-automation.layout>
    </section>
</x-layouts::app>
