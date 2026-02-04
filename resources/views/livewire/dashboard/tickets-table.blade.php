<div>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Ticket ID</flux:table.column>
            <flux:table.column>Subject</flux:table.column>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Assigned To</flux:table.column>
            <flux:table.column>Priority</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Category</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($tickets as $ticket)
                <flux:table.row :key="$ticket->ticket_number">
                    <flux:table.cell class="flex items-center gap-3">
                        {{ $ticket->ticket_number }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $ticket->subject }}
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $ticket->customer_name }}
                    </flux:table.cell>

                    <flux:table.cell variant="strong">
                        {{ $ticket->user->name }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" color="red" inset="top bottom">{{ $ticket->priority }}</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $ticket->status }}
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $ticket->category->name }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom">
                            </flux:button>

                            <flux:navmenu>
                                <flux:navmenu.item  icon="trash">Delete</flux:navmenu.item>
                                <flux:navmenu.item  icon="building-storefront" >
                                       <a href="tickets/{{ $ticket->ticket_number }}" wire:navigate>View details</a> 
                            </flux:navmenu.item>

                            </flux:navmenu>
                        </flux:dropdown>
                    </flux:table.cell>

                </flux:table.row>
            @endforeach
        </flux:table.rows>

    </flux:table>
    <div class="mt-5">
        {{ $tickets->links() }}
    </div>

</div>


<!-- Livewire component example code...
    use \Livewire\WithPagination;

    public $sortBy = 'date';
    public $sortDirection = 'desc';

    public function sort($column) {
        if (sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[\Livewire\Attributes\Computed]
    public function tickets()
    {
        return \App\Models\Ticket::query()
            ->tap(fn ($query) => $this->sortBy ? $query->ticketBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(5);
    }
-->
