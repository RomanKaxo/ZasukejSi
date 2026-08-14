<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Subscriptions and memberships do not expire on their own — the status column
 * has to be moved. Without this, a lapsed subscription stays `active` with a
 * past end date and the admin counts it as live.
 *
 * Runs early, before anyone is likely to be looking at the numbers.
 * Requires a cron entry: `* * * * * php artisan schedule:run`.
 */
Schedule::command('subscriptions:lifecycle')
    ->dailyAt('03:10')
    ->withoutOverlapping()
    ->onOneServer();
