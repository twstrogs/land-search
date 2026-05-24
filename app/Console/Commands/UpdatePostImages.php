<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\SourceRecord;
use Illuminate\Console\Command;

class UpdatePostImages extends Command
{
    protected $signature = 'app:update-post-images {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Update image URLs for existing posts from source records';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $posts = Post::whereNotNull('image_url')
            ->where(function ($q) {
                $q->where('image_url', 'like', '%facebook.com/photo.php%')
                  ->orWhere('image_url', 'not like', '%scontent%');
            })
            ->with('sourceRecord')
            ->get();

        $this->info("Found {$posts->count()} posts with incorrect image URLs.");

        if ($posts->isEmpty()) {
            $this->info('No posts need updating.');
            return Command::SUCCESS;
        }

        $updated = 0;

        foreach ($posts as $post) {
            $sourceRecord = $post->sourceRecord;
            
            if (!$sourceRecord || !$sourceRecord->raw_json) {
                $this->warn("Post #{$post->id}: No source record found, skipping.");
                continue;
            }

            $rawJson = is_string($sourceRecord->raw_json) 
                ? json_decode($sourceRecord->raw_json, true) 
                : $sourceRecord->raw_json;

            $imageUrl = $this->extractFirstImageUrl($rawJson);
            $allImages = $this->extractAllImageUrls($rawJson);

            if ($dryRun) {
                $this->line("Post #{$post->id}: Would update image_url from '{$post->image_url}' to '{$imageUrl}'");
                if (count($allImages) > 1) {
                    $this->line("  + Would add " . count($allImages) . " images");
                }
                $updated++;
            } else {
                $post->update([
                    'image_url' => $imageUrl,
                    'images' => $allImages ?: null,
                ]);
                $this->line("Post #{$post->id}: Updated image_url to '{$imageUrl}'");
                if (count($allImages) > 1) {
                    $this->info("  + Added " . count($allImages) . " images");
                }
                $updated++;
            }
        }

        $this->info("Processed {$updated} posts.");
        
        return Command::SUCCESS;
    }

    private function extractFirstImageUrl(array $rawJson): ?string
    {
        $attachments = $rawJson['attachments'] ?? [];
        
        foreach ($attachments as $attachment) {
            if (!empty($attachment['photo_image']['uri'])) {
                return $attachment['photo_image']['uri'];
            }
            
            if (!empty($attachment['image']['uri'])) {
                return $attachment['image']['uri'];
            }
            
            if (!empty($attachment['thumbnail']) && str_contains($attachment['thumbnail'], 'scontent')) {
                return $attachment['thumbnail'];
            }
        }
        
        return null;
    }

    private function extractAllImageUrls(array $rawJson): array
    {
        $images = [];
        $attachments = $rawJson['attachments'] ?? [];
        
        foreach ($attachments as $attachment) {
            $url = null;
            
            if (!empty($attachment['photo_image']['uri'])) {
                $url = $attachment['photo_image']['uri'];
            } elseif (!empty($attachment['image']['uri'])) {
                $url = $attachment['image']['uri'];
            } elseif (!empty($attachment['thumbnail']) && str_contains($attachment['thumbnail'], 'scontent')) {
                $url = $attachment['thumbnail'];
            }
            
            if ($url && !in_array($url, $images)) {
                $images[] = $url;
            }
        }
        
        return $images;
    }
}
