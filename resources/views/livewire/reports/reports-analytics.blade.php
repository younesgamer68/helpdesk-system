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
    <div class="space-y-8">
        <x-app.reports.header :date-preset="$datePreset" :start-date="$startDate" :end-date="$endDate" />

        <x-app.reports.tabs :active-tab="$activeTab" />

        @if ($activeTab === 'overview')
            @include('livewire.reports.overview')
        @elseif($activeTab === 'agents')
            @include('livewire.reports.agents-tab')
        @elseif($activeTab === 'tickets')
            @include('livewire.reports.tickets-tab')
        @elseif($activeTab === 'categories')
            @include('livewire.reports.categories-tab')
        @endif
    </div>

    <x-app.reports.export-overlay />
</div>
