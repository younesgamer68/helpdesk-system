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
        <div class="pdf-exclude">
            <x-app.reports.header :date-preset="$datePreset" :start-date="$startDate" :end-date="$endDate" />
        </div>
        <div class="pdf-exclude">
            <x-app.reports.tabs :active-tab="$activeTab" />
        </div>

        <div id="reports-pdf-content">
        @if ($activeTab === 'overview')
            <div wire:key="reports-tab-overview">@include('livewire.reports.overview')</div>
        @elseif($activeTab === 'agents')
            <div wire:key="reports-tab-agents">@include('livewire.reports.agents-tab')</div>
        @elseif($activeTab === 'tickets')
            <div wire:key="reports-tab-tickets">@include('livewire.reports.tickets-tab')</div>
        @elseif($activeTab === 'categories')
            <div wire:key="reports-tab-categories">@include('livewire.reports.categories-tab')</div>
        @endif
        </div>
    </div>

    <x-app.reports.export-overlay />
</div>
