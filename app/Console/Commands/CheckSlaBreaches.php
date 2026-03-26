<?php

namespace App\Console\Commands;

use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaBreached;
use App\Services\Automation\AutomationEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helpdesk:check-sla-breaches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for open tickets that have breached their SLA and trigger automation rules';

    /**
     * Execute the console command.
     */
    public function handle(AutomationEngine $automationEngine)
    {
        $this->info('Checking SLA status updates...');

        $tickets = Ticket::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->whereNotNull('due_time')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with(['assignedTo:id,company_id', 'company:id,timezone'])
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('No SLA-eligible tickets found.');

            return;
        }

        $breachedCount = 0;
        $atRiskCount = 0;
        $rulesByCompany = [];
        $adminUsersByCompany = [];

        foreach ($tickets as $ticket) {
            $previousStatus = $ticket->sla_status ?? 'on_time';
            $newStatus = $this->resolveSlaStatus($ticket);

            if ($previousStatus !== $newStatus) {
                $ticket->update(['sla_status' => $newStatus]);
            }

            if ($newStatus === 'at_risk') {
                $atRiskCount++;
            }

            if ($newStatus !== 'breached' || $previousStatus === 'breached') {
                continue;
            }

            $breachedCount++;

            $this->info("Ticket ID {$ticket->id} (company {$ticket->company_id}) marked as SLA breached.");
            Log::info("Ticket ID {$ticket->id} SLA breached.", [
                'company_id' => $ticket->company_id,
                'due_time' => $ticket->due_time,
            ]);

            // Notify assigned operator of the breach
            if ($ticket->assignedTo) {
                $ticket->assignedTo->notify(new SlaBreached($ticket));
            }

            // Let automation rules handle admin notifications to avoid duplicates
            if (! array_key_exists($ticket->company_id, $rulesByCompany)) {
                $rulesByCompany[$ticket->company_id] = $automationEngine->getRulesOfType($ticket->company_id, AutomationRule::TYPE_SLA_BREACH);
            }

            $ruleNotifiedAdmins = false;
            foreach ($rulesByCompany[$ticket->company_id] as $rule) {
                $automationEngine->executeRule($rule, $ticket);
                if (! empty($rule->actions['notify_admin'])) {
                    $ruleNotifiedAdmins = true;
                }
            }

            // Fallback: notify admins if no automation rule handled it
            if (! $ruleNotifiedAdmins) {
                $this->notifyAdminsOfBreach($ticket, $adminUsersByCompany);
            }
        }

        $this->info("SLA status check completed. Breached: {$breachedCount}, At risk: {$atRiskCount}.");
    }

    protected function resolveSlaStatus(Ticket $ticket): string
    {
        $dueTime = $ticket->due_time;

        if (! $dueTime) {
            return 'on_time';
        }

        $timezone = $ticket->company?->timezone ?? 'UTC';
        $currentTime = now($timezone);

        $remainingSeconds = $currentTime->diffInSeconds($dueTime, false);

        if ($remainingSeconds <= 0) {
            return 'breached';
        }

        $totalSlaSeconds = max(1, $ticket->created_at->diffInSeconds($dueTime));
        $atRiskThreshold = (int) floor($totalSlaSeconds * 0.25);

        return $remainingSeconds <= $atRiskThreshold ? 'at_risk' : 'on_time';
    }

    protected function notifyAdminsOfBreach(Ticket $ticket, array &$adminUsersByCompany): void
    {
        if (! array_key_exists($ticket->company_id, $adminUsersByCompany)) {
            $adminUsersByCompany[$ticket->company_id] = User::withoutGlobalScope(\App\Scopes\CompanyScope::class)
                ->where('company_id', $ticket->company_id)
                ->where('role', 'admin')
                ->get();
        }

        foreach ($adminUsersByCompany[$ticket->company_id] as $admin) {
            $admin->notify(new SlaBreached($ticket));
        }
    }
}
