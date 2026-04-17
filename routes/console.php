<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Study Planner: daily reminders at 7 AM
Schedule::command('planner:send-reminders')->dailyAt('07:00');

// Motivational: comeback + streak milestones at 8 PM
Schedule::command('motivation:send-reminders')->dailyAt('20:00');
