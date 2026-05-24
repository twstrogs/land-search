<?php

namespace App\AI\Pipeline\Stages;

use App\AI\Pipeline\PipelineStageInterface;
use App\AI\Exceptions\StageException;
use App\Services\NormalizeService;
use App\Services\FeatureExtractionService;

/**
 * Normalization Stage
 * 
 * Normalizes extracted data:
 * - Price normalization
 * - Area normalization
 * - Phone normalization
 * - Address standardization
 * - Feature extraction
 */
class NormalizationStage implements PipelineStageInterface
{
    private NormalizeService $normalizeService;
    private FeatureExtractionService $featureService;

    public function __construct(
        ?NormalizeService $normalizeService = null,
        ?FeatureExtractionService $featureService = null
    ) {
        $this->normalizeService = $normalizeService ?? new NormalizeService();
        $this->featureService = $featureService ?? new FeatureExtractionService();
    }

    /**
     * Execute normalization
     */
    public function execute(array $data): array
    {
        $extraction = $data['extraction'] ?? $data['raw_extraction'] ?? [];
        $rawText = $data['preprocessed'] ?? '';

        if (empty($extraction)) {
            throw new StageException(
                'NormalizationStage',
                'No extraction data to normalize',
                StageException::VALIDATION_ERROR
            );
        }

        $normalizationLog = [];

        // Normalize title
        $normalized['title'] = $this->normalizeService->normalizeTitle($extraction['title'] ?? null);
        $normalizationLog['title'] = $normalized['title'] !== ($extraction['title'] ?? null);

        // Normalize text fields
        $normalized['description'] = $this->normalizeService->normalizeText($extraction['description'] ?? null);
        $normalized['price_text'] = $this->normalizeService->normalizeText($extraction['price_text'] ?? null);
        $normalized['area_text'] = $this->normalizeService->normalizeText($extraction['area_text'] ?? null);

        // Normalize price value
        $normalized['price_value'] = $this->normalizeService->normalizePrice(
            $extraction['price_text'] ?? $extraction['price_value'] ?? null
        );
        
        // Calculate price per m2 if possible
        if ($normalized['price_value'] && !empty($extraction['area_value'])) {
            $normalized['price_per_m2'] = $normalized['price_value'] / $extraction['area_value'];
        }

        // Normalize area value
        $normalized['area_value'] = $this->normalizeService->normalizeArea($extraction['area_text'] ?? null);

        // Normalize frontage
        $normalized['frontage_texts'] = $this->normalizeService->normalizeFrontageTexts(
            $extraction['frontage_texts'] ?? []
        );
        $normalized['frontage_count'] = $extraction['frontage_count'] ?? 0;
        $normalized['depth_text'] = $extraction['depth_text'] ?? null;

        // Normalize phones
        $normalized['phones'] = $this->normalizeService->normalizePhones($extraction['phones'] ?? []);

        // Normalize address
        $normalized['address_text'] = $this->normalizeService->normalizeText($extraction['address_text'] ?? null);
        $normalized['ward'] = $this->normalizeService->normalizeText($extraction['ward'] ?? null);
        $normalized['district'] = $this->normalizeService->normalizeText($extraction['district'] ?? null);
        $normalized['city'] = $this->normalizeService->normalizeText($extraction['city'] ?? null) ?: 'Thái Nguyên';

        // Normalize property type
        $normalized['property_type'] = $this->normalizeService->normalizePropertyType(
            $extraction['property_type'] ?? null
        );

        // Normalize direction
        $normalized['direction'] = $this->normalizeDirection($extraction['direction'] ?? null);

        // Normalize legal status
        $normalized['legal_status'] = $this->normalizeLegalStatus($extraction['legal_status'] ?? null);

        // Extract and normalize features
        $normalized['features'] = $this->extractFeatures($extraction['features'] ?? [], $rawText);

        // Preserve other fields
        $normalized['facebook_url'] = $this->normalizeService->normalizeUrl($extraction['facebook_url'] ?? null);

        // Preserve confidence
        $normalized['confidence'] = $data['confidence'] ?? $extraction['confidence'] ?? 0.5;

        // Add extraction notes
        $normalized['extraction_notes'] = $this->generateExtractionNotes($normalized);

        return array_merge($data, [
            'normalized' => $normalized,
            'normalization_log' => $normalizationLog,
        ]);
    }

    /**
     * Normalize direction
     */
    private function normalizeDirection(?string $direction): ?string
    {
        if (!$direction) {
            return null;
        }

        $direction = trim(mb_strtolower($direction));

        $directionMap = [
            'đông' => 'dong',
            'tây' => 'tay',
            'nam' => 'nam',
            'bắc' => 'bac',
            'đông nam' => 'dong_nam',
            'đông bắc' => 'dong_bac',
            'tây nam' => 'tay_nam',
            'tây bắc' => 'tay_bac',
            'đông-nam' => 'dong_nam',
            'đông-bắc' => 'dong_bac',
            'tây-nam' => 'tay_nam',
            'tây-bắc' => 'tay_bac',
        ];

        return $directionMap[$direction] ?? $direction;
    }

    /**
     * Normalize legal status
     */
    private function normalizeLegalStatus(?string $status): ?string
    {
        if (!$status) {
            return null;
        }

        $status = trim(mb_strtolower($status));

        $statusMap = [
            'sổ đỏ' => 'Sổ đỏ',
            'sổ hồng' => 'Sổ hồng',
            'bìa đỏ' => 'Sổ đỏ',
            'sổ đỏ chính chủ' => 'Sổ đỏ',
            'sổ hồng chính chủ' => 'Sổ hồng',
            'full thổ cư' => 'Full thổ cư',
            'thổ cư đầy đủ' => 'Full thổ cư',
            'đã có sổ' => 'Đã có sổ',
            'chưa có sổ' => 'Chưa có sổ',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Extract and normalize features
     */
    private function extractFeatures(array $features, string $rawText): array
    {
        // Combine extracted features with raw text for normalization
        $allFeatures = array_merge($features, $this->featureService->normalizeFeatures($features, $rawText));
        
        // Filter and clean
        $cleaned = [];
        foreach ($allFeatures as $feature) {
            $feature = trim($feature);
            if (strlen($feature) > 2 && strlen($feature) < 100) {
                $cleaned[] = $feature;
            }
        }

        return array_values(array_unique($cleaned));
    }

    /**
     * Generate extraction notes
     */
    private function generateExtractionNotes(array $normalized): string
    {
        $notes = [];

        if (empty($normalized['price_value'])) {
            $notes[] = 'Giá không xác định được';
        }

        if (empty($normalized['area_value'])) {
            $notes[] = 'Diện tích không xác định được';
        }

        if (empty($normalized['phones'])) {
            $notes[] = 'Không tìm thấy số điện thoại';
        }

        if (empty($normalized['property_type'])) {
            $notes[] = 'Loại BĐS không xác định';
        }

        if ($normalized['confidence'] < 0.7) {
            $notes[] = 'Độ tin cậy thấp';
        }

        return implode('. ', $notes) ?: 'Trích xuất hoàn chỉnh';
    }

    /**
     * Check if this is a critical stage
     */
    public function isCritical(): bool
    {
        return false; // Non-critical - can continue with partial data
    }

    /**
     * Get stage name
     */
    public function getName(): string
    {
        return 'normalize';
    }

    /**
     * Get stage description
     */
    public function getDescription(): string
    {
        return 'Normalization - Price, area, phone, address normalization, feature extraction';
    }

    /**
     * Get required input data keys
     */
    public function getRequiredInputs(): array
    {
        return ['extraction', 'confidence'];
    }

    /**
     * Get output data keys
     */
    public function getOutputs(): array
    {
        return ['normalized', 'normalization_log'];
    }

    /**
     * Validate input data
     */
    public function validateInput(array $data): void
    {
        if (!isset($data['extraction']) && !isset($data['raw_extraction'])) {
            throw new StageException(
                'NormalizationStage',
                'Missing required input: extraction or raw_extraction',
                StageException::VALIDATION_ERROR
            );
        }
    }
}
