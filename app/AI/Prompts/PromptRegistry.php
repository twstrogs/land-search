<?php

namespace App\AI\Prompts;

use App\Models\PromptVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Prompt Registry
 * 
 * Central management for prompt templates:
 * - Version control
 * - Active prompt selection
 * - Few-shot example management
 * - A/B testing support
 */
class PromptRegistry
{
    private array $cache = [];
    private int $cacheTtl = 3600; // 1 hour

    /**
     * Get active prompt for a task type
     */
    public function getActivePrompt(string $taskType): ?PromptVersion
    {
        $cacheKey = "prompt_active_{$taskType}";
        
        // Try cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $prompt = PromptVersion::where('name', $taskType)
                ->where('is_active', true)
                ->first();

            if ($prompt) {
                $this->cache[$cacheKey] = $prompt;
                return $prompt;
            }

            // Try to create default
            $prompt = $this->ensureDefaultPrompt($taskType);
            if ($prompt) {
                $this->cache[$cacheKey] = $prompt;
                return $prompt;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning("[PromptRegistry] Failed to get active prompt: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Get prompt by ID
     */
    public function getById(int $id): ?PromptVersion
    {
        $cacheKey = "prompt_id_{$id}";
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $prompt = PromptVersion::find($id);
            if ($prompt) {
                $this->cache[$cacheKey] = $prompt;
            }
            return $prompt;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all prompt versions for a task type
     */
    public function getVersions(string $taskType): array
    {
        try {
            return PromptVersion::where('name', $taskType)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Create a new prompt version
     */
    public function createVersion(
        string $taskType,
        string $systemPrompt,
        array $fewShotExamples = [],
        ?array $outputSchema = null,
        bool $activate = true
    ): PromptVersion {
        // Get latest version number
        $latestVersion = PromptVersion::where('name', $taskType)
            ->orderBy('version', 'desc')
            ->first();

        $versionNumber = $latestVersion 
            ? $this->incrementVersion($latestVersion->version)
            : 'v1.0';

        $prompt = PromptVersion::create([
            'name' => $taskType,
            'version' => $versionNumber,
            'system_prompt' => $systemPrompt,
            'few_shot_examples' => $fewShotExamples,
            'output_schema' => $outputSchema,
            'is_active' => $activate,
        ]);

        // Deactivate other versions if this is activated
        if ($activate) {
            $this->deactivateOthers($taskType, $prompt->id);
        }

        // Clear cache
        $this->clearCache();

        Log::info("[PromptRegistry] Created new prompt version", [
            'task_type' => $taskType,
            'version' => $versionNumber,
            'id' => $prompt->id,
        ]);

        return $prompt;
    }

    /**
     * Update an existing prompt
     */
    public function updateVersion(int $id, array $data): ?PromptVersion
    {
        $prompt = PromptVersion::find($id);
        
        if (!$prompt) {
            return null;
        }

        $prompt->fill($data);
        $prompt->save();

        // Clear cache
        $this->clearCache();

        Log::info("[PromptRegistry] Updated prompt version", [
            'id' => $id,
            'version' => $prompt->version,
        ]);

        return $prompt;
    }

    /**
     * Activate a specific prompt version
     */
    public function activateVersion(int $id): bool
    {
        $prompt = PromptVersion::find($id);
        
        if (!$prompt) {
            return false;
        }

        // Deactivate all other versions
        $this->deactivateOthers($prompt->name, $id);

        // Activate this one
        $prompt->is_active = true;
        $prompt->save();

        // Clear cache
        $this->clearCache();

        Log::info("[PromptRegistry] Activated prompt version", [
            'id' => $id,
            'name' => $prompt->name,
            'version' => $prompt->version,
        ]);

        return true;
    }

    /**
     * Deactivate all other versions
     */
    private function deactivateOthers(string $taskType, int $exceptId): void
    {
        PromptVersion::where('name', $taskType)
            ->where('id', '!=', $exceptId)
            ->update(['is_active' => false]);
    }

    /**
     * Delete a prompt version
     */
    public function deleteVersion(int $id): bool
    {
        $prompt = PromptVersion::find($id);
        
        if (!$prompt) {
            return false;
        }

        $wasActive = $prompt->is_active;
        $taskType = $prompt->name;

        $prompt->delete();

        // If deleted version was active, activate the latest
        if ($wasActive) {
            $latest = PromptVersion::where('name', $taskType)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($latest) {
                $latest->is_active = true;
                $latest->save();
            }
        }

        // Clear cache
        $this->clearCache();

        return true;
    }

    /**
     * Duplicate a prompt version
     */
    public function duplicateVersion(int $id): ?PromptVersion
    {
        $original = PromptVersion::find($id);
        
        if (!$original) {
            return null;
        }

        return $this->createVersion(
            $original->name,
            $original->system_prompt,
            $original->few_shot_examples ?? [],
            $original->output_schema,
            false // Don't activate by default
        );
    }

    /**
     * Get few-shot examples
     */
    public function getFewShotExamples(string $taskType, int $count = 3): array
    {
        $prompt = $this->getActivePrompt($taskType);
        
        if (!$prompt || empty($prompt->few_shot_examples)) {
            return $this->getDefaultExamples($taskType);
        }

        $examples = $prompt->few_shot_examples;
        
        // Ensure it's an array
        if (is_string($examples)) {
            $examples = json_decode($examples, true) ?? [];
        }

        return array_slice($examples, 0, $count);
    }

    /**
     * Set few-shot examples for a prompt
     */
    public function setFewShotExamples(int $promptId, array $examples): bool
    {
        $prompt = PromptVersion::find($promptId);
        
        if (!$prompt) {
            return false;
        }

        $prompt->few_shot_examples = $examples;
        $prompt->save();

        // Clear cache
        $this->clearCache();

        return true;
    }

    /**
     * Ensure default prompt exists for task type
     */
    private function ensureDefaultPrompt(string $taskType): ?PromptVersion
    {
        $defaults = $this->getDefaultPrompts();
        $default = $defaults[$taskType] ?? null;

        if (!$default) {
            return null;
        }

        return $this->createVersion(
            $taskType,
            $default['system_prompt'],
            $default['examples'] ?? [],
            $default['schema'] ?? null,
            true
        );
    }

    /**
     * Get default prompts for each task type
     */
    public function getDefaultPrompts(): array
    {
        return [
            'property_extraction' => [
                'system_prompt' => $this->getPropertyExtractionPrompt(),
                'examples' => $this->getPropertyExamples(),
                'schema' => $this->getPropertySchema(),
            ],
            'spam_detection' => [
                'system_prompt' => $this->getSpamDetectionPrompt(),
                'examples' => $this->getSpamExamples(),
                'schema' => $this->getSpamSchema(),
            ],
            'price_analysis' => [
                'system_prompt' => $this->getPriceAnalysisPrompt(),
                'examples' => [],
                'schema' => $this->getPriceSchema(),
            ],
            'feature_tagging' => [
                'system_prompt' => $this->getFeatureTaggingPrompt(),
                'examples' => [],
                'schema' => $this->getFeatureSchema(),
            ],
        ];
    }

    /**
     * Get default examples for task type
     */
    public function getDefaultExamples(string $taskType): array
    {
        $defaults = $this->getDefaultPrompts();
        return $defaults[$taskType]['examples'] ?? [];
    }

    /**
     * Increment version string
     */
    private function incrementVersion(string $version): string
    {
        // Handle v1.0, v1.1, v2.0 formats
        if (preg_match('/^v(\d+)\.(\d+)$/', $version, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2] + 1;
            return "v{$major}.{$minor}";
        }

        // Default increment
        return $version . '.1';
    }

    /**
     * Clear all cached prompts
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Get all available task types
     */
    public function getAvailableTaskTypes(): array
    {
        return array_keys($this->getDefaultPrompts());
    }

    // Prompt Templates

    private function getPropertyExtractionPrompt(): string
    {
        return <<<'PROMPT'
Bạn là CHUYÊN GIA TRÍCH XUẤT THÔNG TIN BẤT ĐỘNG SẢN VIỆT NAM.

## NHIỆM VỤ
Trích xuất thông tin từ bài đăng BĐS và trả về DUY NHẤT một JSON object hợp lệ.

## CÁC TRƯỜNG CẦN TRÍCH XUẤT
[See PromptEngineeringStage for full prompt content]
PROMPT;
    }

    private function getPropertyExamples(): array
    {
        return [
            [
                'input' => 'Bán nhà 3 tầng mặt đường Ngô Gia Tự, phường Tân Thịnh, TP Thái Nguyên. Diện tích 120m2, 4 phòng ngủ, 3 toilet. Nhà mới xây, nội thất đẹp. Giá 2.5 tỷ. LH: 0981234567',
                'output' => [
                    'title' => 'Bán nhà 3 tầng mặt đường Ngô Gia Tự, TP Thái Nguyên',
                    'property_type' => 'Nhà phố/Nhà riêng',
                    'price_text' => '2.5 tỷ',
                    'price_value' => 2500000000,
                    'area_value' => 120,
                    'phones' => ['0981234567'],
                    'confidence' => 0.95,
                ],
            ],
        ];
    }

    private function getPropertySchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'property_type' => ['type' => 'string', 'enum' => ['Căn hộ chung cư', 'Nhà phố/Nhà riêng', 'Biệt thự (Villa)', 'Đất nền/Đất thổ cư']],
                'price_text' => ['type' => 'string'],
                'price_value' => ['type' => 'number'],
                'area_value' => ['type' => 'number'],
                'phones' => ['type' => 'array', 'items' => ['type' => 'string']],
                'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
            ],
            'required' => ['confidence'],
        ];
    }

    private function getSpamDetectionPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia phát hiện spam trong các bài đăng bất động sản Việt Nam.
PROMPT;
    }

    private function getSpamExamples(): array
    {
        return [
            [
                'input' => 'BÁN NHÀ GIÁ SHOCK 500TR!!! Gọi ngay 0999999999!',
                'output' => ['is_spam' => true, 'spam_score' => 0.85],
            ],
        ];
    }

    private function getSpamSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'is_spam' => ['type' => 'boolean'],
                'spam_score' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
            ],
            'required' => ['is_spam', 'spam_score'],
        ];
    }

    private function getPriceAnalysisPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia phân tích giá bất động sản Việt Nam.
PROMPT;
    }

    private function getPriceSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'price_text' => ['type' => 'string'],
                'price_value' => ['type' => 'number'],
                'price_per_m2' => ['type' => 'number'],
            ],
        ];
    }

    private function getFeatureTaggingPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia trích xuất đặc điểm bất động sản Việt Nam.
PROMPT;
    }

    private function getFeatureSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'features' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ];
    }
}
