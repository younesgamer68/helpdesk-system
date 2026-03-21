<x-layouts::app :title="__('Tickets')">
    <div class="mb-5 flex justify-between items-center animate-enter">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tickets</h1>
             <button 
                x-data 
                @click="$dispatch('open-create-ticket-modal')"
                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Ticket
            </button>
     

    </div>
    <livewire:tickets.tickets-table />

</x-layouts::app>
