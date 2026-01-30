<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Log Pruning
\Illuminate\Support\Facades\Schedule::command('logs:prune --days=7')->daily();

// Security Heartbeat: Daily Integrity Check
// 1. Hourly Integrity Check
\Illuminate\Support\Facades\Schedule::command('integrity:check')->hourly();

// 2. Weekly Audit Report (Mondays @ 4am)
\Illuminate\Support\Facades\Schedule::command('integrity:report')->weeklyOn(1, '04:00');
