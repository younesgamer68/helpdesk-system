<x-layouts::app :title="__('Dashboard')">
    @if(auth()->user()->isAdmin())
        @livewire('app.admin-dashboard')
    @else
        @livewire('app.agent-dashboard')
    @endif
</x-layouts::app>
