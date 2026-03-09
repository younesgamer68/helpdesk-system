<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Automation\AutomationEngine;
use Illuminate\Console\Command;

class ProcessTicketEscalations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:process-escalations
                            {--company= : Process escalations only for a specific company}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process ticket escalation rules for idle tickets';

    /**
     * Execute the console command.
     */
    public function handle(AutomationEngine $engine): int
    {
        $companyId = $this->option('company');

        if ($companyId) {
            $this->info("Processing escalations for company ID: {$companyId}");
            $engine->processEscalations((int) $companyId);
        } else {
            $companies = Company::all();
            $bar = $this->output->createProgressBar($companies->count());
            $bar->start();

            foreach ($companies as $company) {
                $engine->processEscalations($company->id);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('Escalation processing completed.');

        return Command::SUCCESS;
    }
}
