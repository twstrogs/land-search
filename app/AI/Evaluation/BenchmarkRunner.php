<?php

namespace App\AI\Evaluation;

use App\Models\EvaluationResult;
use App\Models\EvaluationDataset;
use Illuminate\Support\Facades\DB;

/**
 * Benchmark Runner
 * 
 * Runs comprehensive benchmarks comparing different extraction methods
 */
class BenchmarkRunner
{
    private Evaluator $evaluator;
    private array $results = [];

    public function __construct(?Evaluator $evaluator = null)
    {
        $this->evaluator = $evaluator ?? new Evaluator();
    }

    /**
     * Run full benchmark comparison
     */
    public function runFullComparison(?EvaluationDataset $dataset = null): ComparisonReport
    {
        // Load or create dataset
        $dataset = $dataset ?? $this->getOrCreateDataset();

        // Run comparison
        return $this->evaluator->compare($dataset, [
            'regex',
            'nlp',
            'llm_gemini',
            'llm_ollama',
            'hybrid',
        ]);
    }

    /**
     * Run benchmark for specific methods
     */
    public function runMethods(
        array $methods,
        ?EvaluationDataset $dataset = null
    ): ComparisonReport {
        $dataset = $dataset ?? $this->getOrCreateDataset();
        return $this->evaluator->compare($dataset, $methods);
    }

    /**
     * Run benchmark for specific LLM provider
     */
    public function runProviderBenchmark(
        string $provider,
        ?EvaluationDataset $dataset = null
    ): EvaluationResultModel {
        $dataset = $dataset ?? $this->getOrCreateDataset();
        return $this->evaluator->evaluate("llm_{$provider}", $dataset);
    }

    /**
     * Compare prompt versions
     */
    public function comparePromptVersions(
        array $versionIds,
        ?EvaluationDataset $dataset = null
    ): array {
        $dataset = $dataset ?? $this->getOrCreateDataset();
        $results = [];

        foreach ($versionIds as $versionId) {
            // This would load the specific prompt version and evaluate
            $result = $this->evaluator->evaluate("prompt_{$versionId}", $dataset);
            $results[$versionId] = $result;
        }

        return $results;
    }

    /**
     * Get or create benchmark dataset
     */
    private function getOrCreateDataset(): EvaluationDataset
    {
        try {
            $dataset = EvaluationDataset::where('name', 'thai_nguyen_benchmark_v1')
                ->first();

            if (!$dataset) {
                $dataset = $this->createBenchmarkDataset();
            }

            return $dataset;
        } catch (\Exception $e) {
            // If database not available, create in-memory dataset
            return $this->createInMemoryDataset();
        }
    }

    /**
     * Create benchmark dataset
     */
    private function createBenchmarkDataset(): EvaluationDataset
    {
        $testSamples = $this->getTestSamples();
        $groundTruth = $this->generateGroundTruth($testSamples);

        return EvaluationDataset::create([
            'name' => 'thai_nguyen_benchmark_v1',
            'description' => 'Benchmark dataset for Thai Nguyen real estate extraction',
            'test_samples' => $testSamples,
            'ground_truth' => $groundTruth,
            'total_samples' => count($testSamples),
        ]);
    }

    /**
     * Create in-memory dataset (when DB not available)
     */
    private function createInMemoryDataset(): EvaluationDataset
    {
        $testSamples = $this->getTestSamples();
        $groundTruth = $this->generateGroundTruth($testSamples);

        return new EvaluationDataset([
            'name' => 'thai_nguyen_benchmark_v1',
            'description' => 'Benchmark dataset for Thai Nguyen real estate extraction',
            'test_samples' => $testSamples,
            'ground_truth' => $groundTruth,
            'total_samples' => count($testSamples),
        ]);
    }

    /**
     * Get test samples for benchmarking
     */
    private function getTestSamples(): array
    {
        return [
            [
                'id' => 1,
                'input' => 'Bán nhà 3 tầng mặt đường Ngô Gia Tự, phường Tân Thịnh, TP Thái Nguyên. Diện tích 120m2, 4 phòng ngủ. Giá 2.5 tỷ. LH: 0981234567',
            ],
            [
                'id' => 2,
                'input' => 'Đất nền p. Cam Giá, TP Thái Nguyên. DT 200m2, giá 900tr. LH Ms Hà 0979876543',
            ],
            [
                'id' => 3,
                'input' => 'Căn hộ chung cư 2PN, 2WC, 75m2 tại Quang Trung. Giá 1.8 tỷ. Sổ đỏ chính chủ.',
            ],
            [
                'id' => 4,
                'input' => 'Bán nhà riêng 2 tầng, đường Cái Khế, p. Trưng Vương. 80m2, 3pn, 2wc. 1.2 tỷ.',
            ],
            [
                'id' => 5,
                'input' => 'Đất thổ cư 150m2, đường Lương Ngọc Quyến, gần chợ. Giá 750 triệu.',
            ],
        ];
    }

    /**
     * Generate ground truth for test samples
     */
    private function generateGroundTruth(array $testSamples): array
    {
        $groundTruth = [];

        foreach ($testSamples as $sample) {
            $groundTruth[$sample['id']] = [
                'title' => null, // Would be manually labeled
                'property_type' => null,
                'price_value' => null,
                'area_value' => null,
                'phones' => [],
                'direction' => null,
            ];
        }

        return $groundTruth;
    }

    /**
     * Generate markdown report
     */
    public function generateReport(ComparisonReport $report): string
    {
        $md = "# Benchmark Report\n\n";
        $md .= "## Overall Results\n\n";
        $md .= $report->getTable() . "\n\n";

        $md .= "## Per-Field Analysis\n\n";

        foreach ($report->results as $method => $result) {
            $md .= "### {$method}\n\n";
            $md .= "| Field | Precision | Recall | F1-Score |\n";
            $md .= "|-------|-----------|--------|----------|\n";

            foreach ($result->fieldMetrics as $field => $metrics) {
                $md .= sprintf(
                    "| %s | %.2f%% | %.2f%% | %.2f%% |\n",
                    $field,
                    $metrics['precision'] * 100,
                    $metrics['recall'] * 100,
                    $metrics['f1_score'] * 100
                );
            }

            $md .= "\n";
        }

        $md .= "## Conclusions\n\n";
        $md .= "- **Best Method**: " . ($report->getBestMethod() ?? 'N/A') . "\n";
        $md .= "- **Recommendation**: " . $this->getRecommendation($report) . "\n";

        return $md;
    }

    /**
     * Get recommendation based on results
     */
    private function getRecommendation(ComparisonReport $report): string
    {
        $bestMethod = $report->getBestMethod();
        $hybridResult = $report->results['hybrid'] ?? null;
        $llmResult = $report->results['llm_gemini'] ?? null;

        if (!$hybridResult || !$llmResult) {
            return "Run full comparison to get recommendation.";
        }

        // If hybrid is significantly better than LLM alone
        $improvement = ($hybridResult->metrics['f1_score'] - $llmResult->metrics['f1_score']) * 100;

        if ($improvement > 5) {
            return "Use Hybrid approach (LLM + Regex) for best results. {$improvement}% improvement over LLM alone.";
        }

        return "Use {$bestMethod} for optimal accuracy-speed tradeoff.";
    }

    /**
     * Get historical results
     */
    public function getHistoricalResults(string $method, int $limit = 10): array
    {
        try {
            return EvaluationResult::where('method', $method)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Track improvement over time
     */
    public function trackImprovement(string $method): array
    {
        $results = $this->getHistoricalResults($method, 20);

        if (count($results) < 2) {
            return [
                'trend' => 'insufficient_data',
                'message' => 'Need at least 2 evaluations to track improvement',
            ];
        }

        $first = $results[count($results) - 1];
        $latest = $results[0];

        $f1Improvement = ($latest['f1_score'] - $first['f1_score']) * 100;
        $accuracyImprovement = ($latest['accuracy'] - $first['accuracy']) * 100;

        return [
            'trend' => $f1Improvement > 0 ? 'improving' : ($f1Improvement < 0 ? 'declining' : 'stable'),
            'f1_improvement' => round($f1Improvement, 2),
            'accuracy_improvement' => round($accuracyImprovement, 2),
            'first_evaluation' => $first['created_at'],
            'latest_evaluation' => $latest['created_at'],
        ];
    }
}
