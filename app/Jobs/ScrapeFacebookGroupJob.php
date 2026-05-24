<?php

namespace App\Jobs;

use App\Models\FacebookGroup;
use App\Models\ImportBatch;
use App\Models\ScrapeLog;
use App\Models\Setting;
use App\Services\AI\AiServiceFactory;
use App\Services\ApifyService;
use App\Services\NormalizeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeFacebookGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public FacebookGroup $group
    ) {}

    public function handle(
        NormalizeService $normalizeService,
        ApifyService $apifyService
    ): void {
        $apifyToken = Setting::getValue('scraper.apify_token');
        $scrapeLimit = $this->group->scrape_limit;
        
        ScrapeLog::info('scrape_processing', "Đang xử lý group: {$this->group->name}", $this->group);

        try {
            // Fetch posts from Facebook via Apify
            $posts = $apifyService->scrape($this->group->url, $apifyToken, $scrapeLimit);

            if (empty($posts)) {
                $this->group->markAsCompleted(0);
                ScrapeLog::warning('scrape_no_posts', "Không có bài viết nào được scrape từ: {$this->group->name}", $this->group);
                return;
            }

            // Create import batch
            $batch = ImportBatch::create([
                'user_id' => null,
                'name' => "Scrape: {$this->group->name}",
                'source' => 'facebook_scrape',
                'ai_provider' => AiServiceFactory::getDefaultProvider(),
                'total_records' => count($posts),
                'status' => 'pending',
            ]);

            // ===== TASK 5: Save full JSON to file =====
            $this->saveScrapeJsonFile($batch, $posts, $this->group);

            // Process each post
            $processedCount = 0;
            foreach ($posts as $postData) {
                ProcessSourceRecordJob::dispatch($batch, $postData, $this->group->name);
                $processedCount++;
            }

            $this->group->markAsCompleted($processedCount);
            
            ScrapeLog::success('scrape_completed', "Đã scrape {$processedCount} bài viết từ: {$this->group->name}", $this->group);
            
            Log::info("Scrape completed for group {$this->group->name}: {$processedCount} posts");

        } catch (\Exception $e) {
            $this->group->markAsFailed($e->getMessage());
            
            ScrapeLog::error('scrape_failed', "Scrape failed: {$e->getMessage()}", $this->group, null, [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            
            Log::error("Scrape failed for group {$this->group->name}: {$e->getMessage()}");
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->group->markAsFailed($exception->getMessage());
        
        ScrapeLog::error('scrape_job_failed', "Scrape job failed after {$this->tries} retries: {$exception->getMessage()}", $this->group);
    }

    /**
     * ===== TASK 5: Save full scrape JSON to file =====
     * Creates a comprehensive JSON file for the entire scrape batch
     */
    protected function saveScrapeJsonFile(ImportBatch $batch, array $posts, FacebookGroup $group): void
    {
        try {
            // Build comprehensive scrape data
            $scrapeData = [
                'metadata' => [
                    'scrape_id' => $batch->id,
                    'group_name' => $group->name,
                    'group_url' => $group->url,
                    'scrape_at' => now()->toIso8601String(),
                    'total_posts' => count($posts),
                    'ai_provider' => AiServiceFactory::getDefaultProvider(),
                ],
                'posts' => $posts,
            ];

            // Generate filename: scrape_{batch_id}_{timestamp}.json
            $filename = sprintf(
                'scrape_%d_%s.json',
                $batch->id,
                now()->format('Ymd_His')
            );

            $path = "scrapes/{$filename}";

            // Save to storage
            \Illuminate\Support\Facades\Storage::disk('local')->put(
                $path,
                json_encode($scrapeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            Log::info("Scrape JSON file saved", [
                'batch_id' => $batch->id,
                'path' => $path,
                'posts_count' => count($posts),
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the scrape job
            Log::error("Failed to save scrape JSON file", [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
