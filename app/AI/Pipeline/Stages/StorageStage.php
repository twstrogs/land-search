<?php

namespace App\AI\Pipeline\Stages;

use App\AI\Pipeline\PipelineStageInterface;
use App\AI\Exceptions\StageException;
use App\Models\Post;
use App\Models\AiExtraction;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Storage Stage
 * 
 * Handles storing processed data:
 * - Create/update Post
 * - Create AiExtraction record
 * - Sync features
 * - Generate hash for deduplication
 */
class StorageStage implements PipelineStageInterface
{
    private bool $skipStorage = false;

    /**
     * Execute storage
     */
    public function execute(array $data): array
    {
        if ($this->skipStorage) {
            return array_merge($data, [
                'storage' => [
                    'skipped' => true,
                    'reason' => 'Storage disabled',
                ],
            ]);
        }

        $normalized = $data['normalized'] ?? [];
        $extractionMeta = $data['extraction_meta'] ?? [];
        $validation = $data['validation'] ?? [];
        $rawContent = $data['raw'] ?? '';
        $promptVersion = $data['prompt_version'] ?? 'unknown';
        $preprocessingMeta = $data['preprocessing_metadata'] ?? [];

        if (empty($normalized)) {
            throw new StageException(
                'StorageStage',
                'No normalized data to store',
                StageException::VALIDATION_ERROR
            );
        }

        try {
            return DB::transaction(function () use (
                $normalized, $extractionMeta, $validation, $rawContent,
                $promptVersion, $preprocessingMeta, $data
            ) {
                // Generate hashes for deduplication
                $postHash = $this->generatePostHash($normalized, $rawContent);
                $rawHash = hash('sha256', mb_strtolower(trim($rawContent)));

                // Check for duplicate
                $existingPost = Post::where('raw_content_hash', $rawHash)->first();

                if ($existingPost) {
                    $post = $existingPost;
                    $isNew = false;
                    $action = 'updated';
                } else {
                    $post = new Post();
                    $isNew = true;
                    $action = 'created';
                }

                // Fill post data
                $this->fillPost($post, $normalized, $rawContent, $postHash, $rawHash);

                if ($isNew) {
                    $post->save();
                } else {
                    $post->update();
                }

                // Create AI extraction record
                $extraction = $this->createExtractionRecord(
                    $post,
                    $data,
                    $normalized,
                    $extractionMeta,
                    $validation,
                    $promptVersion
                );

                // Sync features
                $featureIds = $this->syncFeatures($normalized['features'] ?? [], $post);

                return array_merge($data, [
                    'storage' => [
                        'post_id' => $post->id,
                        'extraction_id' => $extraction?->id,
                        'is_new' => $isNew,
                        'action' => $action,
                        'feature_count' => count($featureIds),
                        'duplicate_checked' => true,
                    ],
                    'post' => $post,
                    'extraction' => $extraction,
                ]);
            });

        } catch (\Exception $e) {
            Log::error('[StorageStage] Failed to store data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new StageException(
                'StorageStage',
                'Failed to store data: ' . $e->getMessage(),
                StageException::STORAGE_ERROR
            );
        }
    }

    /**
     * Generate post hash for deduplication
     */
    private function generatePostHash(array $normalized, string $rawContent): string
    {
        // Use raw content hash for primary deduplication
        if (!empty($rawContent)) {
            return hash('sha256', mb_strtolower(trim($rawContent)));
        }

        // Fallback to content-based hash
        $hashContent = implode('|', [
            $normalized['title'] ?? '',
            $normalized['price_text'] ?? '',
            $normalized['area_text'] ?? '',
            $normalized['address_text'] ?? '',
            implode(',', $normalized['phones'] ?? []),
        ]);

        return hash('sha256', mb_strtolower(trim($hashContent)));
    }

    /**
     * Fill post model with normalized data
     */
    private function fillPost(Post $post, array $normalized, string $rawContent, string $postHash, string $rawHash): void
    {
        $post->title = $normalized['title'] ?? null;
        $post->description = $normalized['description'] ?? null;
        $post->raw_content = $rawContent;
        $post->price_text = $normalized['price_text'] ?? null;
        $post->price_value = $normalized['price_value'] ?? null;
        $post->area_text = $normalized['area_text'] ?? null;
        $post->area_value = $normalized['area_value'] ?? null;
        $post->frontage_texts = $normalized['frontage_texts'] ?? [];
        $post->frontage_count = $normalized['frontage_count'] ?? 0;
        $post->depth_text = $normalized['depth_text'] ?? null;
        $post->address_text = $normalized['address_text'] ?? null;
        $post->ward = $normalized['ward'] ?? null;
        $post->district = $normalized['district'] ?? null;
        $post->city = $normalized['city'] ?? 'Thái Nguyên';
        $post->property_type = $normalized['property_type'] ?? null;
        $post->facebook_url = $normalized['facebook_url'] ?? null;
        $post->confidence = $normalized['confidence'] ?? 0.5;
        $post->hash = $postHash;
        $post->raw_content_hash = $rawHash;

        // Extract author info if available
        if (!empty($normalized['author_name'])) {
            $post->author_name = $normalized['author_name'];
        }

        // Set published date
        if (!empty($normalized['published_at'])) {
            $post->published_at = $normalized['published_at'];
        }
    }

    /**
     * Create AI extraction record
     */
    private function createExtractionRecord(
        Post $post,
        array $data,
        array $normalized,
        array $extractionMeta,
        array $validation,
        string $promptVersion
    ): ?AiExtraction {
        $extraction = new AiExtraction();
        $extraction->post_id = $post->id;
        $extraction->raw_response = $data['raw_extraction'] ?? [];
        $extraction->normalized_data = $normalized;
        $extraction->confidence = $normalized['confidence'] ?? 0.5;
        $extraction->processing_time_ms = $extractionMeta['processing_time_ms'] ?? 0;
        $extraction->ai_provider = $extractionMeta['provider'] ?? 'unknown';
        $extraction->ai_model = $extractionMeta['model'] ?? 'unknown';

        // Store validation info
        $extraction->validation_status = $validation['passed'] ? 'passed' : 'failed';
        $extraction->validation_errors = $validation['errors'] ?? [];

        // Store prompt version
        $extraction->prompt_version = $promptVersion;

        $extraction->save();

        return $extraction;
    }

    /**
     * Sync features to post
     */
    private function syncFeatures(array $features, Post $post): array
    {
        $featureIds = [];

        foreach ($features as $featureName) {
            $slug = $this->generateFeatureSlug($featureName);
            
            $feature = Feature::firstOrCreate(
                ['slug' => $slug],
                ['name' => $featureName]
            );
            
            $featureIds[] = $feature->id;
        }

        // Sync to pivot table
        if (!empty($featureIds)) {
            $post->features()->sync($featureIds);
        }

        return $featureIds;
    }

    /**
     * Generate slug from feature name
     */
    private function generateFeatureSlug(string $name): string
    {
        $slug = mb_strtolower(trim($name));
        $slug = preg_replace('/\s+/', '_', $slug);
        $slug = preg_replace('/[^\p{L}\p{N}_]/u', '', $slug);
        
        return $slug;
    }

    /**
     * Skip storage (for testing)
     */
    public function skipStorage(): self
    {
        $this->skipStorage = true;
        return $this;
    }

    /**
     * Check if this is a critical stage
     */
    public function isCritical(): bool
    {
        return false; // Non-critical - can continue without storing
    }

    /**
     * Get stage name
     */
    public function getName(): string
    {
        return 'store';
    }

    /**
     * Get stage description
     */
    public function getDescription(): string
    {
        return 'Storage - Post creation, AI extraction record, feature sync';
    }

    /**
     * Get required input data keys
     */
    public function getRequiredInputs(): array
    {
        return ['normalized'];
    }

    /**
     * Get output data keys
     */
    public function getOutputs(): array
    {
        return ['storage', 'post', 'extraction'];
    }

    /**
     * Validate input data
     */
    public function validateInput(array $data): void
    {
        if (!isset($data['normalized'])) {
            throw new StageException(
                'StorageStage',
                'Missing required input: normalized',
                StageException::VALIDATION_ERROR
            );
        }
    }
}
