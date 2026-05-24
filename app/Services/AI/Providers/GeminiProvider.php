<?php

namespace App\Services\AI\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider extends AbstractAiProvider
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->model = Setting::getValue('ai.gemini_model', 'gemini-2.0-flash');
        $this->apiKey = Setting::getValue('ai.gemini_api_key', '');
        $this->systemPrompt = $this->getExtractionSystemPrompt();
    }

    public function extract(string $content): array
    {
        try {
            $startTime = microtime(true);

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(60)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "{$this->systemPrompt}\n\nNỘI DUNG CẦN TRÍCH XUẤT:\n{$content}",
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'topP' => 0.9,
                        'maxOutputTokens' => 2048,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Gemini extraction failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['error' => 'Gemini request failed'];
            }

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $extracted = $this->parseJsonResponse($text);
            $extracted['_meta'] = [
                'provider' => 'gemini',
                'model' => $this->model,
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            ];

            return $extracted;
        } catch (\Exception $e) {
            Log::error('Gemini extraction exception', [
                'error' => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function checkHealth(): array
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$this->apiKey}";

            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'provider' => 'gemini',
                    'model' => $this->model,
                ];
            }

            return [
                'status' => 'unhealthy',
                'provider' => 'gemini',
                'error' => 'API request failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'provider' => 'gemini',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getName(): string
    {
        return 'Google Gemini';
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
