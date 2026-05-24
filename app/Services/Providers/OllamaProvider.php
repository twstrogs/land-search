<?php

namespace App\Services\Providers;

use App\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements AiProviderInterface
{
    private string $url;
    private string $model;
    private int $timeout;
    private string $systemPrompt;
    private bool $cloudMode;
    private string $apiKey;

    public function __construct(?string $providerType = null)
    {
        $this->url = config('services.ollama.url', 'http://localhost:11434');
        $this->model = config('services.ollama.model', 'llama3.2');
        $this->timeout = config('services.ollama.timeout', 120);
        $this->systemPrompt = $this->buildSystemPrompt();
        
        // Determine cloud mode based on provider type
        if ($providerType === 'ollama-cloud') {
            $this->cloudMode = true;
            $this->apiKey = config('services.ollama.api_key', '');
        } elseif ($providerType === 'ollama-local') {
            $this->cloudMode = false;
            $this->apiKey = '';
        } else {
            // Fallback to env config
            $this->cloudMode = config('services.ollama.cloud_mode', false);
            $this->apiKey = config('services.ollama.api_key', '');
        }
    }

    private function getApiBaseUrl(): string
    {
        return $this->cloudMode ? 'https://ollama.com' : $this->url;
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
Bạn là một công cụ trích xuất thông tin cho các bài đăng bất động sản trên Facebook khu vực Thái Nguyên, Việt Nam.
YÊU CẦU BẮT BUỘC:
- Chỉ trả về DUY NHẤT 1 JSON object hợp lệ.
- Không thêm markdown, không code block, không giải thích, không text thừa.
- Nếu thiếu dữ liệu thì để null hoặc mảng rỗng.

Trả về một đối tượng json với các khóa sau:
- title (string|null)
- description (string|null)
- price_text (string|null):
        QUY TẮC ĐƠN VỊ BẮT BUỘC:
            - "tr", "triệu", "củ" => TRIỆU (10^6), KHÔNG BAO GIỜ là tỷ.
            - "ty", "tỷ", "tỉ" => TỶ (10^9).
            - Với ký hiệu "t" đứng một mình, áp dụng quy tắc phân giải:
                * Nếu số đứng trước "t" có 1-2 chữ số (ví dụ: "6t", "12t") => hiểu là TỶ.
                * Nếu số đứng trước "t" có từ 3 chữ số trở lên (ví dụ: "600t", "900t") => hiểu là TRIỆU.
        Ví dụ bắt buộc đúng:
            - "8xxtr" -> "800 triệu"
            - "900tr" -> "900 triệu"
            - "600t" -> "600 triệu"
            - "6t" -> "6 tỷ"
            - "1t5xx" -> "1.5 tỷ"
    Chỉ giữ phần giá chính, không thêm chữ giải thích.
- area_text (string|null): Giữ nguyên thông tin diện tích.
- frontage_texts (array of string): Trích kích thước mặt tiền.
- frontage_count (integer): Số mặt tiền.
- depth_text (string|null): Chiều sâu.
- address_text (string|null): Địa chỉ ngắn gọn.
- ward (string|null)
- district (string|null)
- city (string|null)
- property_type (string|null): Chỉ được 1 trong 4: "Căn hộ chung cư", "Nhà phố/Nhà riêng", "Biệt thự (Villa)", "Đất nền/Đất thổ cư"
- phones (array of string): Số điện thoại Việt Nam.
- features (array of string): Đặc điểm nổi bật ngắn gọn.
- facebook_url (string|null): Link Facebook.
- confidence (number from 0 to 1)
PROMPT;
    }

    public function extract(string $content): array
    {
        $startTime = microtime(true);
        $apiUrl = $this->getApiBaseUrl();
        
        try {
            $requestBody = [
                'model' => $this->model,
                'prompt' => "Input text:\n{$content}",
                'system' => $this->systemPrompt,
                'stream' => false,
                'format' => 'json',
                'options' => ['temperature' => 0],
            ];

            $http = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json']);

            if ($this->cloudMode && !empty($this->apiKey)) {
                $http = $http->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ]);
            }

            $response = $http->post("{$apiUrl}/api/generate", $requestBody);

            if (!$response->successful()) {
                throw new \Exception('Ollama API error: ' . $response->status());
            }

            $data = $response->json();
            $result = $this->decodeJsonLenient($data['response'] ?? '');
            
            return [
                'success' => (bool) $result,
                'data' => $result,
                'processing_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'provider' => $this->getProviderName(),
                'model' => $this->model,
            ];
        } catch (\Exception $e) {
            Log::error('OllamaProvider extraction failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'processing_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'provider' => $this->getProviderName(),
                'model' => $this->model,
            ];
        }
    }

    public function getProviderName(): string
    {
        return 'ollama';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function isConfigured(): bool
    {
        if ($this->cloudMode) {
            return !empty($this->model) && !empty($this->apiKey);
        }
        return !empty($this->url) && !empty($this->model);
    }

    public function checkHealth(): array
    {
        $startTime = microtime(true);
        $apiUrl = $this->getApiBaseUrl();
        
        try {
            // Build HTTP client with optional auth
            $http = Http::timeout(10);

            if ($this->cloudMode && !empty($this->apiKey)) {
                $http = $http->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ]);
            }

            // Test server connectivity
            $pingResponse = $http->get("{$apiUrl}/");

            // Test model với request đơn giản
            $testStart = microtime(true);
            $testResponse = $http
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post("{$apiUrl}/api/generate", [
                    'model' => $this->model,
                    'prompt' => 'Hi',
                    'stream' => false,
                    'options' => [
                        'num_predict' => 5,
                    ],
                ]);
            
            $testLatencyMs = (int) ((microtime(true) - $testStart) * 1000);
            $totalLatencyMs = (int) ((microtime(true) - $startTime) * 1000);
            
            if (!$testResponse->successful()) {
                $errorBody = $testResponse->body();
                $errorMsg = 'Model không phản hồi';
                
                // Parse error if possible
                $errorData = json_decode($errorBody, true);
                if (isset($errorData['error'])) {
                    $errorMsg = $errorData['error'];
                }
                
                return [
                    'available' => false,
                    'error' => $errorMsg,
                    'latency_ms' => $totalLatencyMs,
                    'test_latency_ms' => $testLatencyMs,
                ];
            }
            
            return [
                'available' => true,
                'current_model' => $this->model,
                'latency_ms' => $totalLatencyMs,
                'test_latency_ms' => $testLatencyMs,
            ];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'error' => $e->getMessage(),
                'latency_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ];
        }
    }

    private function decodeJsonLenient(string $text): ?array
    {
        $text = trim($text);
        
        if (preg_match('/```json\s*([\s\S]*?)\s*```/i', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/```\s*([\s\S]*?)\s*```/i', $text, $matches)) {
            $text = $matches[1];
        }
        
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        
        if ($start !== false && $end !== false && $end > $start) {
            $text = substr($text, $start, $end - $start + 1);
        }
        
        $decoded = json_decode(trim($text), true);
        
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
