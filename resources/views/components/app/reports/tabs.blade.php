@props(['activeTab'])

<div class="border-b border-zinc-200 dark:border-zinc-800">
    <nav class="flex gap-1 justify-between -mb-px">
        <div>
            @foreach (['overview' => 'Overview', 'agents' => 'Agent Performance', 'tickets' => 'Tickets', 'categories' => 'Categories', 'teams' => 'Teams'] as $tabKey => $tabLabel)
                <button wire:click="setTab('{{ $tabKey }}')"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $activeTab === $tabKey ? 'border-teal-500 text-teal-400' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:border-zinc-600' }}">
                    {{ $tabLabel }}
                </button>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            @if ($activeTab === 'tickets')
                <flux:button wire:click="exportTicketsCsv" variant="ghost" size="sm"
                    class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                    <span class="flex items-center gap-1 whitespace-nowrap">
                        <flux:icon.arrow-down-tray class="size-4 shrink-0" /> Tickets CSV
                    </span>
                </flux:button>
            @endif
            @if ($activeTab === 'agents')
                <flux:button wire:click="exportAgentsCsv" variant="ghost" size="sm"
                    class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
                    <span class="flex items-center gap-1 whitespace-nowrap">
                        <flux:icon.arrow-down-tray class="size-4 shrink-0" /> Agents CSV
                    </span>
                </flux:button>
            @endif
            <flux:button type="button" variant="ghost" size="sm"
                class="expdf-btn text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100"
                @click="exportPdf()">
                <flux:icon.document class="size-4 mr-1" /> Export PDF
            </flux:button>
        </div>
    </nav>
</div>
