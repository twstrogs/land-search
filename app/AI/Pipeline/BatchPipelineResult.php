<?php

namespace App\AI\Pipeline;

use JsonSerializable;

/**
 * Result from a batch pipeline execution
 */
class BatchPipelineResult implements JsonSerializable
{
    public function __construct(
        public readonly array $results,
        public readonly string $batchId,
        public readonly int $totalItems,
        public readonly int $successfulItems,
        public readonly int $failedItems,
        public readonly float $totalTimeMs,
        public readonly float $averageTimeMs
    ) {}

    /**
     * Get success rate percentage
     */
    public function getSuccessRate(): float
    {
        if ($this->totalItems === 0) {
            return 0;
        }
        return round(($this->successfulItems / $this->totalItems) * 100, 2);
    }

    /**
     * Get failure rate percentage
     */
    public function getFailureRate(): float
    {
        return 100 - $this->getSuccessRate();
    }

    /**
     * Get all results that succeeded
     */
    public function getSuccessfulResults(): array
    {
        return array_filter(
            $this->results,
            fn($result) => $result instanceof PipelineResult && $result->success
        );
    }

    /**
     * Get all results that failed
     */
    public function getFailedResults(): array
    {
        return array_filter(
            $this->results,
            fn($result) => $result instanceof PipelineResult && !$result->success
        );
    }

    /**
     * Get average confidence
     */
    public function getAverageConfidence(): ?float
    {
        $confidences = array_filter(
            array_map(
                fn($r) => $r instanceof PipelineResult ? $r->confidence : null,
                $this->results
            )
        );

        if (empty($confidences)) {
            return null;
        }

        return round(array_sum($confidences) / count($confidences), 4);
    }

    /**
     * Get high confidence results
     */
    public function getHighConfidenceResults(float $threshold = 0.8): array
    {
        return array_filter(
            $this->results,
            fn($result) => $result instanceof PipelineResult && $result->isHighConfidence($threshold)
        );
    }

    /**
     * Get timing statistics
     */
    public function getTimingStats(): array
    {
        $times = array_map(
            fn($r) => $r instanceof PipelineResult ? $r->totalTimeMs : 0,
            $this->results
        );

        sort($times);
        $count = count($times);

        return [
            'min_ms' => $count > 0 ? min($times) : 0,
            'max_ms' => $count > 0 ? max($times) : 0,
            'avg_ms' => $this->averageTimeMs,
            'median_ms' => $count > 0 ? $times[(int) floor($count / 2)] : 0,
            'p95_ms' => $count > 0 ? $times[(int) floor($count * 0.95)] : 0,
            'p99_ms' => $count > 0 ? $times[(int) floor($count * 0.99)] : 0,
        ];
    }

    /**
     * Get stage timing aggregates
     */
    public function getStageTimingAggregates(): array
    {
        $aggregates = [];
        $stageCounts = [];

        foreach ($this->results as $result) {
            if (!$result instanceof PipelineResult) {
                continue;
            }

            foreach ($result->getStageTimings() as $stage => $time) {
                if (!isset($aggregates[$stage])) {
                    $aggregates[$stage] = 0;
                    $stageCounts[$stage] = 0;
                }
                $aggregates[$stage] += $time;
                $stageCounts[$stage]++;
            }
        }

        $averages = [];
        foreach ($aggregates as $stage => $total) {
            $averages[$stage] = $stageCounts[$stage] > 0
                ? round($total / $stageCounts[$stage], 2)
                : 0;
        }

        return $averages;
    }

    /**
     * Get error summary
     */
    public function getErrorSummary(): array
    {
        $errorTypes = [];

        foreach ($this->results as $result) {
            if (!$result instanceof PipelineResult) {
                continue;
            }

            foreach ($result->errors as $error) {
                $stage = $error['stage'] ?? 'unknown';
                if (!isset($errorTypes[$stage])) {
                    $errorTypes[$stage] = 0;
                }
                $errorTypes[$stage]++;
            }
        }

        return $errorTypes;
    }

    /**
     * Serialize to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'batch_id' => $this->batchId,
            'total_items' => $this->totalItems,
            'successful_items' => $this->successfulItems,
            'failed_items' => $this->failedItems,
            'success_rate' => $this->getSuccessRate(),
            'failure_rate' => $this->getFailureRate(),
            'total_time_ms' => $this->totalTimeMs,
            'average_time_ms' => $this->averageTimeMs,
            'average_confidence' => $this->getAverageConfidence(),
            'high_confidence_count' => count($this->getHighConfidenceResults()),
            'timing_stats' => $this->getTimingStats(),
            'stage_timing_aggregates' => $this->getStageTimingAggregates(),
            'error_summary' => $this->getErrorSummary(),
        ];
    }
}
