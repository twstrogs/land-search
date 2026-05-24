<?php

namespace App\AI\Pipeline;

use JsonSerializable;

/**
 * Result from a single pipeline execution
 */
class PipelineResult implements JsonSerializable
{
    public function __construct(
        public readonly bool $success,
        public readonly ?array $data,
        public readonly ?float $confidence,
        public readonly array $logs,
        public readonly array $errors,
        public readonly string $pipelineId,
        public readonly float $totalTimeMs,
        public readonly int $stagesCompleted,
        public readonly int $totalStages,
        public readonly bool $degraded = false,
        public readonly bool $isFallback = false
    ) {}

    /**
     * Check if pipeline completed all stages
     */
    public function isComplete(): bool
    {
        return $this->stagesCompleted === $this->totalStages;
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage(): float
    {
        if ($this->totalStages === 0) {
            return 0;
        }
        return round(($this->stagesCompleted / $this->totalStages) * 100, 2);
    }

    /**
     * Get failed stages
     */
    public function getFailedStages(): array
    {
        return array_keys(array_filter(
            $this->logs,
            fn($log) => ($log['status'] ?? '') === 'failed'
        ));
    }

    /**
     * Get stage timing breakdown
     */
    public function getStageTimings(): array
    {
        $timings = [];
        foreach ($this->logs as $stage => $log) {
            $timings[$stage] = $log['time_ms'] ?? 0;
        }
        return $timings;
    }

    /**
     * Get slowest stage
     */
    public function getSlowestStage(): ?array
    {
        $maxTime = 0;
        $slowestStage = null;
        
        foreach ($this->logs as $stage => $log) {
            $time = $log['time_ms'] ?? 0;
            if ($time > $maxTime) {
                $maxTime = $time;
                $slowestStage = $stage;
            }
        }
        
        return $slowestStage ? [
            'stage' => $slowestStage,
            'time_ms' => $maxTime,
        ] : null;
    }

    /**
     * Check if extraction is high confidence
     */
    public function isHighConfidence(float $threshold = 0.8): bool
    {
        return $this->confidence !== null && $this->confidence >= $threshold;
    }

    /**
     * Get validation status if validation stage was reached
     */
    public function getValidationStatus(): ?array
    {
        return $this->logs['validate']['status'] ?? null;
    }

    /**
     * Check if validation passed
     */
    public function validationPassed(): bool
    {
        $validation = $this->logs['validate'] ?? [];
        return ($validation['status'] ?? '') === 'success';
    }

    /**
     * Serialize to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'confidence' => $this->confidence,
            'logs' => $this->logs,
            'errors' => $this->errors,
            'pipeline_id' => $this->pipelineId,
            'total_time_ms' => $this->totalTimeMs,
            'stages_completed' => $this->stagesCompleted,
            'total_stages' => $this->totalStages,
            'completion_percentage' => $this->getCompletionPercentage(),
            'degraded' => $this->degraded,
            'is_fallback' => $this->isFallback,
            'failed_stages' => $this->getFailedStages(),
            'slowest_stage' => $this->getSlowestStage(),
            'stage_timings' => $this->getStageTimings(),
        ];
    }

    /**
     * Create from array (for testing)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'] ?? false,
            data: $data['data'] ?? null,
            confidence: $data['confidence'] ?? null,
            logs: $data['logs'] ?? [],
            errors: $data['errors'] ?? [],
            pipelineId: $data['pipeline_id'] ?? '',
            totalTimeMs: $data['total_time_ms'] ?? 0,
            stagesCompleted: $data['stages_completed'] ?? 0,
            totalStages: $data['total_stages'] ?? 0,
            degraded: $data['degraded'] ?? false,
            isFallback: $data['is_fallback'] ?? false
        );
    }
}
