<x-layouts::app :title="__('Customer Details')">
    <div class="p-6">
        @livewire('dashboard.customer-details', ['customer' => $customer])
    </div>
</x-layouts::app>
