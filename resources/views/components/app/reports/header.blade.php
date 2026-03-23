@props(['datePreset' => 'this_week', 'startDate' => '', 'endDate' => ''])

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
    <x-ui.tab-title>Reports & Analytics</x-ui.tab-title>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">Insights and performance metrics for your helpdesk</p>
    </div>
    <div class="flex items-center gap-3" id="reports-controls">
        <flux:dropdown>
            <flux:button variant="outline" class="w-[160px] justify-between text-zinc-700 dark:text-zinc-300 font-normal" icon-trailing="chevron-up-down">
                {{ [
                    'today' => 'Today',
                    'this_week' => 'This Week',
                    'this_month' => 'This Month',
                    'last_3_months' => 'Last 3 Months',
                    'custom' => 'Custom'
                ][$datePreset] ?? 'This Week' }}
            </flux:button>
            <flux:menu class="w-[160px]">
                <flux:menu.radio.group wire:model.live="datePreset">
                    <flux:menu.radio value="today" class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">Today</flux:menu.radio>
                    <flux:menu.radio value="this_week" class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">This Week</flux:menu.radio>
                    <flux:menu.radio value="this_month" class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">This Month</flux:menu.radio>
                    <flux:menu.radio value="last_3_months" class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">Last 3 Months</flux:menu.radio>
                    <flux:menu.radio value="custom" class="hover:!bg-emerald-500 hover:!text-white data-active:!bg-emerald-500 data-active:!text-white dark:hover:!bg-emerald-600 dark:hover:!text-white dark:data-active:!bg-emerald-600 dark:data-active:!text-white">Custom</flux:menu.radio>
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
        @if ($datePreset === 'custom')
            <input type="date" wire:model.live="startDate"
                class="w-50px rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2" />
            <input type="date" wire:model.live="endDate"
                class="w-50px rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2" />
        @endif
    </div>
</div>
