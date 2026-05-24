<?php

namespace App\Services\AI\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider extends AbstractAiProvider
{
    protected string $baseUrl;

    protected ?string $apiKey;

    /** True when using ollama.com API (Bearer key), not a self-hosted daemon. */
    protected bool $usesOllamaCloud;

    public function __construct(array $overrides = [])
    {
        $this->model = (string) ($overrides['model'] ?? Setting::getValue('ai.ollama_model', 'gpt-oss:120b-cloud'));
        $this->apiKey = $overrides['api_key'] ?? Setting::getValue('ai.ollama_api_key');
        $storedUrl = (string) ($overrides['url'] ?? Setting::getValue('ai.ollama_url', 'https://ollama.com'));

        $this->usesOllamaCloud = filled($this->apiKey);

        // API keys chỉ áp dụng cho https://ollama.com — không gọi /api/tags tới LAN khi đã có key.
        $rawBase = $this->usesOllamaCloud ? 'https://ollama.com' : $storedUrl;
        $this->baseUrl = $this->normalizeOllamaBaseUrl($rawBase);
        $this->systemPrompt = $this->getExtractionSystemPrompt();
    }

    /**
     * Chuẩn hoá URL gốc: bỏ dư / và hậu tố /api (route vẫn là /api/tags, /api/generate).
     */
    private function normalizeOllamaBaseUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');
        if (str_ends_with($url, '/api')) {
            $url = substr($url, 0, -strlen('/api'));
        }

        return rtrim($url, '/');
    }

    /**
     * Tài liệu ollama.com dùng tên ví dụ gpt-oss:120b; model UI có thể là gpt-oss:120b-cloud.
     */
    private function remoteApiModel(): string
    {
        if (! $this->usesOllamaCloud) {
            return $this->model;
        }
        if (str_ends_with($this->model, '-cloud')) {
            return substr($this->model, 0, -strlen('-cloud'));
        }

        return $this->model;
    }

    /** Kiểm tra kết nối qua REST giống trình chủ (tags cho local + cloud có key). */
    private function connectsViaOllamaCom(): bool
    {
        return $this->usesOllamaCloud
            || str_contains(parse_url($this->baseUrl, PHP_URL_HOST) ?: '', 'ollama.com');
    }

    public function extract(string $content): array
    {
        try {
            $startTime = microtime(true);

            $headers = ['Content-Type' => 'application/json'];
            if ($this->apiKey) {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            $response = Http::timeout(120)
                ->withHeaders($headers)
                ->post("{$this->baseUrl}/api/generate", [
                    'model' => $this->remoteApiModel(),
                    'prompt' => $this->buildPrompt($content),
                    'stream' => false,
                    'options' => [
                        'temperature' => 0.1,
                        'top_p' => 0.9,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Ollama extraction failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['error' => 'Ollama request failed'];
            }

            $result = $response->json();
            $extracted = $this->parseJsonResponse($result['response'] ?? '');
            $extracted['_meta'] = [
                'provider' => 'ollama',
                'model' => $this->model,
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000),
            ];

            return $extracted;
        } catch (\Exception $e) {
            Log::error('Ollama extraction exception', [
                'error' => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function checkHealth(): array
    {
        try {
            $http = Http::timeout($this->connectsViaOllamaCom() ? 20 : 10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]);

            if ($this->apiKey) {
                $http = $http->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ]);
            }

            // 1) Ping server bằng /api/tags (nhẹ).
            $tagsResponse = $http->get("{$this->baseUrl}/api/tags");

            if (! $tagsResponse->successful()) {
                return [
                    'status' => 'unhealthy',
                    'provider' => 'ollama',
                    'endpoint' => $this->baseUrl.'/api/tags',
                    'error' => 'HTTP '.$tagsResponse->status().': '.$tagsResponse->body(),
                ];
            }

            $data = $tagsResponse->json();
            $models = $data['models'] ?? [];
            $names = array_column($models, 'name');

            // 2) Kiểm tra model theo lựa chọn trong settings.
            //    - Cloud (ollama.com + API key): /api/chat (xác nhận model chạy được)
            //    - Local daemon: /api/generate (xác nhận model tồn tại/chạy được)
            $selectedModel = $this->remoteApiModel();
            $isCloud = $this->connectsViaOllamaCom() && filled($this->apiKey);

            if ($isCloud) {
                $modelResponse = $http
                    ->timeout(30)
                    ->post("{$this->baseUrl}/api/chat", [
                        'model' => $selectedModel,
                        'messages' => [
                            ['role' => 'user', 'content' => 'Hi'],
                        ],
                        'stream' => false,
                    ]);
            } else {
                $modelResponse = $http
                    ->timeout(60)
                    ->post("{$this->baseUrl}/api/generate", [
                        'model' => $selectedModel,
                        'prompt' => 'Hi',
                        'stream' => false,
                        'options' => [
                            'num_predict' => 5,
                        ],
                    ]);
            }

            if (! $modelResponse->successful()) {
                return [
                    'status' => 'unhealthy',
                    'provider' => 'ollama',
                    'endpoint' => $this->baseUrl,
                    'model' => $this->model,
                    'remote_api_model' => $selectedModel,
                    'error' => 'Model check failed: HTTP '.$modelResponse->status().': '.$modelResponse->body(),
                ];
            }

            if ($isCloud) {
                return [
                    'status' => 'healthy',
                    'provider' => 'ollama',
                    'endpoint' => $this->baseUrl,
                    'model' => $this->model,
                    'remote_api_model' => $selectedModel,
                    'message' => 'Kết nối tới Ollama OK',
                ];
            }

            return [
                'status' => 'healthy',
                'provider' => 'ollama',
                'endpoint' => $this->baseUrl,
                'models' => $names,
                'current_model' => $this->model,
                'message' => 'Kết nối tới Ollama OK',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'provider' => 'ollama',
                'endpoint' => $this->baseUrl.'/api/tags',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getName(): string
    {
        return 'Ollama';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    private function buildPrompt(string $content): string
    {
        return <<<PROMPT
{$this->systemPrompt}

NỘI DUNG CẦN TRÍCH XUẤT:
{$content}

Trả về JSON:
PROMPT;
    }
}
