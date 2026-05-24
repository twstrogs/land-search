<?php

namespace App\AI\Pipeline;

use App\AI\Pipeline\Stages\TextPreprocessingStage;
use App\AI\Pipeline\Stages\PromptEngineeringStage;
use App\AI\Pipeline\Stages\LLMExtractionStage;
use App\AI\Pipeline\Stages\ValidationStage;
use App\AI\Pipeline\Stages\NormalizationStage;
use App\AI\Pipeline\Stages\StorageStage;
use App\AI\Exceptions\PipelineException;
use App\AI\Exceptions\StageException;
use Illuminate\Support\Facades\Log;

/**
 * Main AI Processing Pipeline Orchestrator
 * 
 * Coordinates all stages of the AI extraction pipeline with:
 * - Error handling and rollback
 * - Performance monitoring
 * - Detailed logging
 * - Circuit breaker pattern
 */
class AIPipeline
{
    private array $stages = [];
    private array $stageOrder = [
        'preprocess',
        'prompt',
        'extract',
        'validate',
        'normalize',
        'store',
    ];
    
    private bool $circuitBreakerOpen = false;
    private int $failureCount = 0;
    private int $maxFailures = 5;
    private int $circuitResetTimeout = 60; // seconds
    
    public function __construct(
        private TextPreprocessingStage $preprocessor,
        private PromptEngineeringStage $promptEngine,
        private LLMExtractionStage $llmStage,
        private ValidationStage $validator,
        private NormalizationStage $normalizer,
        private StorageStage $storage,
        private ?PipelineLoggerInterface $logger = null
    ) {
        $this->stages = [
            'preprocess' => $this->preprocessor,
            'prompt' => $this->promptEngine,
            'extract' => $this->llmStage,
            'validate' => $this->validator,
            'normalize' => $this->normalizer,
            'store' => $this->storage,
        ];
    }

    /**
     * Process raw content through the full pipeline
     */
    public function process(string $rawContent, array $context = []): PipelineResult
    {
        $startTime = microtime(true);
        $pipelineId = $this->generatePipelineId();
        
        $data = [
            'raw' => $rawContent,
            'context' => $context,
            'pipeline_id' => $pipelineId,
        ];
        
        $logs = [];
        $errors = [];
        
        $this->log($pipelineId, 'info', 'Pipeline started', [
            'raw_length' => mb_strlen($rawContent),
            'context_keys' => array_keys($context),
        ]);
        
        // Check circuit breaker
        if ($this->isCircuitBreakerOpen()) {
            $this->log($pipelineId, 'warning', 'Circuit breaker is open, using fallback');
            return $this->processWithFallback($rawContent, $context, $pipelineId);
        }
        
        foreach ($this->stageOrder as $stageName) {
            $stage = $this->stages[$stageName];
            $stageStart = microtime(true);
            
            try {
                $this->log($pipelineId, 'debug', "Stage '{$stageName}' started");
                
                $data = $stage->execute($data);
                
                $stageTime = round((microtime(true) - $stageStart) * 1000, 2);
                
                $logs[$stageName] = [
                    'status' => 'success',
                    'time_ms' => $stageTime,
                    'data_keys' => array_keys($data),
                ];
                
                $this->log($pipelineId, 'debug', "Stage '{$stageName}' completed", [
                    'time_ms' => $stageTime,
                ]);
                
            } catch (StageException $e) {
                $stageTime = round((microtime(true) - $stageStart) * 1000, 2);
                
                $logs[$stageName] = [
                    'status' => 'failed',
                    'time_ms' => $stageTime,
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                ];
                
                $errors[] = [
                    'stage' => $stageName,
                    'error' => $e->getMessage(),
                    'is_critical' => $stage->isCritical(),
                ];
                
                $this->log($pipelineId, 'error', "Stage '{$stageName}' failed: " . $e->getMessage(), [
                    'error_type' => get_class($e),
                    'is_critical' => $stage->isCritical(),
                ]);
                
                // Handle critical stage failure
                if ($stage->isCritical()) {
                    $this->handleFailure();
                    break;
                }
                
                // Try to continue with degraded mode for non-critical failures
                $data = $this->handleNonCriticalFailure($stageName, $data, $e);
            } catch (\Exception $e) {
                $stageTime = round((microtime(true) - $stageStart) * 1000, 2);
                
                $logs[$stageName] = [
                    'status' => 'failed',
                    'time_ms' => $stageTime,
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                ];
                
                $errors[] = [
                    'stage' => $stageName,
                    'error' => $e->getMessage(),
                    'is_critical' => $stage->isCritical(),
                ];
                
                $this->handleFailure();
                break;
            }
        }
        
        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Check if pipeline completed successfully
        $hasErrors = !empty($errors);
        $hasCriticalErrors = collect($errors)->contains('is_critical', true);
        
        if (!$hasErrors) {
            $this->resetCircuitBreaker();
        }
        
        $result = new PipelineResult(
            success: !$hasCriticalErrors,
            data: $data['normalized'] ?? $data['raw_extraction'] ?? null,
            confidence: $data['confidence'] ?? null,
            logs: $logs,
            errors: $errors,
            pipelineId: $pipelineId,
            totalTimeMs: $totalTime,
            stagesCompleted: count(array_filter($logs, fn($l) => $l['status'] === 'success')),
            totalStages: count($this->stageOrder),
            degraded: $hasErrors && !$hasCriticalErrors
        );
        
        $this->log($pipelineId, 'info', 'Pipeline completed', [
            'success' => $result->success,
            'total_time_ms' => $totalTime,
            'stages_completed' => $result->stagesCompleted,
            'degraded' => $result->degraded,
        ]);
        
        return $result;
    }

    /**
     * Process multiple items in batch
     */
    public function processBatch(array $items, array $options = []): BatchPipelineResult
    {
        $startTime = microtime(true);
        $batchId = $this->generatePipelineId();
        
        $concurrency = $options['concurrency'] ?? 1;
        $results = [];
        $progress = 0;
        $total = count($items);
        
        $this->log($batchId, 'info', 'Batch processing started', [
            'total_items' => $total,
            'concurrency' => $concurrency,
        ]);
        
        // Process items with controlled concurrency
        $chunks = array_chunk($items, $concurrency, true);
        
        foreach ($chunks as $chunk) {
            foreach ($chunk as $index => $item) {
                $result = $this->process($item['content'], $item['context'] ?? []);
                $results[$index] = $result;
                
                $progress++;
                if ($options['progress_callback'] ?? false) {
                    ($options['progress_callback'])($progress, $total);
                }
            }
        }
        
        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Calculate batch statistics
        $successCount = count(array_filter($results, fn($r) => $r->success));
        $failureCount = count($results) - $successCount;
        $avgTime = array_sum(array_column($results, 'totalTimeMs')) / count($results);
        
        $batchResult = new BatchPipelineResult(
            results: $results,
            batchId: $batchId,
            totalItems: $total,
            successfulItems: $successCount,
            failedItems: $failureCount,
            totalTimeMs: $totalTime,
            averageTimeMs: round($avgTime, 2)
        );
        
        $this->log($batchId, 'info', 'Batch processing completed', [
            'total' => $total,
            'success' => $successCount,
            'failed' => $failureCount,
            'total_time_ms' => $totalTime,
        ]);
        
        return $batchResult;
    }

    /**
     * Process with fallback when circuit breaker is open
     */
    private function processWithFallback(string $rawContent, array $context, string $pipelineId): PipelineResult
    {
        $startTime = microtime(true);
        
        try {
            // Use simple regex-based extraction as fallback
            $fallbackData = [
                'raw' => $rawContent,
                'context' => $context,
                'pipeline_id' => $pipelineId,
            ];
            
            // Apply basic preprocessing
            $fallbackData = $this->stages['preprocess']->execute($fallbackData);
            
            // Use regex-based extraction
            $fallbackData['raw_extraction'] = $this->regexFallback($fallbackData['preprocessed']);
            $fallbackData['confidence'] = 0.3; // Low confidence for fallback
            
            return new PipelineResult(
                success: true,
                data: $fallbackData['raw_extraction'],
                confidence: 0.3,
                logs: ['fallback' => ['status' => 'used', 'time_ms' => round((microtime(true) - $startTime) * 1000, 2)]],
                errors: [],
                pipelineId: $pipelineId,
                totalTimeMs: round((microtime(true) - $startTime) * 1000, 2),
                stagesCompleted: 1,
                totalStages: count($this->stageOrder),
                degraded: true,
                isFallback: true
            );
        } catch (\Exception $e) {
            return new PipelineResult(
                success: false,
                data: null,
                confidence: 0,
                logs: [],
                errors: [['stage' => 'fallback', 'error' => $e->getMessage()]],
                pipelineId: $pipelineId,
                totalTimeMs: round((microtime(true) - $startTime) * 1000, 2),
                stagesCompleted: 0,
                totalStages: count($this->stageOrder),
                degraded: true,
                isFallback: true
            );
        }
    }

    /**
     * Simple regex-based fallback extraction
     */
    private function regexFallback(string $text): array
    {
        $extraction = [
            'title' => null,
            'description' => $text,
            'price_text' => null,
            'price_value' => null,
            'area_text' => null,
            'area_value' => null,
            'phones' => [],
            'property_type' => null,
            'confidence' => 0.3,
        ];
        
        // Extract phone numbers
        preg_match_all('/0\d{9,10}/', $text, $phoneMatches);
        $extraction['phones'] = array_unique($phoneMatches[0] ?? []);
        
        // Extract price (simple pattern)
        if (preg_match('/(\d+(?:\.\d+)?)\s*(tỷ|ty|tr|triệu)/i', $text, $priceMatch)) {
            $extraction['price_text'] = $priceMatch[0];
            $multiplier = stripos($priceMatch[2], 'tỷ') !== false ? 1_000_000_000 : 1_000_000;
            $extraction['price_value'] = (float) $priceMatch[1] * $multiplier;
        }
        
        // Extract area
        if (preg_match('/(\d+(?:\.\d+)?)\s*m2/i', $text, $areaMatch)) {
            $extraction['area_text'] = $areaMatch[0];
            $extraction['area_value'] = (float) $areaMatch[1];
        }
        
        return $extraction;
    }

    /**
     * Handle non-critical stage failure
     */
    private function handleNonCriticalFailure(string $stageName, array $data, \Exception $e): array
    {
        // Provide default values based on stage
        return match ($stageName) {
            'normalize' => array_merge($data, [
                'normalized' => $data['raw_extraction'] ?? [],
                'confidence' => ($data['confidence'] ?? 0.5) * 0.5, // Reduce confidence
            ]),
            'store' => $data, // Just skip storage, keep data
            default => $data,
        };
    }

    /**
     * Handle circuit breaker failure
     */
    private function handleFailure(): void
    {
        $this->failureCount++;
        
        if ($this->failureCount >= $this->maxFailures) {
            $this->circuitBreakerOpen = true;
            $this->circuitOpenSince = time();
            
            Log::warning('Circuit breaker opened due to repeated failures', [
                'failure_count' => $this->failureCount,
            ]);
        }
    }

    /**
     * Reset circuit breaker after timeout
     */
    private function resetCircuitBreaker(): void
    {
        $this->failureCount = 0;
        $this->circuitBreakerOpen = false;
        $this->circuitOpenSince = null;
    }

    /**
     * Check if circuit breaker should be checked and possibly reset
     */
    private function isCircuitBreakerOpen(): bool
    {
        if (!$this->circuitBreakerOpen) {
            return false;
        }
        
        // Check if timeout has passed
        if (isset($this->circuitOpenSince)) {
            $elapsed = time() - $this->circuitOpenSince;
            if ($elapsed >= $this->circuitResetTimeout) {
                // Allow one attempt to see if service is back
                $this->circuitBreakerOpen = false;
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get stage by name
     */
    public function getStage(string $name): ?object
    {
        return $this->stages[$name] ?? null;
    }

    /**
     * Get pipeline status
     */
    public function getStatus(): array
    {
        return [
            'circuit_breaker_open' => $this->circuitBreakerOpen,
            'failure_count' => $this->failureCount,
            'max_failures' => $this->maxFailures,
            'stages_count' => count($this->stages),
            'stages' => array_keys($this->stages),
        ];
    }

    /**
     * Generate unique pipeline ID
     */
    private function generatePipelineId(): string
    {
        return 'pl_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 12);
    }

    /**
     * Log pipeline events
     */
    private function log(string $pipelineId, string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($pipelineId, $level, $message, $context);
        }
        
        $logContext = array_merge(['pipeline_id' => $pipelineId], $context);
        
        match ($level) {
            'debug' => Log::debug("[AI Pipeline] {$message}", $logContext),
            'info' => Log::info("[AI Pipeline] {$message}", $logContext),
            'warning' => Log::warning("[AI Pipeline] {$message}", $logContext),
            'error' => Log::error("[AI Pipeline] {$message}", $logContext),
            default => Log::info("[AI Pipeline] {$message}", $logContext),
        };
    }
}
