<?php

namespace App\Livewire\Tickets;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SlaConfiguration extends Component
{
    public string $timezone = 'UTC';

    public bool $is_enabled = false;

    public int $low_minutes = 1440;

    public int $medium_minutes = 480;

    public int $high_minutes = 120;

    public int $urgent_minutes = 30;

    public int $warning_hours = 24;

    public int $auto_close_hours = 48;

    public int $reopen_hours = 48;

    public int $linked_ticket_days = 7;

    public int $soft_delete_days = 30;

    public int $hard_delete_days = 90;

    public function mount(): void
    {
        $company = Auth::user()->company;
        $this->timezone = $company->timezone ?? 'UTC';

        $policy = SlaPolicy::where('company_id', Auth::user()->company_id)->first();

        if ($policy) {
            $this->is_enabled = $policy->is_enabled;
            $this->low_minutes = $policy->low_minutes;
            $this->medium_minutes = $policy->medium_minutes;
            $this->high_minutes = $policy->high_minutes;
            $this->urgent_minutes = $policy->urgent_minutes;
            $this->warning_hours = $policy->warning_hours ?? 24;
            $this->auto_close_hours = $policy->auto_close_hours ?? 48;
            $this->reopen_hours = $policy->reopen_hours ?? 48;
            $this->linked_ticket_days = $policy->linked_ticket_days ?? 7;
            $this->soft_delete_days = $policy->soft_delete_days ?? 30;
            $this->hard_delete_days = $policy->hard_delete_days ?? 90;
        }
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function timezones(): array
    {
        $identifiers = DateTimeZone::listIdentifiers();
        $timezones = [];

        foreach ($identifiers as $tz) {
            $timezones[$tz] = str_replace(['/', '_'], [' / ', ' '], $tz);
        }

        return $timezones;
    }

    protected function rules(): array
    {
        return [
            'timezone' => 'required|string|timezone',
            'is_enabled' => 'boolean',
            'low_minutes' => 'required|integer|min:1',
            'medium_minutes' => 'required|integer|min:1',
            'high_minutes' => 'required|integer|min:1',
            'urgent_minutes' => 'required|integer|min:1',
            'warning_hours' => 'required|integer|min:1',
            'auto_close_hours' => 'required|integer|min:1',
            'reopen_hours' => 'required|integer|min:1',
            'linked_ticket_days' => 'required|integer|min:1',
            'soft_delete_days' => 'required|integer|min:1',
            'hard_delete_days' => 'required|integer|min:1',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Auth::user()->company->update([
            'timezone' => $this->timezone,
        ]);

        $policy = SlaPolicy::updateOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'is_enabled' => $this->is_enabled,
                'low_minutes' => $this->low_minutes,
                'medium_minutes' => $this->medium_minutes,
                'high_minutes' => $this->high_minutes,
                'urgent_minutes' => $this->urgent_minutes,
                'warning_hours' => $this->warning_hours,
                'auto_close_hours' => $this->auto_close_hours,
                'reopen_hours' => $this->reopen_hours,
                'linked_ticket_days' => $this->linked_ticket_days,
                'soft_delete_days' => $this->soft_delete_days,
                'hard_delete_days' => $this->hard_delete_days,
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
        $company = Auth::user()->company;
        $timezone = $company->timezone ?? 'UTC';

        $tickets = Ticket::where('company_id', $company->id)
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

            // Recalculate from ticket creation time using company timezone
            $newDueTime = $ticket->created_at->copy()->addMinutes($minutes);

            // Update SLA status based on new due time using company timezone
            $slaStatus = now($timezone)->greaterThan($newDueTime) ? 'breached' : 'on_time';

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
