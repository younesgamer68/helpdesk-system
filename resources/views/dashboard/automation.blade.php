<x-layouts::app :title="__('Automation Rules')">
    <div class="mb-5 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Automation Rules</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Configure automatic actions for your tickets</p>
        </div>
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
    
    <livewire:dashboard.automation-rules-table />
</x-layouts::app>
