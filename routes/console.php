<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Process ticket escalations every 15 minutes
Schedule::command('tickets:process-escalations')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
