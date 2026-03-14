@props(['datePreset' => 'this_week', 'startDate' => '', 'endDate' => ''])

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Reports & Analytics</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Insights and performance metrics for your helpdesk</p>
    </div>
    <div class="flex items-center gap-3" id="reports-controls">
        <flux:select wire:model.live="datePreset" class="w-50px">
            <flux:select.option value="today">Today</flux:select.option>
            <flux:select.option value="this_week">This Week</flux:select.option>
            <flux:select.option value="this_month">This Month</flux:select.option>
            <flux:select.option value="last_3_months">Last 3 Months</flux:select.option>
            <flux:select.option value="custom">Custom</flux:select.option>
        </flux:select>
        @if ($datePreset === 'custom')
            <input type="date" wire:model.live="startDate"
                class="w-50px rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2" />
            <input type="date" wire:model.live="endDate"
                class="w-50px rounded-lg border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-200 text-sm px-3 py-2" />
        @endif
    </div>
</div>
