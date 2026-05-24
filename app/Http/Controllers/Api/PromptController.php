<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\AI\Prompts\PromptRegistry;
use App\Models\PromptVersion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PromptController extends Controller
{
    private PromptRegistry $registry;

    public function __construct()
    {
        $this->registry = new PromptRegistry();
    }

    /**
     * Get all prompt versions
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'task_type' => 'string|nullable',
        ]);

        $taskType = $request->input('task_type');

        if ($taskType) {
            $versions = $this->registry->getVersions($taskType);
        } else {
            $versions = PromptVersion::orderBy('created_at', 'desc')->get()->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => $versions,
        ]);
    }

    /**
     * Get active prompt
     */
    public function active(Request $request): JsonResponse
    {
        $request->validate([
            'task_type' => 'required|string',
        ]);

        $taskType = $request->input('task_type');
        $prompt = $this->registry->getActivePrompt($taskType);

        if (!$prompt) {
            return response()->json([
                'success' => false,
                'error' => 'No active prompt found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $prompt,
        ]);
    }

    /**
     * Create new prompt version
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'task_type' => 'required|string',
            'system_prompt' => 'required|string',
            'few_shot_examples' => 'array|nullable',
            'output_schema' => 'array|nullable',
            'activate' => 'boolean',
        ]);

        try {
            $prompt = $this->registry->createVersion(
                $request->input('task_type'),
                $request->input('system_prompt'),
                $request->input('few_shot_examples', []),
                $request->input('output_schema'),
                $request->input('activate', false)
            );

            return response()->json([
                'success' => true,
                'data' => $prompt,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update prompt version
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'system_prompt' => 'string|nullable',
            'few_shot_examples' => 'array|nullable',
            'output_schema' => 'array|nullable',
        ]);

        try {
            $data = array_filter([
                'system_prompt' => $request->input('system_prompt'),
                'few_shot_examples' => $request->input('few_shot_examples'),
                'output_schema' => $request->input('output_schema'),
            ], fn($v) => $v !== null);

            $prompt = $this->registry->updateVersion($id, $data);

            if (!$prompt) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prompt not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $prompt,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate prompt version
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $success = $this->registry->activateVersion($id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prompt not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prompt activated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete prompt version
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->registry->deleteVersion($id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prompt not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prompt deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate prompt version
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $prompt = $this->registry->duplicateVersion($id);

            if (!$prompt) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prompt not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $prompt,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get few-shot examples
     */
    public function examples(Request $request): JsonResponse
    {
        $request->validate([
            'task_type' => 'required|string',
            'count' => 'integer|min:1|max:10',
        ]);

        $examples = $this->registry->getFewShotExamples(
            $request->input('task_type'),
            $request->input('count', 3)
        );

        return response()->json([
            'success' => true,
            'data' => $examples,
        ]);
    }

    /**
     * Update few-shot examples
     */
    public function updateExamples(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'examples' => 'required|array',
        ]);

        try {
            $success = $this->registry->setFewShotExamples($id, $request->input('examples'));

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prompt not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Examples updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available task types
     */
    public function taskTypes(): JsonResponse
    {
        $types = $this->registry->getAvailableTaskTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
