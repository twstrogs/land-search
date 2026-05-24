<?php

namespace App\AI\Pipeline\Stages;

use App\AI\Pipeline\PipelineStageInterface;
use App\AI\Exceptions\StageException;
use App\AI\Factories\AiServiceFactory;
use App\AI\Providers\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * LLM Extraction Stage
 * 
 * Handles LLM API calls with:
 * - Provider abstraction
 * - Retry mechanism
 * - Circuit breaker integration
 * - Error handling
 */
class LLMExtractionStage implements PipelineStageInterface
{
    private int $maxRetries = 3;
    private array $retryDelays = [1000, 3000, 10000]; // milliseconds
    private ?AiServiceFactory $factory = null;

    public function __construct(?AiServiceFactory $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * Execute LLM extraction
     */
    public function execute(array $data): array
    {
        $systemPrompt = $data['system_prompt'] ?? '';
        $userPrompt = $data['user_prompt'] ?? '';

        if (empty($systemPrompt) || empty($userPrompt)) {
            throw new StageException(
                'LLMExtractionStage',
                'Missing prompts for extraction',
                StageException::VALIDATION_ERROR
            );
        }

        $startTime = microtime(true);
        $providerName = $data['context']['provider'] ?? null;

        // Get optimal provider
        $provider = $this->getProvider($providerName);

        // Execute with retry
        $result = $this->executeWithRetry(function() use ($provider, $systemPrompt, $userPrompt) {
            return $provider->extractWithPrompt($systemPrompt, $userPrompt);
        });

        $processingTime = round((microtime(true) - $startTime) * 1000, 2);

        if (!$result['success']) {
            throw new StageException(
                'LLMExtractionStage',
                $result['error'] ?? 'LLM extraction failed',
                StageException::EXTRACTION_ERROR
            );
        }

        return array_merge($data, [
            'raw_extraction' => $result['data'],
            'extraction_meta' => [
                'provider' => $provider->getName(),
                'model' => $provider->getModel(),
                'processing_time_ms' => $processingTime,
                'tokens_used' => $result['tokens_used'] ?? null,
                'api_calls' => $result['api_calls'] ?? 1,
                'raw_response' => $result['raw'] ?? null,
            ],
        ]);
    }

    /**
     * Get AI provider
     */
    private function getProvider(?string $providerName): AiProviderInterface
    {
        if ($this->factory) {
            return $this->factory->make($providerName);
        }

        // Default factory
        return app(AiServiceFactory::class)->make($providerName);
    }

    /**
     * Execute with retry mechanism
     */
    private function executeWithRetry(callable $operation): array
    {
        $attempts = 0;
        $lastError = null;

        while ($attempts < $this->maxRetries) {
            $attempts++;

            try {
                $result = $operation();
                $result['api_calls'] = $attempts;
                return $result;

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastError = 'Connection error: ' . $e->getMessage();
                Log::warning("[LLMExtraction] Attempt {$attempts} failed: {$lastError}");

            } catch (\Illuminate\Http\Client\RequestException $e) {
                $statusCode = $e->response?->status();
                
                if ($statusCode === 429) {
                    // Rate limited - wait longer
                    $lastError = 'Rate limited (429)';
                    Log::warning("[LLMExtraction] Rate limited on attempt {$attempts}");
                    
                } elseif ($statusCode >= 500) {
                    // Server error - retry
                    $lastError = "Server error ({$statusCode})";
                    Log::warning("[LLMExtraction] Server error on attempt {$attempts}");

                } else {
                    // Client error - don't retry
                    throw new StageException(
                        'LLMExtractionStage',
                        "API error: {$statusCode} - " . $e->getMessage(),
                        StageException::EXTRACTION_ERROR
                    );
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("[LLMExtraction] Attempt {$attempts} failed: {$lastError}");
            }

            // Wait before retry
            if ($attempts < $this->maxRetries) {
                $delay = $this->retryDelays[$attempts - 1] ?? 5000;
                usleep($delay * 1000);
            }
        }

        return [
            'success' => false,
            'error' => $lastError ?? 'Max retries exceeded',
            'api_calls' => $attempts,
        ];
    }

    /**
     * Check if this is a critical stage
     */
    public function isCritical(): bool
    {
        return true;
    }

    /**
     * Get stage name
     */
    public function getName(): string
    {
        return 'extract';
    }

    /**
     * Get stage description
     */
    public function getDescription(): string
    {
        return 'LLM Extraction - AI provider calls, retry mechanism, response parsing';
    }

    /**
     * Get required input data keys
     */
    public function getRequiredInputs(): array
    {
        return ['system_prompt', 'user_prompt'];
    }

    /**
     * Get output data keys
     */
    public function getOutputs(): array
    {
        return ['raw_extraction', 'extraction_meta'];
    }

    /**
     * Validate input data
     */
    public function validateInput(array $data): void
    {
        if (!isset($data['system_prompt'])) {
            throw new StageException(
                'LLMExtractionStage',
                'Missing required input: system_prompt',
                StageException::VALIDATION_ERROR
            );
        }

        if (!isset($data['user_prompt'])) {
            throw new StageException(
                'LLMExtractionStage',
                'Missing required input: user_prompt',
                StageException::VALIDATION_ERROR
            );
        }
    }
}
