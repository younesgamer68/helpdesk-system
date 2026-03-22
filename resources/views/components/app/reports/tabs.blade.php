@props(['activeTab'])

<div class="border-b border-zinc-200 dark:border-zinc-800">
    <nav class="flex gap-1 justify-between -mb-px">
        <div>
            @foreach (['overview' => 'Overview', 'agents' => 'Agent Performance', 'tickets' => 'Tickets', 'categories' => 'Categories', 'teams' => 'Teams'] as $tabKey => $tabLabel)
                <button wire:click="setTab('{{ $tabKey }}')"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $activeTab === $tabKey ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:border-zinc-600' }}">
                    {{ $tabLabel }}
                </button>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            @if ($activeTab === 'tickets')
                <button wire:click="exportTicketsCsv" type="button"
                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2 shadow-sm hover:shadow-md">
                    <span class="flex items-center gap-1 whitespace-nowrap">
                        <flux:icon.arrow-down-tray class="size-4 shrink-0" /> Tickets CSV
                    </span>
                </button>
            @endif
            @if ($activeTab === 'agents')
                <button wire:click="exportAgentsCsv" type="button"
                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2 shadow-sm hover:shadow-md">
                    <span class="flex items-center gap-1 whitespace-nowrap">
                        <flux:icon.arrow-down-tray class="size-4 shrink-0" /> Agents CSV
                    </span>
                </button>
            @endif
            <button type="button" @click="exportPdf()"
                class="expdf-btn px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2 shadow-sm hover:shadow-md">
                <flux:icon.document class="size-4 shrink-0" /> Export PDF
            </button>
        </div>
    </nav>
</div>
