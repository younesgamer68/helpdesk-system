<x-layouts::app :title="($filterMode ?? 'ticket') === 'assignment' ? __('Assignment Rules') : __('Ticket Rules')">
    <section class="w-full">

        <flux:heading class="sr-only">{{ __('Automation Settings') }}</flux:heading>

        <x-automation.layout :heading="($filterMode ?? 'ticket') === 'assignment' ? __('Assignment Rules') : __('Ticket Rules')" :subheading="__('Configure automatic actions for your tickets')">
            <livewire:automation.automation-rules-table :filterMode="$filterMode ?? 'ticket'" />
        </x-automation.layout>
    </section>
</x-layouts::app>
