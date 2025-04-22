<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Define the application's command schedule
Schedule::command('app:scrape-arsenal-tickets')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/arsenal-scraper.log'));
