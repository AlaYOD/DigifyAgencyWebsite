<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('careers:close-expired')->hourly();
Schedule::command('forms:purge-expired')->dailyAt('02:30')->withoutOverlapping();
