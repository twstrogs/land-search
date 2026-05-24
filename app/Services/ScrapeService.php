<?php

namespace App\Services;

use App\Models\FacebookGroup;
use App\Models\ScrapeLog;
use App\Models\Setting;
use App\Jobs\ScrapeFacebookGroupJob;
use Illuminate\Support\Facades\Log;

class ScrapeService
{
    public function dispatchScrapeJob(FacebookGroup $group): void
    {
        $group->markAsRunning();
        
        ScrapeLog::info('scrape_start', "Bắt đầu scrape group: {$group->name}", $group);
        
        ScrapeFacebookGroupJob::dispatch($group);
    }

    public function runAutoScrape(): void
    {
        $autoEnabled = Setting::getValue('scraper.auto_enabled', false);
        
        if (!$autoEnabled) {
            Log::info('Auto scrape skipped: disabled in settings');
            return;
        }

        $groups = FacebookGroup::enabled()
            ->where('status', FacebookGroup::STATUS_IDLE)
            ->orderBy('priority', 'desc')
            ->get();

        if ($groups->isEmpty()) {
            Log::info('Auto scrape: No groups available');
            return;
        }

        $concurrentJobs = Setting::getValue('scraper.concurrent_jobs', 3);
        $dailyLimit = Setting::getValue('scraper.daily_limit', 100);

        $dispatched = 0;

        foreach ($groups as $group) {
            if ($dispatched >= $concurrentJobs) {
                break;
            }

            if ($group->posts_scraped >= $dailyLimit) {
                continue;
            }

            $this->dispatchScrapeJob($group);
            $dispatched++;
        }

        ScrapeLog::info('auto_scrape', "Auto scrape đã khởi động cho {$dispatched} groups");
        
        Log::info("Auto scrape completed: dispatched {$dispatched} jobs");
    }

    public function getScheduledTime(): string
    {
        return Setting::getValue('scraper.schedule_time', '06:00');
    }
}
