@use('Carbon\Carbon')

<div wire:key="reports-{{ $datePreset }}-{{ $startDate }}-{{ $endDate }}"
    x-data="reportsCharts({
    ticketVolume: @js($this->ticketVolumeChart),
    statusBreakdown: @js($this->statusBreakdown),
    priorityBreakdown: @js($this->priorityBreakdown),
    categoryVolume: @js($this->categoryVolume),
    activeTab: @js($activeTab),
    selectedAgentData: @js($activeTab === 'agents' ? $this->selectedAgentData : null),
    expandedCategoryDetails: @js($activeTab === 'categories' ? $this->expandedCategoryDetails : null),
    categoryHealth: @js($activeTab === 'categories' ? $this->categoryHealth : null),
})" x-init="init()" id="reports-page">
    <div class="p-4 lg:p-6 space-y-8">
        <x-dashboard.reports.header :date-preset="$datePreset" :start-date="$startDate" :end-date="$endDate" />

        <x-dashboard.reports.tabs :active-tab="$activeTab" />

        @if ($activeTab === 'overview')
            @include('livewire.dashboard.reports.overview')
        @elseif($activeTab === 'agents')
            @include('livewire.dashboard.reports.agents-tab')
        @elseif($activeTab === 'tickets')
            @include('livewire.dashboard.reports.tickets-tab')
        @elseif($activeTab === 'categories')
            @include('livewire.dashboard.reports.categories-tab')
        @endif
    </div>

    <x-dashboard.reports.export-overlay />
</div>
