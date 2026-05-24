<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageDownloaderService
{
    protected int $maxSizeBytes;
    protected int $timeoutSeconds;
    protected int $maxImagesPerPost;

    public function __construct()
    {
        $this->maxSizeBytes = (int) config('landsearch.images.max_size_bytes', 5 * 1024 * 1024);
        $this->timeoutSeconds = (int) config('landsearch.images.timeout_seconds', 30);
        $this->maxImagesPerPost = (int) config('landsearch.images.max_per_post', 10);
    }

    /**
     * Download multiple images from URLs
     *
     * @param array $urls Array of image URLs
     * @return array Array of local storage paths
     */
    public function downloadImages(array $urls): array
    {
        if (empty($urls)) {
            return [];
        }

        $localPaths = [];
        $count = 0;

        foreach ($urls as $url) {
            if ($count >= $this->maxImagesPerPost) {
                break;
            }

            if (!$this->isValidImageUrl($url)) {
                continue;
            }

            $localPath = $this->downloadSingle($url);
            if ($localPath) {
                $localPaths[] = $localPath;
                $count++;
            }
        }

        return $localPaths;
    }

    /**
     * Download a single image and store locally
     *
     * @param string $url Image URL
     * @return string|null Local storage path or null if failed
     */
    public function downloadSingle(string $url): ?string
    {
        if (empty($url) || !$this->isValidImageUrl($url)) {
            return null;
        }

        try {
            $hash = $this->generateHash($url);
            $filename = "{$hash}.jpg";
            $path = "posts/{$filename}";

            // Check if already downloaded
            if (Storage::disk('public')->exists($path)) {
                Log::debug("Image already exists: {$path}");
                return $path;
            }

            // Download image
            $response = Http::timeout($this->timeoutSeconds)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Referer' => 'https://www.facebook.com/',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning("Failed to download image: HTTP {$response->status()}", [
                    'url' => $url,
                ]);
                return null;
            }

            // Validate content type
            $contentType = $response->header('Content-Type', '');
            if (!str_starts_with($contentType, 'image/')) {
                Log::warning("Invalid content type: {$contentType}", [
                    'url' => $url,
                ]);
                return null;
            }

            // Validate and resize if needed
            $content = $response->body();
            $contentLength = strlen($content);

            if ($contentLength > $this->maxSizeBytes) {
                Log::warning("Image too large: {$contentLength} bytes", [
                    'url' => $url,
                    'max_size' => $this->maxSizeBytes,
                ]);
                return null;
            }

            if ($contentLength < 1000) {
                Log::warning("Image too small (likely placeholder)", [
                    'url' => $url,
                    'size' => $contentLength,
                ]);
                return null;
            }

            // Save to storage
            Storage::disk('public')->put($path, $content);

            Log::info("Image downloaded successfully", [
                'url' => $url,
                'path' => $path,
                'size' => $contentLength,
            ]);

            return $path;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("Connection error downloading image", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Failed to download image", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if URL is a valid image URL
     */
    public function isValidImageUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Must be HTTP or HTTPS
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return false;
        }

        // Skip data URLs
        if (str_starts_with($url, 'data:')) {
            return false;
        }

        return true;
    }

    /**
     * Generate hash from URL for consistent naming
     */
    public function generateHash(string $url): string
    {
        return hash('sha256', $url);
    }

    /**
     * Delete image from storage
     */
    public function deleteImage(string $path): bool
    {
        try {
            return Storage::disk('public')->delete($path);
        } catch (\Exception $e) {
            Log::error("Failed to delete image", [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete multiple images from storage
     */
    public function deleteImages(array $paths): int
    {
        $deleted = 0;
        foreach ($paths as $path) {
            if ($this->deleteImage($path)) {
                $deleted++;
            }
        }
        return $deleted;
    }

    /**
     * Check if image exists in storage
     */
    public function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Get image URL from storage path
     */
    public function getUrl(string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // If already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Convert storage path to URL
        return url('storage/' . ltrim($path, '/'));
    }

    /**
     * Get image URLs from storage paths
     */
    public function getUrls(array $paths): array
    {
        return array_map(fn($path) => $this->getUrl($path), $paths);
    }

    /**
     * Get disk usage for posts directory
     */
    public function getDiskUsage(): array
    {
        $totalSize = 0;
        $fileCount = 0;

        $files = Storage::disk('public')->files('posts');
        foreach ($files as $file) {
            $size = Storage::disk('public')->size($file);
            $totalSize += $size;
            $fileCount++;
        }

        return [
            'total_size_bytes' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'file_count' => $fileCount,
        ];
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        $size = $bytes;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
