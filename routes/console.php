<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run locally with `php artisan schedule:work`, or `schedule:run` from cron every minute.
Schedule::command('appointments:cancel-unconfirmed')->hourly()->withoutOverlapping();
Schedule::command('reminders:send')->dailyAt('08:00')->withoutOverlapping();
