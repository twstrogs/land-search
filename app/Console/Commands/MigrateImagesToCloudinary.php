<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\CloudinaryService;
use App\Services\ImageDownloaderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateImagesToCloudinary extends Command
{
    protected $signature = 'cloudinary:migrate
                            {--dry-run : Show what would be migrated without uploading}
                            {--limit= : Limit number of posts to process}
                            {--chunk=100 : Process in chunks}
                            {--force : Force re-upload even if already cloudinary URL}';

    protected $description = 'Migrate existing images from local storage to Cloudinary';

    public function handle(CloudinaryService $cloudinaryService, ImageDownloaderService $imageDownloader): int
    {
        $isDryRun = $this->option('dry-run');
        $limit = $this->option('limit');
        $chunkSize = (int) $this->option('chunk');
        $force = $this->option('force');

        if ($isDryRun) {
            $this->warn('=== DRY RUN MODE - No images will be uploaded ===' . PHP_EOL);
        }

        // Get posts with local image URLs (localhost/storage/...)
        $query = Post::whereNotNull('image_url')
            ->where('image_url', '!=', '');

        if (!$force) {
            // Only migrate posts that haven't been migrated yet
            $query->where(function ($q) {
                $q->where('image_url', 'LIKE', 'http://localhost/storage/%')
                  ->orWhere('image_url', 'LIKE', 'http://127.0.0.1/storage/%')
                  ->orWhere('image_url', 'LIKE', '%/storage/posts/%');
            });
        }

        if ($limit) {
            $query->limit((int) $limit);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No local images to migrate.');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} posts with local images to migrate.");
        $this->info("Processing in chunks of {$chunkSize}..." . PHP_EOL);

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        $query->chunk($chunkSize, function ($posts) use ($cloudinaryService, $imageDownloader, $isDryRun, &$success, &$failed, &$skipped, $bar) {

            foreach ($posts as $post) {
                $localImages = $post->images ?? [];

                if (empty($localImages)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if ($isDryRun) {
                    $this->line(PHP_EOL . "Post #{$post->id}: Would migrate " . count($localImages) . " images");
                    $success++;
                    $bar->advance();
                    continue;
                }

                // Upload images to Cloudinary
                $cloudinaryUrls = [];

                foreach ($localImages as $imageItem) {
                    // Handle both string (URL/path) and array format
                    $localPath = is_array($imageItem) ? ($imageItem['url'] ?? $imageItem['path'] ?? '') : $imageItem;

                    if (empty($localPath)) {
                        continue;
                    }

                    // Skip if already a Cloudinary URL
                    if ($cloudinaryService->isCloudinaryUrl($localPath)) {
                        $cloudinaryUrls[] = $localPath;
                        continue;
                    }

                    $result = $cloudinaryService->uploadFromPath($localPath);

                    if ($result) {
                        $cloudinaryUrls[] = $result['secure_url'];
                        $this->line(PHP_EOL . "  Uploaded: " . substr($localPath, -40) . " -> " . substr($result['secure_url'], -40));
                    } else {
                        $this->warn(PHP_EOL . "  Failed: " . substr($localPath, -40));
                        $failed++;
                    }
                }

                // Update post with Cloudinary URLs
                if (!empty($cloudinaryUrls)) {
                    $post->update([
                        'image_url' => $cloudinaryUrls[0],
                        'images' => $cloudinaryUrls,
                    ]);

                    // Delete local files after successful upload (only if they exist on disk)
                    $localPath = $cloudinaryService->extractLocalPath($localPath);
                    if (!str_starts_with($localPath, 'http://') && !str_starts_with($localPath, 'https://')) {
                        $imageDownloader->deleteImage($localPath);
                    }

                    $success++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Migration Complete ===');
        $this->line("  Total processed: {$total}");
        $this->line("  Success: {$success}");
        $this->line("  Failed: {$failed}");
        $this->line("  Skipped: {$skipped}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
