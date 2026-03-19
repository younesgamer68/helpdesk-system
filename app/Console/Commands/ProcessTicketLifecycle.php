<?php

namespace App\Console\Commands;

use App\Mail\TicketClosed;
use App\Mail\TicketClosedWarning;
use App\Models\Company;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessTicketLifecycle extends Command
{
    protected $signature = 'app:process-ticket-lifecycle';

    protected $description = 'Send closure warnings and auto-close resolved tickets';

    public function handle(): int
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $policy = SlaPolicy::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $company->id)
                ->first();

            $warningHours = $policy?->warning_hours ?? 24;
            $autoCloseHours = $policy?->auto_close_hours ?? 48;

            $this->sendWarnings($company->id, $warningHours, $autoCloseHours);
            $this->autoCloseTickets($company->id, $autoCloseHours);
        }

        $this->info('Ticket lifecycle processing completed.');

        return Command::SUCCESS;
    }

    private function sendWarnings(int $companyId, int $warningHours, int $autoCloseHours): void
    {
        $warningThreshold = now()->subHours($autoCloseHours - $warningHours);
        $autoCloseThreshold = now()->subHours($autoCloseHours);

        Ticket::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('status', 'resolved')
            ->whereNull('warning_sent_at')
            ->where('resolved_at', '<=', $warningThreshold)
            ->where('resolved_at', '>', $autoCloseThreshold)
            ->each(function (Ticket $ticket) use ($autoCloseHours) {
                $resolvedAt = $ticket->resolved_at;
                $closesAt = $resolvedAt->copy()->addHours($autoCloseHours);
                $remainingHours = (int) max(1, now()->diffInHours($closesAt, false));

                $customerEmail = $ticket->customer_email;

                if ($customerEmail) {
                    Mail::to($customerEmail)->send(new TicketClosedWarning($ticket, $remainingHours));
                }

                $ticket->update(['warning_sent_at' => now()]);

                Log::info("Ticket #{$ticket->ticket_number}: closure warning sent.");
            });
    }

    private function autoCloseTickets(int $companyId, int $autoCloseHours): void
    {
        $threshold = now()->subHours($autoCloseHours);

        Ticket::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('status', 'resolved')
            ->where('resolved_at', '<=', $threshold)
            ->each(function (Ticket $ticket) {
                $customerEmail = $ticket->customer_email;

                if ($customerEmail) {
                    Mail::to($customerEmail)->send(new TicketClosed($ticket, 'auto_closed'));
                }

                $ticket->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                    'close_reason' => 'auto_closed',
                ]);

                $ticket->logs()->create([
                    'company_id' => $ticket->company_id,
                    'action' => 'auto_closed',
                    'description' => 'Ticket automatically closed after no activity following resolution.',
                ]);

                Log::info("Ticket #{$ticket->ticket_number}: auto-closed.");
            });
    }
}
