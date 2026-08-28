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
Schedule::command('tickets:close-expired')->hourly()->withoutOverlapping();
Schedule::command('sla:sweep')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('agent-tasks:reminder-sweep')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('email:resweep')->everyTenMinutes()->withoutOverlapping();
Schedule::command('chat:sweep-sessions')->everyFiveMinutes()->withoutOverlapping();
// TODO(465): fold tickets:close-expired into an automation rule once rules are seeded in production
Schedule::command('automation:sweep')->everyTenMinutes()->withoutOverlapping();
Schedule::command('report:schedule-sweep')->everyFiveMinutes()->withoutOverlapping();
