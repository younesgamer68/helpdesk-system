<x-layouts::app :title="__('Customers')">
    <div class="mb-5">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Customers</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manage, search, and review everyone who has submitted a
            ticket.</p>
    </div>

    <livewire:tickets.customers-table />
</x-layouts::app>
