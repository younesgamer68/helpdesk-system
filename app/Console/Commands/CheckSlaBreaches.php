<?php

namespace App\Console\Commands;

use App\Models\AutomationRule;
use App\Models\Ticket;
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
        $this->info('Checking for SLA breaches...');

        // Find tickets that are past due_time, not resolved/closed, and not already marked as breached
        $breachedTickets = Ticket::whereNotNull('due_time')
            ->where('due_time', '<=', now())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->where('sla_status', '!=', 'breached')
            ->get();

        if ($breachedTickets->isEmpty()) {
            $this->info('No new SLA breaches found.');
            return;
        }

        $this->info("Found {$breachedTickets->count()} new SLA breaches.");

        foreach ($breachedTickets as $ticket) {
            // 1. Update status
            $ticket->update(['sla_status' => 'breached']);

            $this->info("Ticket ID {$ticket->id} marked as SLA breached.");
            Log::info("Ticket ID {$ticket->id} SLA breached. Current time: " . now() . " Due time: " . $ticket->due_time);

            // 2. Fetch active SLA breach rules for the company
            $rules = $automationEngine->getRulesOfType($ticket->company_id, AutomationRule::TYPE_SLA_BREACH);

            foreach ($rules as $rule) {
                $automationEngine->executeRule($rule, $ticket);
            }
        }

        $this->info('SLA breach check completed.');
    }
}
