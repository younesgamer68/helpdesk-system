<?php

namespace App\Livewire\Tickets;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SlaConfiguration extends Component
{
    public bool $is_enabled = false;

    public int $low_minutes = 1440;

    public int $medium_minutes = 480;

    public int $high_minutes = 120;

    public int $urgent_minutes = 30;

    public function mount(): void
    {
        $policy = SlaPolicy::where('company_id', Auth::user()->company_id)->first();

        if ($policy) {
            $this->is_enabled = $policy->is_enabled;
            $this->low_minutes = $policy->low_minutes;
            $this->medium_minutes = $policy->medium_minutes;
            $this->high_minutes = $policy->high_minutes;
            $this->urgent_minutes = $policy->urgent_minutes;
        }
    }

    protected function rules(): array
    {
        return [
            'is_enabled' => 'boolean',
            'low_minutes' => 'required|integer|min:1',
            'medium_minutes' => 'required|integer|min:1',
            'high_minutes' => 'required|integer|min:1',
            'urgent_minutes' => 'required|integer|min:1',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $policy = SlaPolicy::updateOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'is_enabled' => $this->is_enabled,
                'low_minutes' => $this->low_minutes,
                'medium_minutes' => $this->medium_minutes,
                'high_minutes' => $this->high_minutes,
                'urgent_minutes' => $this->urgent_minutes,
            ]
        );

        // Recalculate due_time for all open tickets
        $this->recalculateOpenTickets($policy);

        $this->dispatch('show-toast', message: 'SLA configuration saved and applied to existing tickets!', type: 'success');
    }

    /**
     * Recalculate due_time for all non-closed/non-resolved tickets.
     */
    protected function recalculateOpenTickets(SlaPolicy $policy): void
    {
        $tickets = Ticket::where('company_id', Auth::user()->company_id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->get();

        foreach ($tickets as $ticket) {
            $minutes = match ($ticket->priority) {
                'urgent' => $policy->urgent_minutes,
                'high' => $policy->high_minutes,
                'medium' => $policy->medium_minutes,
                'low' => $policy->low_minutes,
                default => $policy->low_minutes,
            };

            // Recalculate from ticket creation time
            $newDueTime = $ticket->created_at->addMinutes($minutes);

            // Update SLA status based on new due time
            $slaStatus = $newDueTime->isPast() ? 'breached' : 'on_time';

            $ticket->update([
                'due_time' => $newDueTime,
                'sla_status' => $slaStatus,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.tickets.sla-configuration');
    }
}
