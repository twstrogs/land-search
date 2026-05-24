<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractAiProvider implements AiProviderInterface
{
    protected string $model;
    protected string $systemPrompt;

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    protected function parseJsonResponse(string $content): array
    {
        $content = trim($content);
        
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
            $content = $matches[1];
        } elseif (preg_match('/```\s*(.*?)\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        $content = trim($content);
        
        if (str_starts_with($content, '{')) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        $content = preg_replace('/^[^{]*./', '{', $content, 1);
        $content = preg_replace('/[^}]*$/s', '}', $content);
        
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        Log::warning('AI response parsing failed', [
            'raw_content' => substr($content, 0, 500),
            'error' => json_last_error_msg(),
        ]);

        return [];
    }

    protected function getExtractionSystemPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia trích xuất thông tin bất động sản từ Việt Nam.
Trích xuất thông tin từ bài đăng và trả về DUY NHẤT một JSON object hợp lệ.

Các trường cần trích xuất:
- title: Tiêu đề bài đăng (string)
- description: Mô tả chi tiết (string)
- price_text: Giá dạng text như "1.5 tỷ", "800 triệu" (string)
- area_text: Diện tích dạng text như "100m2", "5 sào" (string)
- frontage_texts: Mảng mặt tiền, ví dụ ["5m", "10m"] (array)
- frontage_count: Số mặt tiền (integer)
- depth_text: Chiều sâu như "20m" (string)
- address_text: Địa chỉ đầy đủ (string)
- ward: Phường/Xã (string)
- district: Quận/Huyện (string)
- city: Tỉnh/Thành phố (string)
- property_type: Loại BĐS - 1 trong 4 giá trị: "Căn hộ chung cư", "Nhà phố/Nhà riêng", "Biệt thự (Villa)", "Đất nền/Đất thổ cư" (string)
- phones: Mảng số điện thoại VN (array)
- features: Mảng đặc điểm nổi bật (array)
- facebook_url: URL bài viết Facebook (string)
- confidence: Độ tin cậy 0-1 (number)

QUAN TRỌNG:
1. Chỉ trả về JSON object, không có markdown code block
2. Số điện thoại phải bắt đầu bằng 0
3. Giá trị không tìm thấy thì để null
4. property_type phải thuộc 1 trong 4 loại trên
5. Nếu không chắc chắn, để giá trị là null
PROMPT;
    }
}
