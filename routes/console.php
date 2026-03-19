<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* |-------------------------------------------------------------------------- | Scheduled Tasks |-------------------------------------------------------------------------- */

// Mark inactive users as offline every minute
Schedule::command('app:mark-inactive-users')
    ->everyMinute()
    ->runInBackground();

// Process ticket escalations every 15 minutes
Schedule::command('tickets:process-escalations')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Check for SLA breaches every minute
Schedule::command('helpdesk:check-sla-breaches')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Process ticket lifecycle (warnings + auto-close) every hour
Schedule::command('app:process-ticket-lifecycle')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Clean up old closed and soft-deleted tickets daily at midnight
Schedule::command('app:cleanup-old-tickets')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();
