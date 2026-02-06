<x-layouts::app :title="__('Tickets')">
    <div class="mb-5 flex justify-between items-center">
        <h1 class="text-3xl ">Tickets</h1>
             <button 
                x-data 
                @click="$dispatch('open-create-ticket-modal')"
                class="px-4 py-2 bg-teal-500  text-white text-sm font-medium rounded-lg  flex items-center gap-2 ">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Ticket
            </button>
     

    </div>
    <livewire:dashboard.tickets-table />

</x-layouts::app>
