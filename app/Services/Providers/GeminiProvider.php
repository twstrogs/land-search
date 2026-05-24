<?php

namespace App\Services\Providers;

use App\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;
    private string $systemPrompt;

    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
        $this->timeout = config('services.gemini.timeout', 60);
        $this->systemPrompt = $this->buildSystemPrompt();
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
    Chỉ giữ phần giá chính.
- area_text (string|null): Diện tích.
- frontage_texts (array of string): Kích thước mặt tiền.
- frontage_count (integer): Số mặt tiền.
- depth_text (string|null): Chiều sâu.
- address_text (string|null): Địa chỉ ngắn gọn.
- ward (string|null)
- district (string|null)
- city (string|null)
- property_type (string|null): Một trong 4: "Căn hộ chung cư", "Nhà phố/Nhà riêng", "Biệt thự (Villa)", "Đất nền/Đất thổ cư"
- phones (array of string): Số điện thoại.
- features (array of string): Đặc điểm nổi bật.
- facebook_url (string|null): Link Facebook.
- confidence (number from 0 to 1)
PROMPT;
    }

    public function extract(string $content): array
    {
        $startTime = microtime(true);
        
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gemini API key not configured',
                'processing_time_ms' => 0,
                'provider' => $this->getProviderName(),
                'model' => $this->model,
            ];
        }

        try {
            $url = sprintf('%s/%s:generateContent?key=%s', 
                self::BASE_URL, 
                $this->model, 
                $this->apiKey
            );

            $prompt = $this->systemPrompt . "\n\nInput text:\n" . $content;

            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0,
                        'topP' => 0.95,
                        'topK' => 40,
                    ],
                ]);

            if (!$response->successful()) {
                throw new \Exception('Gemini API error: ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();
            
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $result = $this->decodeJsonLenient($text);

            return [
                'success' => (bool) $result,
                'data' => $result,
                'processing_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'provider' => $this->getProviderName(),
                'model' => $this->model,
            ];
        } catch (\Exception $e) {
            Log::error('GeminiProvider extraction failed', ['error' => $e->getMessage()]);
            
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
        return 'gemini';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function checkHealth(): array
    {
        $startTime = microtime(true);
        
        if (!$this->isConfigured()) {
            return [
                'available' => false,
                'error' => 'Gemini API key not configured',
                'latency_ms' => 0,
            ];
        }
        
        try {
            $url = sprintf('%s/%s:generateContent?key=%s', 
                self::BASE_URL, 
                $this->model, 
                $this->apiKey
            );
            
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        'parts' => [
                            ['text' => 'Hi']
                        ]
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 5,
                    ],
                ]);
            
            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);
            
            if (!$response->successful()) {
                $errorBody = $response->json();
                return [
                    'available' => false,
                    'error' => $errorBody['error']['message'] ?? 'Gemini API error: ' . $response->status(),
                    'latency_ms' => $latencyMs,
                ];
            }
            
            return [
                'available' => true,
                'model' => $this->model,
                'latency_ms' => $latencyMs,
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
