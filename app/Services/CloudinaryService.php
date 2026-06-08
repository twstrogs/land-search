<?php

namespace App\Services;

use App\Services\ImageDownloaderService;
use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CloudinaryService
{
    protected Cloudinary $cloudinary;
    protected ImageDownloaderService $imageDownloader;
    protected string $folder;
    protected array $uploadConfig;

    public function __construct(ImageDownloaderService $imageDownloader)
    {
        $this->imageDownloader = $imageDownloader;

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => config('cloudinary.url.secure', true),
            ],
        ]);

        $this->folder = config('cloudinary.upload.folder', 'land-search/posts');
        $this->uploadConfig = config('cloudinary.upload', []);
    }

    /**
     * Upload a single image from URL to Cloudinary
     *
     * @param string $url The source image URL
     * @return array|null Upload result with secure_url and public_id, or null if failed
     */
    public function uploadFromUrl(string $url): ?array
    {
        if (empty($url)) {
            return null;
        }

        // If already a Cloudinary URL, return existing data
        if (str_contains($url, 'res.cloudinary.com')) {
            return $this->extractFromCloudinaryUrl($url);
        }

        try {
            // Download the image first
            $localPath = $this->imageDownloader->downloadSingle($url);

            if (!$localPath) {
                Log::warning("Failed to download image before Cloudinary upload", ['url' => $url]);
                return null;
            }

            // Upload to Cloudinary
            $result = $this->uploadFromPath($localPath);

            // Clean up local file after successful upload
            $this->imageDownloader->deleteImage($localPath);

            return $result;

        } catch (\Exception $e) {
            Log::error("Cloudinary upload failed", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Upload a single image from local storage path to Cloudinary
     *
     * @param string $path Local storage path (e.g., "posts/abc123.jpg") or full URL
     * @return array|null Upload result with secure_url and public_id
     */
    public function uploadFromPath(string $path): ?array
    {
        if (empty($path)) {
            return null;
        }

        // Convert full URL to local path
        $localPath = $this->extractLocalPath($path);

        try {
            // Get full path for local file
            $fullPath = Storage::disk('public')->path($localPath);

            if (!file_exists($fullPath)) {
                Log::warning("Local file not found for Cloudinary upload", ['path' => $localPath, 'original' => $path]);
                return null;
            }

            // Generate unique public_id based on hash
            $hash = pathinfo($localPath, PATHINFO_FILENAME);
            $publicId = "{$this->folder}/{$hash}";

            // Upload to Cloudinary
            $result = $this->cloudinary->uploadApi()->upload($fullPath, [
                'folder' => $this->folder,
                'public_id' => $hash,
                'resource_type' => 'image',
                'quality' => $this->uploadConfig['transformation']['quality'] ?? 'auto',
                'fetch_format' => $this->uploadConfig['transformation']['fetch_format'] ?? 'auto',
            ]);

            Log::info("Image uploaded to Cloudinary", [
                'path' => $localPath,
                'public_id' => $result['public_id'],
                'size_bytes' => $result['bytes'],
            ]);

            return [
                'public_id' => $result['public_id'],
                'secure_url' => $result['secure_url'],
                'url' => $result['url'] ?? $result['secure_url'],
                'format' => $result['format'],
                'width' => $result['width'],
                'height' => $result['height'],
                'bytes' => $result['bytes'],
                'created_at' => $result['created_at'] ?? now()->toIso8601String(),
            ];

        } catch (\Exception $e) {
            Log::error("Cloudinary upload from path failed", [
                'path' => $localPath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract local path from full URL or return as-is if already a path
     *
     * @param string $input Full URL or local path
     * @return string Local path
     */
    public function extractLocalPath(string $input): string
    {
        // If it's already a local path, return as-is
        if (!str_starts_with($input, 'http://') && !str_starts_with($input, 'https://')) {
            return $input;
        }

        // Extract path from URL
        // Format: http://localhost/storage/posts/filename.jpg
        // Result: posts/filename.jpg
        $parsed = parse_url($input, PHP_URL_PATH);
        $path = ltrim($parsed, '/');

        // Remove 'storage/' prefix if present
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return $path;
    }

    /**
     * Upload multiple images from URLs to Cloudinary
     *
     * @param array $urls Array of image URLs
     * @return array Array of Cloudinary URLs (secure_url)
     */
    public function uploadMultipleFromUrls(array $urls): array
    {
        $cloudinaryUrls = [];

        foreach ($urls as $url) {
            $result = $this->uploadFromUrl($url);
            if ($result) {
                $cloudinaryUrls[] = $result['secure_url'];
            }
        }

        return $cloudinaryUrls;
    }

    /**
     * Upload multiple images from local paths to Cloudinary
     *
     * @param array $paths Array of local storage paths
     * @return array Array of Cloudinary URLs (secure_url)
     */
    public function uploadMultipleFromPaths(array $paths): array
    {
        $cloudinaryUrls = [];

        foreach ($paths as $path) {
            $result = $this->uploadFromPath($path);
            if ($result) {
                $cloudinaryUrls[] = $result['secure_url'];
            }
        }

        return $cloudinaryUrls;
    }

    /**
     * Get image details from Cloudinary
     *
     * @param string $publicId The public ID of the image
     * @return array|null Image metadata
     */
    public function getImageDetails(string $publicId): ?array
    {
        try {
            $result = $this->cloudinary->adminApi()->asset($publicId);

            return [
                'public_id' => $result['public_id'],
                'format' => $result['format'],
                'width' => $result['width'],
                'height' => $result['height'],
                'bytes' => $result['bytes'],
                'secure_url' => $result['secure_url'],
            ];

        } catch (\Exception $e) {
            Log::error("Failed to get Cloudinary image details", [
                'public_id' => $publicId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $publicId The public ID of the image
     * @return bool True if deleted successfully
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            $this->cloudinary->adminApi()->deleteAssets([$publicId]);

            Log::info("Image deleted from Cloudinary", ['public_id' => $publicId]);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to delete Cloudinary image", [
                'public_id' => $publicId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate a transformed URL (for display optimization)
     *
     * @param string $publicId The public ID of the image
     * @param string $transformation Transformation string (e.g., "f_auto,q_auto")
     * @return string The transformed URL
     */
    public function getTransformedUrl(string $publicId, string $transformation = 'f_auto,q_auto'): string
    {
        $cloudName = config('cloudinary.cloud_name');
        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$transformation}/{$publicId}";
    }

    /**
     * Generate optimized URL with f_auto (auto format) and q_auto (auto quality)
     *
     * @param string $publicId The public ID of the image
     * @return string The optimized URL
     */
    public function getOptimizedUrl(string $publicId): string
    {
        return $this->getTransformedUrl($publicId, 'f_auto,q_auto');
    }

    /**
     * Extract public_id from a Cloudinary URL
     *
     * @param string $url The Cloudinary URL
     * @return array|null Extracted data
     */
    protected function extractFromCloudinaryUrl(string $url): ?array
    {
        // Parse URL like: https://res.cloudinary.com/{cloud_name}/image/upload/v1234567890/folder/image.jpg
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+)$/', $url, $matches)) {
            $publicId = $matches[1];
            // Remove extension
            $publicId = preg_replace('/\.[^.]+$/', '', $publicId);

            return [
                'public_id' => $publicId,
                'secure_url' => $url,
                'url' => $url,
            ];
        }

        return null;
    }

    /**
     * Check if URL is already a Cloudinary URL
     *
     * @param string $url The URL to check
     * @return bool
     */
    public function isCloudinaryUrl(string $url): bool
    {
        return str_contains($url, 'res.cloudinary.com');
    }
}
