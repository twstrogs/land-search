<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\AI\Evaluation\Evaluator;
use App\AI\Evaluation\BenchmarkRunner;
use App\Models\EvaluationDataset;
use App\Models\EvaluationResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    /**
     * Run full benchmark
     */
    public function runBenchmark(Request $request): JsonResponse
    {
        $request->validate([
            'methods' => 'array',
            'methods.*' => 'in:regex,nlp,llm_gemini,llm_ollama,llm_openai,hybrid',
            'dataset' => 'string|nullable',
        ]);

        $methods = $request->input('methods', [
            'regex',
            'nlp',
            'llm_gemini',
            'hybrid'
        ]);

        $datasetName = $request->input('dataset');

        try {
            $dataset = $datasetName 
                ? EvaluationDataset::where('name', $datasetName)->first()
                : null;

            $runner = new BenchmarkRunner();
            $report = $runner->runMethods($methods, $dataset);

            return response()->json([
                'success' => true,
                'data' => $report->toArray(),
                'summary' => [
                    'best_method' => $report->getBestMethod(),
                    'methods_compared' => count($methods),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run single method evaluation
     */
    public function runEvaluation(Request $request): JsonResponse
    {
        $request->validate([
            'method' => 'required|in:regex,nlp,llm_gemini,llm_ollama,llm_openai,hybrid',
            'dataset' => 'string|nullable',
        ]);

        $method = $request->input('method');
        $datasetName = $request->input('dataset');

        try {
            $dataset = $datasetName 
                ? EvaluationDataset::where('name', $datasetName)->first()
                : null;

            $evaluator = new Evaluator();
            $result = $evaluator->evaluate($method, $dataset ?? new \App\AI\Evaluation\BenchmarkRunner());

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get evaluation history
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'method' => 'string|nullable',
            'dataset' => 'string|nullable',
            'limit' => 'integer|min:1|max:100',
        ]);

        $query = EvaluationResult::query();

        if ($request->has('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->has('dataset')) {
            $query->where('test_dataset_name', $request->input('dataset'));
        }

        $results = $query
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 20))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Get comparison table
     */
    public function comparison(Request $request): JsonResponse
    {
        $request->validate([
            'dataset' => 'required|string',
        ]);

        $datasetName = $request->input('dataset');

        try {
            $results = EvaluationResult::compareMethods($datasetName);

            // Format as comparison table
            $table = [];
            foreach ($results as $method => $result) {
                $table[] = [
                    'method' => $method,
                    'accuracy' => $result->accuracy,
                    'precision' => $result->precision_score,
                    'recall' => $result->recall_score,
                    'f1_score' => $result->f1_score,
                    'avg_time_ms' => $result->processing_time_ms_avg,
                    'created_at' => $result->created_at,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'dataset' => $datasetName,
                    'methods' => $table,
                    'best_by_accuracy' => collect($table)->sortByDesc('accuracy')->first()['method'] ?? null,
                    'best_by_f1' => collect($table)->sortByDesc('f1_score')->first()['method'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available datasets
     */
    public function datasets(): JsonResponse
    {
        $datasets = EvaluationDataset::all(['id', 'name', 'description', 'total_samples', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $datasets,
        ]);
    }

    /**
     * Create new dataset
     */
    public function createDataset(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:evaluation_datasets,name',
            'description' => 'string|nullable',
            'test_samples' => 'required|array',
            'test_samples.*.id' => 'required|integer',
            'test_samples.*.input' => 'required|string',
            'ground_truth' => 'required|array',
        ]);

        try {
            $dataset = EvaluationDataset::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'test_samples' => $request->input('test_samples'),
                'ground_truth' => $request->input('ground_truth'),
                'total_samples' => count($request->input('test_samples')),
            ]);

            return response()->json([
                'success' => true,
                'data' => $dataset,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get field metrics breakdown
     */
    public function fieldMetrics(Request $request): JsonResponse
    {
        $request->validate([
            'result_id' => 'required|integer',
        ]);

        $result = EvaluationResult::find($request->input('result_id'));

        if (!$result) {
            return response()->json([
                'success' => false,
                'error' => 'Result not found',
            ], 404);
        }

        $fieldMetrics = $result->field_metrics ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'method' => $result->method,
                'field_metrics' => $fieldMetrics,
                'overall' => [
                    'accuracy' => $result->accuracy,
                    'precision' => $result->precision_score,
                    'recall' => $result->recall_score,
                    'f1_score' => $result->f1_score,
                ],
            ],
        ]);
    }
}
