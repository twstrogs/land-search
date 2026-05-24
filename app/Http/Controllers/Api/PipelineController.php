<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\AI\Pipeline\AIPipeline;
use App\AI\Pipeline\Stages\TextPreprocessingStage;
use App\AI\Pipeline\Stages\PromptEngineeringStage;
use App\AI\Pipeline\Stages\LLMExtractionStage;
use App\AI\Pipeline\Stages\ValidationStage;
use App\AI\Pipeline\Stages\NormalizationStage;
use App\AI\Pipeline\Stages\StorageStage;
use App\Models\ExtractionLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PipelineController extends Controller
{
    private ?AIPipeline $pipeline = null;

    /**
     * Get pipeline instance
     */
    private function getPipeline(): AIPipeline
    {
        if ($this->pipeline) {
            return $this->pipeline;
        }

        $this->pipeline = new AIPipeline(
            new TextPreprocessingStage(),
            new PromptEngineeringStage(),
            new LLMExtractionStage(),
            new ValidationStage(),
            new NormalizationStage(),
            (new StorageStage())->skipStorage()
        );

        return $this->pipeline;
    }

    /**
     * Run extraction through pipeline
     */
    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'provider' => 'string|nullable',
        ]);

        $content = $request->input('content');
        $context = $request->input('context', []);

        if ($request->has('provider')) {
            $context['provider'] = $request->input('provider');
        }

        try {
            $pipeline = $this->getPipeline();
            $result = $pipeline->process($content, $context);

            // Log extraction
            $this->logExtraction($content, $result, $context);

            return response()->json([
                'success' => $result->success,
                'data' => [
                    'normalized' => $result->data,
                    'confidence' => $result->confidence,
                    'validation' => $result->validation ?? null,
                    'stages_completed' => $result->stagesCompleted,
                    'total_time_ms' => $result->totalTimeMs,
                ],
                'pipeline_id' => $result->pipelineId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run batch extraction
     */
    public function extractBatch(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.content' => 'required|string',
            'items.*.context' => 'array|nullable',
        ]);

        $items = $request->input('items');

        try {
            $pipeline = $this->getPipeline();
            
            $results = [];
            $progress = 0;
            
            foreach ($items as $item) {
                $result = $pipeline->process($item['content'], $item['context'] ?? []);
                $results[] = [
                    'success' => $result->success,
                    'confidence' => $result->confidence,
                    'data' => $result->data,
                ];
                
                $progress++;
                
                // Could emit progress event here
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'total' => count($results),
                    'successful' => count(array_filter($results, fn($r) => $r['success'])),
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
     * Get pipeline status
     */
    public function status(): JsonResponse
    {
        $pipeline = $this->getPipeline();
        $status = $pipeline->getStatus();

        // Get extraction statistics
        $stats = ExtractionLog::getRecentStats();

        return response()->json([
            'success' => true,
            'data' => [
                'pipeline' => $status,
                'statistics' => $stats,
            ],
        ]);
    }

    /**
     * Get extraction statistics
     */
    public function stats(): JsonResponse
    {
        $stats = ExtractionLog::getRecentStats();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $stats['total'],
                'avg_confidence' => $stats['avg_confidence'],
                'avg_time_ms' => $stats['avg_time_ms'],
                'max_time_ms' => $stats['max_time_ms'],
                'min_time_ms' => $stats['min_time_ms'],
                'status_counts' => $stats['status_counts'],
                'success_rate' => $stats['total'] > 0 
                    ? round(($stats['status_counts']['passed'] ?? 0) / $stats['total'] * 100, 2)
                    : 0,
            ],
        ]);
    }

    /**
     * Get extraction logs
     */
    public function logs(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
            'post_id' => 'integer|nullable',
            'status' => 'string|in:passed,failed,warning,pending|nullable',
        ]);

        $query = ExtractionLog::query()
            ->with('post')
            ->orderBy('created_at', 'desc');

        if ($request->has('post_id')) {
            $query->where('post_id', $request->input('post_id'));
        }

        if ($request->has('status')) {
            $query->where('validation_status', $request->input('status'));
        }

        $logs = $query
            ->limit($request->input('limit', 20))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Log extraction
     */
    private function logExtraction(string $content, $result, array $context): void
    {
        try {
            ExtractionLog::create([
                'raw_content' => $content,
                'raw_response' => $result->data ?? [],
                'normalized_data' => $result->data,
                'confidence_score' => $result->confidence,
                'processing_time_ms' => $result->totalTimeMs,
                'validation_status' => $result->validation['passed'] ?? false ? 'passed' : 'failed',
                'validation_errors' => $result->validation['errors'] ?? [],
                'ai_provider' => $context['provider'] ?? 'unknown',
                'ai_model' => 'unknown',
            ]);
        } catch (\Exception $e) {
            // Silently fail logging
        }
    }
}
