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

/**
 * Scrape sources that carry an interval.
 *
 * Checked hourly rather than run hourly: the command only touches sources
 * whose own slot has come, so an interval of 24 hours means one run a day.
 * `withoutOverlapping` matters more here than anywhere — a harvest waits out
 * the crawl delay between requests and can easily outlive its hour.
 */
Schedule::command('scrape:due')
    ->hourly()
    ->withoutOverlapping(120)
    ->onOneServer();

/**
 * Scraper bookkeeping nobody will read again.
 *
 * The URL cache keeps one row per address ever fetched and the runs keep a log
 * of up to five hundred lines each; both grow forever and neither is anybody's
 * data. Weekly is often enough — this is housekeeping, not a deadline.
 */
Schedule::command('scrape:prune')
    ->weeklyOn(1, '03:40')
    ->withoutOverlapping()
    ->onOneServer();
