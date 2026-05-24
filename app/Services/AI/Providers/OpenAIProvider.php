<?php

namespace App\Services\AI\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider extends AbstractAiProvider
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->model = 'gpt-4o';
        $this->apiKey = Setting::getValue('ai.openai_api_key', '');
        $this->systemPrompt = $this->getExtractionSystemPrompt();
    }

    public function extract(string $content): array
    {
        try {
            $startTime = microtime(true);

            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => "NỘI DUNG CẦN TRÍCH XUẤT:\n{$content}",
                        ],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 2048,
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI extraction failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['error' => 'OpenAI request failed'];
            }

            $result = $response->json();
            $text = $result['choices'][0]['message']['content'] ?? '';
            $extracted = $this->parseJsonResponse($text);
            $extracted['_meta'] = [
                'provider' => 'openai',
                'model' => $this->model,
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            ];

            return $extracted;
        } catch (\Exception $e) {
            Log::error('OpenAI extraction exception', [
                'error' => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function checkHealth(): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'provider' => 'openai',
                    'model' => $this->model,
                ];
            }

            return [
                'status' => 'unhealthy',
                'provider' => 'openai',
                'error' => 'API request failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'provider' => 'openai',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
