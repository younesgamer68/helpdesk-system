<x-layouts::app :title="__('SLA Policy')">
    <section class="w-full">
        <flux:separator class="mb-5 border-b border-zinc-200 dark:border-zinc-700" />

        <flux:heading class="sr-only">{{ __('Automation Settings') }}</flux:heading>

        <x-automation.layout :heading="__('SLA Policy')" :subheading="__('Configure resolving timelines and tracking for tickets')">
            <livewire:tickets.sla-configuration />
        </x-automation.layout>
    </section>
</x-layouts::app>
