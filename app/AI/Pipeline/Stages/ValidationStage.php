<?php

namespace App\AI\Pipeline\Stages;

use App\AI\Pipeline\PipelineStageInterface;
use App\AI\Pipeline\Validators\JsonSchemaValidator;
use App\AI\Pipeline\Validators\FieldRangeValidator;
use App\AI\Pipeline\Validators\CrossReferenceValidator;
use App\AI\Exceptions\StageException;

/**
 * Validation Stage
 * 
 * Validates LLM output with:
 * - JSON schema validation
 * - Field range validation
 * - Cross-reference validation
 * - Confidence scoring
 */
class ValidationStage implements PipelineStageInterface
{
    private array $validators = [];
    private float $defaultConfidence = 0.5;

    public function __construct(
        ?JsonSchemaValidator $schemaValidator = null,
        ?FieldRangeValidator $rangeValidator = null,
        ?CrossReferenceValidator $crossValidator = null
    ) {
        $this->validators = [
            'schema' => $schemaValidator ?? new JsonSchemaValidator(),
            'range' => $rangeValidator ?? new FieldRangeValidator(),
            'cross_ref' => $crossValidator ?? new CrossReferenceValidator(),
        ];
    }

    /**
     * Execute validation
     */
    public function execute(array $data): array
    {
        $extraction = $data['raw_extraction'] ?? [];

        if (empty($extraction)) {
            throw new StageException(
                'ValidationStage',
                'No extraction data to validate',
                StageException::VALIDATION_ERROR
            );
        }

        $allPassed = true;
        $errors = [];
        $warnings = [];
        $validationResults = [];

        // Run all validators
        foreach ($this->validators as $name => $validator) {
            $result = $validator->validate($extraction);
            $validationResults[$name] = $result;

            if (!$result['passed']) {
                $allPassed = false;
                $errors = array_merge($errors, $result['errors'] ?? []);
            }

            if (!empty($result['warnings'])) {
                $warnings = array_merge($warnings, $result['warnings'] ?? []);
            }
        }

        // Calculate confidence
        $confidence = $this->calculateConfidence($extraction, $validationResults);

        // Determine if should auto-approve
        $autoApprove = $confidence >= 0.8 && empty($errors);
        $needsReview = $confidence < 0.8 || !empty($warnings);

        return array_merge($data, [
            'validation' => [
                'passed' => $allPassed,
                'errors' => $errors,
                'warnings' => $warnings,
                'details' => $validationResults,
                'auto_approve' => $autoApprove,
                'needs_review' => $needsReview,
            ],
            'confidence' => $confidence,
            'extraction' => $extraction,
        ]);
    }

    /**
     * Calculate confidence score
     */
    private function calculateConfidence(array $extraction, array $validationResults): float
    {
        // Start with LLM-provided confidence if available
        $baseConfidence = (float) ($extraction['confidence'] ?? $this->defaultConfidence);

        // Completeness score
        $completenessScore = $this->calculateCompleteness($extraction);

        // Consistency score
        $consistencyScore = $this->calculateConsistency($extraction);

        // Validation score
        $validationScore = $this->calculateValidationScore($validationResults);

        // Combine scores with weights
        $finalConfidence = 
            ($baseConfidence * 0.4) +      // LLM confidence
            ($completenessScore * 0.25) +  // Completeness
            ($consistencyScore * 0.2) +     // Consistency
            ($validationScore * 0.15);     // Validation

        // Penalize for errors
        $errorCount = count($validationResults['schema']['errors'] ?? [])
            + count($validationResults['range']['errors'] ?? []);
        $finalConfidence -= min($errorCount * 0.05, 0.3);

        // Penalize for warnings
        $warningCount = count($validationResults['schema']['warnings'] ?? [])
            + count($validationResults['range']['warnings'] ?? [])
            + count($validationResults['cross_ref']['warnings'] ?? []);
        $finalConfidence -= min($warningCount * 0.02, 0.15);

        return max(0.0, min(1.0, round($finalConfidence, 4)));
    }

    /**
     * Calculate completeness score
     */
    private function calculateCompleteness(array $extraction): float
    {
        $requiredFields = [
            'title', 'property_type', 'price_text', 'price_value',
            'area_value', 'address_text', 'phones'
        ];

        $optionalFields = [
            'description', 'ward', 'district', 'city',
            'frontage_texts', 'frontage_count', 'direction',
            'features', 'legal_status'
        ];

        $requiredScore = 0;
        $optionalScore = 0;

        foreach ($requiredFields as $field) {
            if (!empty($extraction[$field])) {
                $requiredScore += 1;
            }
        }

        foreach ($optionalFields as $field) {
            if (!empty($extraction[$field])) {
                $optionalScore += 1;
            }
        }

        $requiredRatio = $requiredScore / count($requiredFields);
        $optionalRatio = count($optionalFields) > 0 
            ? $optionalScore / count($optionalFields) 
            : 1;

        // Required fields are weighted more heavily
        return ($requiredRatio * 0.7) + ($optionalRatio * 0.3);
    }

    /**
     * Calculate consistency score
     */
    private function calculateConsistency(array $extraction): float
    {
        $score = 1.0;

        // Check price-area consistency
        if (isset($extraction['price_value'], $extraction['area_value'])) {
            $pricePerM2 = $extraction['price_value'] / $extraction['area_value'];
            
            // Reasonable price per m2 for Thai Nguyen: 5-50 million VND
            if ($pricePerM2 < 1_000_000 || $pricePerM2 > 100_000_000) {
                $score -= 0.2;
            }
        }

        // Check phone format consistency
        if (!empty($extraction['phones'])) {
            $validPhones = 0;
            foreach ($extraction['phones'] as $phone) {
                if (preg_match('/^0\d{9,10}$/', (string) $phone)) {
                    $validPhones++;
                }
            }
            $phoneRatio = $validPhones / count($extraction['phones']);
            $score *= (0.5 + ($phoneRatio * 0.5));
        }

        // Check address consistency
        if (!empty($extraction['address_text'])) {
            $hasDistrict = !empty($extraction['district']);
            $hasCity = !empty($extraction['city']);
            if (!$hasDistrict || !$hasCity) {
                $score -= 0.1;
            }
        }

        return max(0.0, min(1.0, $score));
    }

    /**
     * Calculate validation score
     */
    private function calculateValidationScore(array $validationResults): float
    {
        $score = 1.0;

        foreach ($validationResults as $result) {
            if (!$result['passed']) {
                $errorCount = count($result['errors'] ?? []);
                $score -= min($errorCount * 0.1, 0.3);
            }
        }

        return max(0.0, min(1.0, $score));
    }

    /**
     * Check if this is a critical stage
     */
    public function isCritical(): bool
    {
        return false; // Non-critical - we can continue with warnings
    }

    /**
     * Get stage name
     */
    public function getName(): string
    {
        return 'validate';
    }

    /**
     * Get stage description
     */
    public function getDescription(): string
    {
        return 'Validation - JSON schema, field ranges, cross-reference validation, confidence scoring';
    }

    /**
     * Get required input data keys
     */
    public function getRequiredInputs(): array
    {
        return ['raw_extraction'];
    }

    /**
     * Get output data keys
     */
    public function getOutputs(): array
    {
        return ['validation', 'confidence', 'extraction'];
    }

    /**
     * Validate input data
     */
    public function validateInput(array $data): void
    {
        if (!isset($data['raw_extraction'])) {
            throw new StageException(
                'ValidationStage',
                'Missing required input: raw_extraction',
                StageException::VALIDATION_ERROR
            );
        }

        if (!is_array($data['raw_extraction'])) {
            throw new StageException(
                'ValidationStage',
                'raw_extraction must be an array',
                StageException::VALIDATION_ERROR
            );
        }
    }
}
