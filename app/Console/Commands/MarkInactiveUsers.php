<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MarkInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark users as offline if they have been inactive for more than 5 minutes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Users table has CompanyScope — must use withoutGlobalScopes in console
        $count = \App\Models\User::withoutGlobalScopes()
            ->where('status', 'online')
            ->where('last_activity', '<', now()->subMinutes(5))
            ->update(['status' => 'offline']);

        $this->info("Successfully marked {$count} inactive users as offline.");
    }
}
