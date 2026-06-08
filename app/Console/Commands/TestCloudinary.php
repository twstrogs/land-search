<?php

namespace App\Console\Commands;

use App\Services\CloudinaryService;
use Cloudinary\Cloudinary;
use Illuminate\Console\Command;

class TestCloudinary extends Command
{
    protected $signature = 'cloudinary:test';

    protected $description = 'Test Cloudinary integration - upload, get details, and transform';

    public function handle(): int
    {
        $this->info('=== Cloudinary Integration Test ===' . PHP_EOL);

        // Configure Cloudinary
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        // STEP 1: Upload a sample image from Cloudinary's demo domain
        $this->info('STEP 1: Uploading sample image...');
        $sampleImageUrl = 'https://res.cloudinary.com/demo/image/upload/sample.jpg';

        try {
            // Fetch the sample image
            $imageContent = file_get_contents($sampleImageUrl);
            if ($imageContent === false) {
                $this->error('Failed to fetch sample image');
                return Command::FAILURE;
            }

            // Save temporarily
            $tempFile = tempnam(sys_get_temp_dir(), 'cloudinary_test_') . '.jpg';
            file_put_contents($tempFile, $imageContent);

            // Upload to Cloudinary
            $uploadResult = $cloudinary->uploadApi()->upload($tempFile, [
                'folder' => config('cloudinary.upload.folder', 'land-search/posts'),
                'public_id' => 'test_' . time(),
                'resource_type' => 'image',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ]);

            // Clean up temp file
            unlink($tempFile);

            $publicId = $uploadResult['public_id'];
            $secureUrl = $uploadResult['secure_url'];

            $this->info("✓ Uploaded successfully!");
            $this->line("  Public ID: {$publicId}");
            $this->line("  Secure URL: {$secureUrl}");

        } catch (\Exception $e) {
            $this->error("✗ Upload failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        // STEP 2: Get image details
        $this->info(PHP_EOL . 'STEP 2: Getting image details...');

        try {
            // Use the Admin API to get resource details
            $details = $cloudinary->adminApi()->asset($publicId);

            $this->info("✓ Details retrieved:");
            $this->line("  Width: {$details['width']} px");
            $this->line("  Height: {$details['height']} px");
            $this->line("  Format: {$details['format']}");
            $this->line("  File size: " . number_format($details['bytes']) . " bytes");

        } catch (\Exception $e) {
            $this->error("✗ Failed to get details: " . $e->getMessage());
        }

        // STEP 3: Transform the image
        $this->info(PHP_EOL . 'STEP 3: Generating transformed URL...');

        // f_auto: Automatically selects the optimal format (WebP, AVIF, etc.)
        // q_auto: Automatically selects the optimal quality compression
        $transformation = 'f_auto,q_auto';

        // Build transformed URL manually (SDK v3 doesn't have direct url() method)
        $cloudName = config('cloudinary.cloud_name');
        $transformedUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/{$transformation}/{$publicId}.jpg";

        $this->info("✓ Transformed URL generated:");
        $this->line("  Transformation: {$transformation}");
        $this->line("  URL: {$transformedUrl}");

        // STEP 4: Cleanup - Delete test image
        $this->info(PHP_EOL . 'STEP 4: Cleaning up test image...');

        try {
            $cloudinary->adminApi()->deleteAssets([$publicId]);
            $this->info("✓ Test image deleted from Cloudinary");
        } catch (\Exception $e) {
            $this->warn("⚠ Could not delete test image: " . $e->getMessage());
        }

        $this->info(PHP_EOL . '=== Done! Click link below to see optimized version ===');
        $this->info($transformedUrl);
        $this->info(PHP_EOL . 'Check the size and format by opening the URL above.');

        return Command::SUCCESS;
    }
}
