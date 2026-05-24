<?php

namespace App\AI\Evaluation;

use App\Models\EvaluationResult;
use App\Models\EvaluationDataset;
use App\Models\AiExtraction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Evaluator
 * 
 * Main evaluation class for comparing extraction methods:
 * - Regex vs NLP vs LLM
 * - Cross-provider comparison
 * - Per-field metrics
 */
class Evaluator
{
    private array $fieldWeights = [
        'title' => 0.1,
        'property_type' => 0.15,
        'price_text' => 0.15,
        'price_value' => 0.15,
        'area_value' => 0.1,
        'address_text' => 0.1,
        'ward' => 0.05,
        'district' => 0.05,
        'phones' => 0.1,
        'direction' => 0.05,
    ];

    /**
     * Evaluate a method on a dataset
     */
    public function evaluate(
        string $method,
        EvaluationDataset $dataset,
        ?array $config = null
    ): EvaluationResultModel {
        $predictions = [];
        $groundTruth = $dataset->getGroundTruth();
        $testSamples = $dataset->getTestSamples();

        $totalTime = 0;
        $errors = [];

        foreach ($testSamples as $sample) {
            $startTime = microtime(true);
            
            try {
                $prediction = $this->predict($method, $sample['input'], $config);
                $prediction['id'] = $sample['id'];
                $predictions[$sample['id']] = $prediction;
            } catch (\Exception $e) {
                $errors[] = [
                    'sample_id' => $sample['id'],
                    'error' => $e->getMessage(),
                ];
                $predictions[$sample['id']] = ['error' => $e->getMessage()];
            }

            $totalTime += (microtime(true) - $startTime) * 1000;
        }

        // Calculate metrics
        $metrics = $this->calculateMetrics($predictions, $groundTruth, $method);

        // Calculate per-field metrics
        $fieldMetrics = $this->calculateFieldMetrics($predictions, $groundTruth);

        // Save result
        $result = $this->saveResult($method, $dataset, $metrics, $fieldMetrics, $totalTime, $errors);

        return new EvaluationResultModel(
            method: $method,
            metrics: $metrics,
            fieldMetrics: $fieldMetrics,
            totalSamples: count($testSamples),
            errorCount: count($errors),
            processingTimeMs: $totalTime,
            resultId: $result?->id
        );
    }

    /**
     * Predict using specified method
     */
    private function predict(string $method, string $input, ?array $config = null): array
    {
        return match ($method) {
            'regex' => $this->predictWithRegex($input),
            'nlp' => $this->predictWithNLP($input),
            'llm_gemini' => $this->predictWithLLM($input, 'gemini', $config),
            'llm_ollama' => $this->predictWithLLM($input, 'ollama', $config),
            'llm_openai' => $this->predictWithLLM($input, 'openai', $config),
            'hybrid' => $this->predictWithHybrid($input, $config),
            default => throw new \Exception("Unknown method: {$method}"),
        };
    }

    /**
     * Regex-based prediction
     */
    private function predictWithRegex(string $input): array
    {
        $prediction = [
            'title' => null,
            'property_type' => null,
            'price_text' => null,
            'price_value' => null,
            'area_value' => null,
            'address_text' => null,
            'ward' => null,
            'district' => null,
            'phones' => [],
            'direction' => null,
        ];

        // Extract price
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(tỷ|ty|tỉ)/i', $input, $m)) {
            $prediction['price_text'] = $m[0];
            $prediction['price_value'] = (float) str_replace(',', '.', $m[1]) * 1_000_000_000;
        } elseif (preg_match('/(\d+(?:[.,]\d+)?)\s*(tr|triệu|củ)/i', $input, $m)) {
            $prediction['price_text'] = $m[0];
            $prediction['price_value'] = (float) str_replace(',', '.', $m[1]) * 1_000_000;
        }

        // Extract area
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*m2/i', $input, $m)) {
            $prediction['area_value'] = (float) str_replace(',', '.', $m[1]);
        }

        // Extract phones
        preg_match_all('/0\d{9,10}/', $input, $phones);
        $prediction['phones'] = array_unique($phones[0] ?? []);

        // Extract property type
        $inputLower = mb_strtolower($input);
        if (strpos($inputLower, 'căn hộ') !== false || strpos($inputLower, 'chung cư') !== false) {
            $prediction['property_type'] = 'Căn hộ chung cư';
        } elseif (strpos($inputLower, 'nhà') !== false) {
            $prediction['property_type'] = 'Nhà phố/Nhà riêng';
        } elseif (strpos($inputLower, 'đất nền') !== false || strpos($inputLower, 'thổ cư') !== false) {
            $prediction['property_type'] = 'Đất nền/Đất thổ cư';
        }

        // Extract direction
        $directions = ['đông', 'tây', 'nam', 'bắc', 'đông nam', 'đông bắc', 'tây nam', 'tây bắc'];
        foreach ($directions as $dir) {
            if (strpos(mb_strtolower($input), $dir) !== false) {
                $prediction['direction'] = str_replace(' ', '_', $dir);
                break;
            }
        }

        return $prediction;
    }

    /**
     * NLP-based prediction
     */
    private function predictWithNLP(string $input): array
    {
        // Use existing NLP services
        $prediction = $this->predictWithRegex($input); // Start with regex base

        // Enhance with NLP processing
        // This would use spaCy or similar for Vietnamese NLP
        // For now, just enhance what regex found

        return $prediction;
    }

    /**
     * LLM-based prediction
     */
    private function predictWithLLM(string $input, string $provider, ?array $config = null): array
    {
        $factory = app(\App\AI\Factories\AiServiceFactory::class);
        $llm = $factory->make($provider);

        $prompt = "Trích xuất thông tin BĐS từ: {$input}. Trả về JSON.";

        $result = $llm->extract($prompt);

        if (isset($result['error'])) {
            throw new \Exception($result['error']);
        }

        return $result;
    }

    /**
     * Hybrid prediction (LLM + Regex)
     */
    private function predictWithHybrid(string $input, ?array $config = null): array
    {
        // Get LLM prediction
        $llmPrediction = $this->predictWithLLM($input, 'gemini', $config);

        // Get regex prediction
        $regexPrediction = $this->predictWithRegex($input);

        // Merge: Use LLM but fallback to regex for missing fields
        foreach ($regexPrediction as $field => $value) {
            if (empty($llmPrediction[$field]) && !empty($value)) {
                $llmPrediction[$field] = $value;
            }
        }

        return $llmPrediction;
    }

    /**
     * Calculate overall metrics
     */
    private function calculateMetrics(array $predictions, array $groundTruth, string $method): array
    {
        $metrics = [
            'accuracy' => 0,
            'precision' => 0,
            'recall' => 0,
            'f1_score' => 0,
        ];

        $fieldScores = [];
        $totalWeight = 0;

        foreach ($this->fieldWeights as $field => $weight) {
            $fieldMetrics = $this->calculateFieldAccuracy($predictions, $groundTruth, $field);
            $fieldScores[$field] = $fieldMetrics;
            $totalWeight += $weight;
        }

        // Weighted average of field accuracies
        $weightedAccuracy = 0;
        foreach ($this->fieldWeights as $field => $weight) {
            $normalizedWeight = $weight / $totalWeight;
            $weightedAccuracy += $fieldScores[$field]['accuracy'] * $normalizedWeight;
        }

        $metrics['accuracy'] = $weightedAccuracy;

        // Calculate overall precision and recall
        $totalTp = 0;
        $totalFp = 0;
        $totalFn = 0;

        foreach ($fieldScores as $scores) {
            $totalTp += $scores['true_positives'];
            $totalFp += $scores['false_positives'];
            $totalFn += $scores['false_negatives'];
        }

        $metrics['precision'] = $totalTp / ($totalTp + $totalFp) ?: 0;
        $metrics['recall'] = $totalTp / ($totalTp + $totalFn) ?: 0;
        $metrics['f1_score'] = 2 * ($metrics['precision'] * $metrics['recall']) / ($metrics['precision'] + $metrics['recall']) ?: 0;

        return $metrics;
    }

    /**
     * Calculate per-field metrics
     */
    private function calculateFieldMetrics(array $predictions, array $groundTruth): array
    {
        $fieldMetrics = [];

        foreach ($this->fieldWeights as $field => $weight) {
            $fieldMetrics[$field] = $this->calculateFieldAccuracy($predictions, $groundTruth, $field);
        }

        return $fieldMetrics;
    }

    /**
     * Calculate accuracy for a specific field
     */
    private function calculateFieldAccuracy(array $predictions, array $groundTruth, string $field): array
    {
        $tp = 0;
        $fp = 0;
        $fn = 0;

        foreach ($groundTruth as $id => $truth) {
            $pred = $predictions[$id] ?? [];
            if (isset($pred['error'])) {
                $fn++;
                continue;
            }

            $predValue = $pred[$field] ?? null;
            $truthValue = $truth[$field] ?? null;

            if ($this->valuesMatch($predValue, $truthValue, $field)) {
                $tp++;
            } else {
                if ($predValue !== null) {
                    $fp++;
                }
                if ($truthValue !== null) {
                    $fn++;
                }
            }
        }

        $precision = $tp + $fp > 0 ? $tp / ($tp + $fp) : 0;
        $recall = $tp + $fn > 0 ? $tp / ($tp + $fn) : 0;
        $f1 = $precision + $recall > 0 ? 2 * ($precision * $recall) / ($precision + $recall) : 0;

        return [
            'true_positives' => $tp,
            'false_positives' => $fp,
            'false_negatives' => $fn,
            'precision' => round($precision, 4),
            'recall' => round($recall, 4),
            'f1_score' => round($f1, 4),
            'accuracy' => $tp + $fn > 0 ? round($tp / ($tp + $fn + $fp), 4) : 0,
        ];
    }

    /**
     * Check if two values match
     */
    private function valuesMatch($pred, $truth, string $field): bool
    {
        if ($pred === $truth) {
            return true;
        }

        if ($pred === null || $truth === null) {
            return false;
        }

        // For numbers, allow tolerance
        if (is_numeric($pred) && is_numeric($truth)) {
            $tolerance = max(abs((float) $truth) * 0.05, 0.01);
            return abs((float) $pred - (float) $truth) <= $tolerance;
        }

        // For strings, case-insensitive comparison
        if (is_string($pred) && is_string($truth)) {
            return trim(mb_strtolower($pred)) === trim(mb_strtolower($truth));
        }

        // For arrays (phones), check if there's overlap
        if (is_array($pred) && is_array($truth)) {
            return !empty(array_intersect($pred, $truth));
        }

        return false;
    }

    /**
     * Save evaluation result to database
     */
    private function saveResult(
        string $method,
        EvaluationDataset $dataset,
        array $metrics,
        array $fieldMetrics,
        float $processingTime,
        array $errors
    ): ?EvaluationResult {
        try {
            return EvaluationResult::create([
                'test_dataset_name' => $dataset->name,
                'method' => $method,
                'provider' => str_starts_with($method, 'llm_') ? substr($method, 4) : null,
                'model' => null,
                'accuracy' => $metrics['accuracy'],
                'precision_score' => $metrics['precision'],
                'recall_score' => $metrics['recall'],
                'f1_score' => $metrics['f1_score'],
                'field_metrics' => $fieldMetrics,
                'total_samples' => $dataset->getTotalSamples(),
                'processing_time_ms_avg' => $processingTime / $dataset->getTotalSamples(),
                'error_count' => count($errors),
            ]);
        } catch (\Exception $e) {
            Log::warning("[Evaluator] Failed to save result: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Compare multiple methods
     */
    public function compare(
        EvaluationDataset $dataset,
        array $methods = ['regex', 'nlp', 'llm_gemini', 'hybrid']
    ): ComparisonReport {
        $results = [];

        foreach ($methods as $method) {
            $results[$method] = $this->evaluate($method, $dataset);
        }

        return new ComparisonReport($results);
    }
}

/**
 * Evaluation Result Model (Value Object)
 */
class EvaluationResultModel
{
    public function __construct(
        public readonly string $method,
        public readonly array $metrics,
        public readonly array $fieldMetrics,
        public readonly int $totalSamples,
        public readonly int $errorCount,
        public readonly float $processingTimeMs,
        public readonly ?int $resultId = null
    ) {}

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'metrics' => $this->metrics,
            'field_metrics' => $this->fieldMetrics,
            'total_samples' => $this->totalSamples,
            'error_count' => $this->errorCount,
            'processing_time_ms' => round($this->processingTimeMs, 2),
            'result_id' => $this->resultId,
        ];
    }
}

/**
 * Comparison Report
 */
class ComparisonReport
{
    public function __construct(
        public readonly array $results // method => EvaluationResultModel
    ) {}

    public function getBestMethod(string $metric = 'f1_score'): ?string
    {
        $best = null;
        $bestScore = -1;

        foreach ($this->results as $method => $result) {
            $score = $result->metrics[$metric] ?? -1;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $method;
            }
        }

        return $best;
    }

    public function getTable(): string
    {
        $table = "| Method | Accuracy | Precision | Recall | F1-Score | Time (ms) |\n";
        $table .= "|--------|----------|-----------|--------|----------|----------|\n";

        foreach ($this->results as $method => $result) {
            $table .= sprintf(
                "| %s | %.2f%% | %.2f%% | %.2f%% | %.2f%% | %d |\n",
                $method,
                $result->metrics['accuracy'] * 100,
                $result->metrics['precision'] * 100,
                $result->metrics['recall'] * 100,
                $result->metrics['f1_score'] * 100,
                $result->processingTimeMs / $result->totalSamples
            );
        }

        return $table;
    }
}
