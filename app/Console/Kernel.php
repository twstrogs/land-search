<?php

namespace App\Console;

use App\Services\ScrapeService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\InitSettings::class,
        Commands\ScrapeCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Auto scrape - runs at configured time daily
        $schedule->call(function () {
            $scrapeService = app(ScrapeService::class);
            $scrapeService->runAutoScrape();
        })
        ->timezone('Asia/Ho_Chi_Minh')
        ->dailyAt(config('services.scraper.schedule_time', '06:00'))
        ->withoutOverlapping()
        ->runInBackground()
        ->appendOutputTo(storage_path('logs/scrape.log'));

        // Check for stuck jobs every 5 minutes
        $schedule->call(function () {
            \App\Models\FacebookGroup::where('status', 'running')
                ->where('updated_at', '<', now()->subMinutes(30))
                ->update(['status' => 'idle']);
        })
        ->everyFiveMinutes()
        ->timezone('Asia/Ho_Chi_Minh');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
