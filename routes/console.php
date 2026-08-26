<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('backup:verify')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('retention:purge')->dailyAt('03:30')->withoutOverlapping();
