<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Models\Post;
use App\Models\SourceRecord;
use App\Models\ScrapeLog;
use App\Models\ExtractionLog;
use App\Models\PostEmbedding;
use App\Services\AI\AiServiceFactory;
use App\Services\FeatureExtractionService;
use App\Services\ImageDownloaderService;
use App\Services\NormalizeService;
use App\Search\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSourceRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public ImportBatch $batch,
        public array $data,
        public ?string $sourceGroup = null
    ) {}

    public function handle(
        NormalizeService $normalizeService,
        ImageDownloaderService $imageDownloader,
        FeatureExtractionService $featureService
    ): void {
        // Support both Apify format (text) and normalized format (raw_content)
        $rawContent = $this->data['raw_content']
            ?? $this->data['text']
            ?? $this->data['rawText']
            ?? '';

        // Always create a source row so admin can inspect skipped/failed reasons.
        $sourceRecord = SourceRecord::create([
            'import_batch_id' => $this->batch->id,
            'raw_content' => (string) $rawContent,
            'raw_content_hash' => hash('sha256', mb_strtolower(trim((string) $rawContent))),
            'raw_json' => $this->data,
            'status' => 'pending',
        ]);

        if (empty($rawContent)) {
            $sourceRecord->update([
                'status' => 'skipped',
                'error_message' => 'Bỏ qua vì raw_content rỗng',
            ]);
            $this->batch->increment('skipped_records');
            return;
        }

        // Check for duplicates
        $existingPost = Post::where('raw_content_hash', $sourceRecord->raw_content_hash)->first();

        if ($existingPost) {
            $sourceRecord->update([
                'status' => 'skipped',
                'error_message' => 'Trùng nội dung với post #'.$existingPost->id,
            ]);
            $this->batch->increment('duplicate_records');
            return;
        }

        try {
            // Download images first (before AI extraction to save time)
            $imageUrls = [];

            // Support multiple field names from different sources
            if (!empty($this->data['image_url'])) {
                $imageUrls[] = $this->data['image_url'];
            }
            if (!empty($this->data['image'])) {
                $imageUrls[] = $this->data['image'];
            }
            if (!empty($this->data['images']) && is_array($this->data['images'])) {
                $imageUrls = array_merge($imageUrls, $this->data['images']);
            }

            // Extract from Apify attachments
            if (!empty($this->data['attachments']) && is_array($this->data['attachments'])) {
                foreach ($this->data['attachments'] as $attachment) {
                    if (!empty($attachment['image']['uri'])) {
                        $imageUrls[] = $attachment['image']['uri'];
                    }
                }
            }

            // Download and store images locally
            $localImagePaths = [];
            if (!empty($imageUrls)) {
                $localImagePaths = $imageDownloader->downloadImages($imageUrls);
                Log::debug("Downloaded images for post", [
                    'source_record_id' => $sourceRecord->id,
                    'urls_count' => count($imageUrls),
                    'downloaded_count' => count($localImagePaths),
                ]);
            }

            // AI Extraction with timing
            $startTime = microtime(true);
            $extracted = AiServiceFactory::extract($rawContent);
            $processingTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            if (isset($extracted['error'])) {
                throw new \Exception($extracted['error']);
            }

            // ===== TASK 1: Create ExtractionLog =====
            $extractionLog = ExtractionLog::create([
                'post_id' => null, // Will update after post creation
                'raw_content' => $rawContent,
                'raw_response' => $extracted,
                'normalized_data' => $extracted,
                'confidence_score' => $extracted['confidence'] ?? 0.5,
                'processing_time_ms' => $processingTimeMs,
                'validation_status' => 'pending',
                'ai_provider' => $extracted['ai_provider'] ?? AiServiceFactory::getDefaultProvider(),
                'ai_model' => $extracted['ai_model'] ?? null,
            ]);

            // Normalize data
            $normalized = $normalizeService->normalize($extracted);
            $normalized['raw_content'] = $rawContent;
            $normalized['raw_content_hash'] = $sourceRecord->raw_content_hash;
            $normalized['user_id'] = $this->batch->user_id;
            $normalized['published_at'] = $this->data['published_at']
                ?? $this->data['createdAt']
                ?? $this->data['time']
                ?? now();
            $normalized['author_id'] = $this->data['author_id']
                ?? $this->data['user']['id']
                ?? null;
            $normalized['author_name'] = $this->data['author_name']
                ?? $this->data['user']['name']
                ?? null;

            // Store local paths for images (not Facebook URLs)
            $normalized['images'] = $localImagePaths;
            $normalized['image_url'] = $localImagePaths[0] ?? null;

            $normalized['facebook_url'] = $this->data['facebook_url']
                ?? $this->data['url']
                ?? null;
            $normalized['source_group'] = $this->sourceGroup ?? null;
            $normalized['hash'] = hash('sha256', json_encode($normalized));

            // Create post
            $post = Post::create($normalized);

            // ===== TASK 2: Update ExtractionLog with post_id =====
            $extractionLog->update(['post_id' => $post->id]);

            // ===== TASK 3: Validate and update ExtractionLog status =====
            $validationResult = $this->validateExtraction($extracted, $normalized);
            $extractionLog->update([
                'validation_status' => $validationResult['status'],
                'validation_errors' => $validationResult['errors'],
            ]);

            // Link to source record
            $sourceRecord->update([
                'post_id' => $post->id,
                'status' => 'completed',
            ]);

            // ===== TASK 3: Create AiExtraction record =====
            $post->aiExtractions()->create([
                'source_record_id' => $sourceRecord->id,
                'raw_response' => $extracted,
                'normalized_data' => $normalized,
                'confidence' => $extracted['confidence'] ?? 0.5,
                'processing_time_ms' => $processingTimeMs,
                'ai_provider' => $extracted['ai_provider'] ?? AiServiceFactory::getDefaultProvider(),
                'ai_model' => $extracted['ai_model'] ?? null,
            ]);

            // ===== TASK 4: Create Contact records =====
            if (!empty($extracted['phones']) && is_array($extracted['phones'])) {
                foreach ($extracted['phones'] as $phone) {
                    if (!empty($phone)) {
                        $post->contacts()->create([
                            'phone' => $phone,
                            'label' => 'Liên hệ',
                        ]);
                    }
                }
            }

            // ===== FEATURES: Sync features to database =====
            // Normalize raw features from AI to standard codes (including directions)
            $normalizedFeatures = $featureService->normalizeFeatures(
                $extracted['features'] ?? [],
                $rawContent // Pass raw text for better direction extraction
            );

            // Sync to feature_post pivot table
            if (!empty($normalizedFeatures)) {
                $featureService->syncFeatures($normalizedFeatures, $post);
            }

            // Update batch progress
            $this->batch->increment('processed_records');

            // ===== TASK 3: Create PostEmbedding for semantic search =====
            $this->createPostEmbedding($post, $rawContent);

            Log::info("Processed source record", [
                'post_id' => $post->id,
                'extraction_log_id' => $extractionLog->id,
                'images_downloaded' => count($localImagePaths),
                'phones_found' => count($extracted['phones'] ?? []),
                'features_found' => count($normalizedFeatures),
            ]);

        } catch (\Exception $e) {
            $sourceRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $this->batch->increment('failed_records');

            Log::error("Process source record failed", [
                'source_record_id' => $sourceRecord->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            $this->refreshBatchStatus();
        }
    }

    /**
     * Create embedding for semantic search
     */
    private function createPostEmbedding(Post $post, string $rawContent): void
    {
        try {
            $embeddingService = app(EmbeddingService::class);

            // Build searchable content combining AI extracted data and raw content
            $searchableContent = $embeddingService->buildSearchableContent($post);
            
            // Append raw content for more context
            if (!empty($rawContent)) {
                $searchableContent .= ". " . mb_substr($rawContent, 0, 500);
            }

            // Generate embedding
            $embedding = $embeddingService->embed($searchableContent);

            // Save to database
            PostEmbedding::updateOrCreate(
                ['post_id' => $post->id],
                [
                    'embedding' => $embedding,
                    'embedding_model' => $embeddingService->getProviderInfo()['model'],
                    'indexed_at' => now(),
                ]
            );

            Log::debug("Created post embedding", [
                'post_id' => $post->id,
                'embedding_model' => $embeddingService->getProviderInfo()['model'],
            ]);

        } catch (\Exception $e) {
            // Don't fail the whole job if embedding fails
            Log::warning("Failed to create post embedding", [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate extraction results
     */
    private function validateExtraction(array $extracted, array $normalized): array
    {
        $errors = [];

        // Check required fields
        $requiredFields = ['price_value', 'area_value'];
        foreach ($requiredFields as $field) {
            if (empty($normalized[$field])) {
                $errors[] = [
                    'field' => $field,
                    'message' => "Trường {$field} trống",
                    'severity' => 'warning',
                ];
            }
        }

        // Validate price range
        if (!empty($normalized['price_value'])) {
            if ($normalized['price_value'] < 1_000_000) {
                $errors[] = [
                    'field' => 'price_value',
                    'message' => 'Giá quá thấp (dưới 1 triệu)',
                    'severity' => 'error',
                ];
            }
            if ($normalized['price_value'] > 100_000_000_000) {
                $errors[] = [
                    'field' => 'price_value',
                    'message' => 'Giá quá cao (trên 100 tỷ)',
                    'severity' => 'error',
                ];
            }
        }

        // Validate area range
        if (!empty($normalized['area_value'])) {
            if ($normalized['area_value'] < 1) {
                $errors[] = [
                    'field' => 'area_value',
                    'message' => 'Diện tích quá nhỏ (dưới 1m²)',
                    'severity' => 'error',
                ];
            }
            if ($normalized['area_value'] > 100_000) {
                $errors[] = [
                    'field' => 'area_value',
                    'message' => 'Diện tích quá lớn (trên 100,000m²)',
                    'severity' => 'error',
                ];
            }
        }

        // Determine status based on errors
        $hasErrors = collect($errors)->contains('severity', 'error');
        $hasWarnings = collect($errors)->contains('severity', 'warning');

        $status = match (true) {
            $hasErrors => 'failed',
            $hasWarnings => 'warning',
            default => 'passed',
        };

        return [
            'status' => $status,
            'errors' => $errors,
        ];
    }

    private function refreshBatchStatus(): void
    {
        $batch = ImportBatch::find($this->batch->id);
        if (! $batch) {
            return;
        }

        $done = $batch->processed_records + $batch->failed_records + $batch->skipped_records + $batch->duplicate_records;

        if ($done >= $batch->total_records && $batch->status !== 'completed') {
            $batch->update(['status' => 'completed']);
        } elseif ($done < $batch->total_records && $batch->status === 'pending') {
            $batch->update(['status' => 'processing']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->batch->increment('failed_records');
        $this->refreshBatchStatus();

        Log::error("ProcessSourceRecordJob failed permanently", [
            'batch_id' => $this->batch->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
