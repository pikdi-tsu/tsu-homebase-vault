<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('health:check')->everyMinute();

Schedule::command('model:prune', [
    '--model' => [\Spatie\Health\Models\HealthCheckResultHistoryItem::class],
])->monthly();

Schedule::command(\Spatie\Health\Commands\ScheduleCheckHeartbeatCommand::class)->everyMinute();
