<x-layouts::app :title="__('Customers')">
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white">Customers Management</h1>
            <p class="text-sm text-zinc-400 mt-1">Manage, search, and view all clients who have submitted tickets.</p>
        </div>

        <livewire:tickets.customers-table />
    </div>
</x-layouts::app>
