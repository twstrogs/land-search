<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private string $url;
    private string $model;
    private int $timeout;
    private string $systemPrompt;

    public function __construct()
    {
        $this->url = config('services.ollama.url', 'http://localhost:11434');
        $this->model = config('services.ollama.model', 'gpt-oss:120b-cloud');
        $this->timeout = config('services.ollama.timeout', 120);
        
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
            - Nếu chuỗi có cả "tỷ" và "tr" thì ưu tiên tách phần chính theo tỷ (ví dụ "2 tỷ 0xx tr" -> "2 tỷ").
        Ví dụ bắt buộc đúng:
            - "900tr" -> "900 triệu"
            - "6xxtr" -> "600 triệu"
            - "600t" -> "600 triệu"
            - "6t" -> "6 tỷ"
            - "670tr" -> "670 triệu"
            - "1t5xx" -> "1.5 tỷ"
            - "5,x tỷ" -> "5 tỷ"
            - "2 tỷ 0xx tr" -> "2 tỷ"
            - "1 tỷ 5xx tr" -> "1 tỷ 500 triệu"
        CẤM suy diễn sai: "900tr" tuyệt đối không được thành "900 tỷ"; "6xxtr" tuyệt đối không được thành "600 tỷ"; "600t" tuyệt đối không được thành "600 tỷ"; "6t" tuyệt đối không được thành "6 triệu".
    Chỉ giữ phần giá chính, không thêm chữ giải thích.
- area_text (string|null): Giữ nguyên thông tin diện tích theo kiểu con người phân tích, có thể gồm nhiều phần.
- frontage_texts (array of string): Trích tất cả kích thước mặt tiền xuất hiện trong bài.
- frontage_count (integer): Nếu có cụm "2 mặt tiền" thì frontage_count = 2, nếu có 1 số đo thì = 1, không xác định được thì = 0.
- depth_text (string|null): lấy chiều sâu nếu có.
- address_text (string|null): Ưu tiên cụm vị trí ngắn gọn.
- ward (string|null)
- district (string|null)
- city (string|null)
- property_type (string|null): BẮT BUỘC chỉ được chọn duy nhất 1 trong 4 giá trị: "Căn hộ chung cư", "Nhà phố/Nhà riêng", "Biệt thự (Villa)", "Đất nền/Đất thổ cư". Nếu không đủ dữ kiện thì trả null.
- phones (array of string): chuẩn hóa về chuỗi số liền nhau, chỉ giữ số điện thoại Việt Nam hợp lệ.
- features (array of string): chỉ giữ đặc điểm nổi bật ngắn gọn.
- facebook_url (string|null): lấy link https://www.facebook.com/... nếu có.
- confidence (number from 0 to 1)
PROMPT;
    }

    public function extract(string $content): array
    {
        $startTime = microtime(true);
        
        $prompt = $this->buildUserPrompt($content);
        
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->url}/api/generate", [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'system' => $this->systemPrompt,
                    'stream' => false,
                    'format' => 'json',
                    'options' => [
                        'temperature' => 0,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Ollama API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Ollama API returned error: ' . $response->status());
            }

            $data = $response->json();
            $result = $this->decodeJsonLenient($data['response'] ?? '');
            
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => true,
                'data' => $result,
                'processing_time_ms' => $processingTime,
            ];
        } catch (\Exception $e) {
            Log::error('AiService extraction failed', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'processing_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ];
        }
    }

    private function buildUserPrompt(string $content): string
    {
        return "Input text:\n{$content}";
    }

    public function decodeJsonLenient(string $text): ?array
    {
        $text = trim($text);
        
        if (preg_match('/```json\s*([\s\S]*?)\s*```/i', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/```\s*([\s\S]*?)\s*```/i', $text, $matches)) {
            $text = $matches[1];
        }
        
        $text = trim($text);
        
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        
        if ($start !== false && $end !== false && $end > $start) {
            $text = substr($text, $start, $end - $start + 1);
        }
        
        $text = trim($text);
        
        if (empty($text)) {
            return null;
        }
        
        $decoded = json_decode($text, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('JSON decode failed, trying lenient parsing', [
                'error' => json_last_error_msg(),
                'text_preview' => substr($text, 0, 200),
            ]);
            return null;
        }
        
        return $decoded;
    }
}
