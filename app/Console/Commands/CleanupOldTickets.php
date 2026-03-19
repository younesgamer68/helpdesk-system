<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldTickets extends Command
{
    protected $signature = 'app:cleanup-old-tickets';

    protected $description = 'Soft-delete closed tickets and hard-delete old soft-deleted tickets';

    public function handle(): int
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $policy = SlaPolicy::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $company->id)
                ->first();

            $softDeleteDays = $policy?->soft_delete_days ?? 30;
            $hardDeleteDays = $policy?->hard_delete_days ?? 90;

            $this->softDeleteClosedTickets($company->id, $softDeleteDays);
            $this->hardDeleteOldTickets($company->id, $hardDeleteDays);
        }

        $this->info('Ticket cleanup completed.');

        return Command::SUCCESS;
    }

    private function softDeleteClosedTickets(int $companyId, int $softDeleteDays): void
    {
        $threshold = now()->subDays($softDeleteDays);

        Ticket::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('status', 'closed')
            ->where('closed_at', '<=', $threshold)
            ->each(function (Ticket $ticket) {
                Log::info("Ticket #{$ticket->ticket_number}: soft-deleting.");
                $ticket->delete();
            });
    }

    private function hardDeleteOldTickets(int $companyId, int $hardDeleteDays): void
    {
        $threshold = now()->subDays($hardDeleteDays);

        Ticket::withoutGlobalScope(CompanyScope::class)
            ->onlyTrashed()
            ->where('company_id', $companyId)
            ->where('deleted_at', '<=', $threshold)
            ->each(function (Ticket $ticket) {
                Log::info("Ticket #{$ticket->ticket_number}: hard-deleting.");
                $ticket->forceDelete();
            });
    }
}
