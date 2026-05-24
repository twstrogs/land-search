<?php

namespace App\Console\Commands;

use App\Models\FacebookGroup;
use App\Services\ScrapeService;
use Illuminate\Console\Command;

class ScrapeCommand extends Command
{
    protected $signature = 'app:scrape {group_id? : ID of Facebook Group to scrape}';
    protected $description = 'Manually scrape a Facebook Group';

    public function handle(ScrapeService $scrapeService): int
    {
        $groupId = $this->argument('group_id');

        if ($groupId) {
            $group = FacebookGroup::find($groupId);
            
            if (!$group) {
                $this->error("Facebook Group #{$groupId} not found.");
                return Command::FAILURE;
            }

            $this->info("Starting scrape for: {$group->name}");
            $scrapeService->dispatchScrapeJob($group);
            $this->info("Scrape job dispatched successfully!");
            
            return Command::SUCCESS;
        }

        // Run auto scrape for all enabled groups
        $this->info('Running auto scrape...');
        $scrapeService->runAutoScrape();
        $this->info('Auto scrape completed!');
        
        return Command::SUCCESS;
    }
}
