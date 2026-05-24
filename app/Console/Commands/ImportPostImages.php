<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPostImages extends Command
{
    protected $signature = 'app:import-post-images {file : Path to JSON file with Facebook post data}';

    protected $description = 'Import/update images for posts from JSON file';

    public function handle(): int
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($file), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON file');
            return Command::FAILURE;
        }

        if (!is_array($data) || !isset($data[0])) {
            $data = [$data];
        }

        $this->info("Processing " . count($data) . " records...");

        $updated = 0;
        $notFound = 0;

        foreach ($data as $index => $item) {
            $text = trim($item['text'] ?? '');
            $attachments = $item['attachments'] ?? [];
            
            if (!$text) {
                continue;
            }

            // Find post by matching raw_content or title
            $post = Post::where('raw_content', 'LIKE', '%' . substr($text, 0, 50) . '%')
                       ->orWhere('title', 'LIKE', '%' . substr($text, 0, 30) . '%')
                       ->first();

            if (!$post) {
                $notFound++;
                continue;
            }

            $imageUrl = null;
            $images = [];

            foreach ($attachments as $attachment) {
                // Try photo_image first (high quality)
                if (!empty($attachment['photo_image']['uri'])) {
                    $url_img = $attachment['photo_image']['uri'];
                    if (!$imageUrl) {
                        $imageUrl = $url_img;
                    }
                    if (!in_array($url_img, $images)) {
                        $images[] = $url_img;
                    }
                }
                // Try image uri
                elseif (!empty($attachment['image']['uri'])) {
                    $url_img = $attachment['image']['uri'];
                    if (!$imageUrl) {
                        $imageUrl = $url_img;
                    }
                    if (!in_array($url_img, $images)) {
                        $images[] = $url_img;
                    }
                }
                // Try thumbnail as fallback
                elseif (!empty($attachment['thumbnail']) && str_contains($attachment['thumbnail'], 'scontent')) {
                    $url_img = $attachment['thumbnail'];
                    if (!$imageUrl) {
                        $imageUrl = $url_img;
                    }
                    if (!in_array($url_img, $images)) {
                        $images[] = $url_img;
                    }
                }
            }

            if ($imageUrl) {
                $updateData = ['image_url' => $imageUrl];
                if (!empty($images)) {
                    $updateData['images'] = $images;
                }
                $post->update($updateData);
                $this->line("Post #{$post->id} ('{$post->title}'): Updated image to '{$imageUrl}'" . (count($images) > 1 ? " + " . count($images) . " images" : ""));
                $updated++;
            }
        }

        $this->info("Updated {$updated} posts. Not found: {$notFound}");
        
        return Command::SUCCESS;
    }
}
