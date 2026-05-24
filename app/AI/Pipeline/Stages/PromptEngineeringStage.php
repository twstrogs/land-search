<?php

namespace App\AI\Pipeline\Stages;

use App\AI\Pipeline\PipelineStageInterface;
use App\AI\Exceptions\StageException;
use App\AI\Prompts\PromptRegistry;
use App\Models\PromptVersion;

/**
 * Prompt Engineering Stage
 * 
 * Handles prompt construction and management:
 * - Retrieves active prompt template
 * - Injects few-shot examples
 * - Builds system and user prompts
 * - Handles prompt versioning
 */
class PromptEngineeringStage implements PipelineStageInterface
{
    public function __construct(
        private ?PromptRegistry $registry = null
    ) {
        $this->registry = $registry ?? new PromptRegistry();
    }

    /**
     * Execute prompt engineering
     */
    public function execute(array $data): array
    {
        $preprocessed = $data['preprocessed'] ?? '';

        if (empty(trim($preprocessed))) {
            throw new StageException(
                'PromptEngineeringStage',
                'Preprocessed text is empty',
                StageException::VALIDATION_ERROR
            );
        }

        $taskType = $data['context']['task_type'] ?? 'property_extraction';
        
        // Get active prompt version
        $promptVersion = $this->getActivePrompt($taskType);
        
        // Get few-shot examples
        $examples = $this->getExamples($taskType, $data['context']['example_count'] ?? 3);
        
        // Build system prompt
        $systemPrompt = $this->buildSystemPrompt($promptVersion, $examples);
        
        // Build user prompt
        $userPrompt = $this->buildUserPrompt($preprocessed, $taskType);
        
        // Estimate tokens
        $tokenEstimate = $this->estimateTokens($systemPrompt . $userPrompt);

        return array_merge($data, [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'prompt_version' => $promptVersion['version'] ?? 'default',
            'prompt_version_id' => $promptVersion['id'] ?? null,
            'examples_used' => count($examples),
            'metadata' => array_merge($data['preprocessing_metadata'] ?? [], [
                'example_count' => count($examples),
                'prompt_tokens_estimate' => $tokenEstimate,
                'task_type' => $taskType,
            ]),
        ]);
    }

    /**
     * Get active prompt for task type
     */
    private function getActivePrompt(string $taskType): array
    {
        // Try to get from database
        try {
            $prompt = PromptVersion::where('name', $taskType)
                ->where('is_active', true)
                ->first();

            if ($prompt) {
                return [
                    'id' => $prompt->id,
                    'version' => $prompt->version,
                    'system_prompt' => $prompt->system_prompt,
                    'few_shot_examples' => $prompt->few_shot_examples ?? [],
                    'output_schema' => $prompt->output_schema ?? null,
                ];
            }
        } catch (\Exception $e) {
            // Database not available, use defaults
        }

        // Return default prompt based on task type
        return $this->getDefaultPrompt($taskType);
    }

    /**
     * Get default prompt for task type
     */
    private function getDefaultPrompt(string $taskType): array
    {
        $prompts = [
            'property_extraction' => [
                'version' => 'v1.0-default',
                'system_prompt' => $this->getDefaultPropertyExtractionPrompt(),
                'few_shot_examples' => $this->getDefaultPropertyExamples(),
            ],
            'spam_detection' => [
                'version' => 'v1.0-default',
                'system_prompt' => $this->getDefaultSpamDetectionPrompt(),
                'few_shot_examples' => $this->getDefaultSpamExamples(),
            ],
            'price_analysis' => [
                'version' => 'v1.0-default',
                'system_prompt' => $this->getDefaultPriceAnalysisPrompt(),
                'few_shot_examples' => [],
            ],
            'feature_tagging' => [
                'version' => 'v1.0-default',
                'system_prompt' => $this->getDefaultFeatureTaggingPrompt(),
                'few_shot_examples' => [],
            ],
        ];

        return $prompts[$taskType] ?? $prompts['property_extraction'];
    }

    /**
     * Get few-shot examples
     */
    private function getExamples(string $taskType, int $count = 3): array
    {
        $examples = match ($taskType) {
            'property_extraction' => $this->getDefaultPropertyExamples(),
            'spam_detection' => $this->getDefaultSpamExamples(),
            default => [],
        };

        return array_slice($examples, 0, $count);
    }

    /**
     * Build system prompt with examples
     */
    private function buildSystemPrompt(array $prompt, array $examples): string
    {
        $system = $prompt['system_prompt'] ?? '';

        if (!empty($examples)) {
            $system .= "\n\n## VÍ DỤ MẪU (Few-shot Examples):\n";
            $system .= "Hãy phân tích theo format của các ví dụ sau:\n\n";

            foreach ($examples as $i => $example) {
                $system .= "### Ví dụ " . ($i + 1) . ":\n";
                $system .= "**Input:** " . ($example['input'] ?? '') . "\n";
                $system .= "**Output:** ```json\n" . json_encode($example['output'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n```\n\n";
            }
        }

        return $system;
    }

    /**
     * Build user prompt
     */
    private function buildUserPrompt(string $preprocessedText, string $taskType): string
    {
        $taskInstructions = match ($taskType) {
            'property_extraction' => "Trích xuất thông tin bất động sản từ bài đăng sau:",
            'spam_detection' => "Phân tích bài đăng sau để xác định có phải spam hay không:",
            'price_analysis' => "Phân tích giá bất động sản từ thông tin sau:",
            'feature_tagging' => "Trích xuất các đặc điểm nổi bật từ bài đăng:",
            default => "Xử lý thông tin từ bài đăng sau:",
        };

        return <<<PROMPT
{$taskInstructions}

---
NỘI DUNG CẦN XỬ LÝ:
{$preprocessedText}
---

Trả về kết quả theo định dạng JSON yêu cầu:
PROMPT;
    }

    /**
     * Estimate token count (rough approximation)
     */
    private function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token for Vietnamese
        return (int) ceil(mb_strlen($text) / 4);
    }

    /**
     * Default property extraction prompt
     */
    private function getDefaultPropertyExtractionPrompt(): string
    {
        return <<<'PROMPT'
Bạn là CHUYÊN GIA TRÍCH XUẤT THÔNG TIN BẤT ĐỘNG SẢN VIỆT NAM.

## NHIỆM VỤ
Trích xuất thông tin từ bài đăng BĐS và trả về DUY NHẤT một JSON object hợp lệ.

## CÁC TRƯỜNG CẦN TRÍCH XUẤT

### Thông tin cơ bản:
- `title`: Tiêu đề ngắn gọn, hấp dẫn (50-100 ký tự)
- `description`: Mô tả chi tiết từ bài đăng
- `property_type`: Loại BĐS - CHỈ một trong 4 giá trị:
  * "Căn hộ chung cư"
  * "Nhà phố/Nhà riêng"  
  * "Biệt thự (Villa)"
  * "Đất nền/Đất thổ cư"

### Thông tin tài chính:
- `price_text`: Giá dạng text, ví dụ "1.5 tỷ", "800 triệu"
- `price_value`: Giá dạng số (VND)
- `price_per_m2`: Giá trên m² nếu có thể tính

### Thông tin vị trí:
- `address_text`: Địa chỉ đầy đủ
- `ward`: Phường/Xã
- `district`: Quận/Huyện
- `city`: Tỉnh/Thành phố

### Thông tin vật lý:
- `area_text`: Diện tích dạng text
- `area_value`: Diện tích (m²)
- `frontage_texts`: Mảng kích thước mặt tiền
- `frontage_count`: Số mặt tiền (1, 2, 3...)
- `depth_text`: Chiều sâu

### Thông tin bổ sung:
- `direction`: Hướng nhà (đông, tây, nam, bắc, đông nam,...)
- `phones`: Mảng số điện thoại VN hợp lệ
- `features`: Mảng đặc điểm nổi bật
- `legal_status`: Tình trạng pháp lý (sổ đỏ, sổ hồng,...)

## QUY TẮC QUAN TRỌNG

### 1. Quy tắc đơn vị tiền tệ:
- "tr", "triệu", "củ" → TRIỆU (×10⁶)
- "tỷ", "ty", "tỉ" → TỶ (×10⁹)
- "t" đứng một mình:
  * Số 1-2 chữ số (6t, 12t) → TỶ
  * Số ≥3 chữ số (600t, 900t) → TRIỆU

### 2. Quy tắc số điện thoại:
- Phải bắt đầu bằng số 0
- 10-11 chữ số
- Loại bỏ các số giả (999..., 000...)

### 3. Quy tắc diện tích:
- Chuyển đổi: 1 sào = 360m², 1 hecta = 10000m²
- Ưu tiên m² làm đơn vị chuẩn

### 4. Confidence scoring:
Đánh giá độ tin cậy 0-1 dựa trên:
- Độ đầy đủ của thông tin
- Tính nhất quán của dữ liệu
- Chất lượng bài đăng gốc

## OUTPUT FORMAT
```json
{
  "title": "string|null",
  "description": "string|null",
  "property_type": "string|null",
  "price_text": "string|null",
  "price_value": number|null,
  "area_text": "string|null",
  "area_value": number|null,
  "frontage_texts": ["string"],
  "frontage_count": number,
  "depth_text": "string|null",
  "direction": "string|null",
  "address_text": "string|null",
  "ward": "string|null",
  "district": "string|null",
  "city": "string|null",
  "phones": ["string"],
  "features": ["string"],
  "legal_status": "string|null",
  "confidence": number (0-1)
}
```

## LƯU Ý
1. Chỉ trả về JSON, không có markdown, không có giải thích
2. Nếu không tìm thấy thông tin, để giá trị là null
3. Không suy đoán thông tin không có trong bài đăng
4. Giữ nguyên tiếng Việt có dấu
PROMPT;
    }

    /**
     * Default spam detection prompt
     */
    private function getDefaultSpamDetectionPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia phát hiện spam trong các bài đăng bất động sản Việt Nam.

## NHIỆM VỤ
Phân tích bài đăng và trả về đánh giá spam.

## CÁC DẤU HIỆU SPAM
- Giá quá rẻ so với thị trường
- Sử dụng từ khóa "shock", "sale", "free"
- Số điện thoại nhiều và lạ
- Yêu cầu "gọi ngay", "chỉ còn"
- Nội dung mơ hồ, thiếu thông tin cụ thể
- Link không phải Facebook

## OUTPUT FORMAT
```json
{
  "is_spam": boolean,
  "spam_score": number (0-1),
  "reasons": ["string"],
  "flags": ["string"]
}
```
PROMPT;
    }

    /**
     * Default price analysis prompt
     */
    private function getDefaultPriceAnalysisPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia phân tích giá bất động sản Việt Nam.

## NHIỆM VỤ
Phân tích và chuẩn hóa giá từ bài đăng.

## QUY TẮC
- "tr", "triệu", "củ" → TRIỆU (×10⁶)
- "tỷ", "ty", "tỉ" → TỶ (×10⁹)
- "t" đứng một mình: 1-2 chữ số → TỶ, ≥3 chữ số → TRIỆU

## OUTPUT FORMAT
```json
{
  "price_text": "string",
  "price_value": number (VND),
  "price_per_m2": number|null,
  "is_reasonable": boolean,
  "market_comparison": "string"
}
```
PROMPT;
    }

    /**
     * Default feature tagging prompt
     */
    private function getDefaultFeatureTaggingPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia trích xuất đặc điểm bất động sản Việt Nam.

## NHIỆM VỤ
Trích xuất các đặc điểm nổi bật từ bài đăng.

## CÁC NHÓM ĐẶC ĐIỂM
- legal: Sổ đỏ, sổ hồng, pháp lý
- location: Gần trường, chợ, bệnh viện
- infrastructure: Đường nhựa, điện nước
- land: Vuông vắn, nở hậu, không ngập
- house: Nội thất, garage, ban công
- investment: Tiềm năng, cho thuê
- environment: Yên tĩnh, an ninh

## OUTPUT FORMAT
```json
{
  "features": ["string"],
  "feature_groups": {
    "legal": ["string"],
    "location": ["string"],
    "infrastructure": ["string"],
    "land": ["string"],
    "house": ["string"],
    "investment": ["string"],
    "environment": ["string"]
  }
}
```
PROMPT;
    }

    /**
     * Default property examples
     */
    private function getDefaultPropertyExamples(): array
    {
        return [
            [
                'input' => 'Bán nhà 3 tầng mặt đường Ngô Gia Tự, phường Tân Thịnh, TP Thái Nguyên. Diện tích 120m2, 4 phòng ngủ, 3 toilet. Nhà mới xây, nội thất đẹp. Giá 2.5 tỷ. LH: 0981234567',
                'output' => [
                    'title' => 'Bán nhà 3 tầng mặt đường Ngô Gia Tự, TP Thái Nguyên',
                    'description' => 'Bán nhà 3 tầng mặt đường Ngô Gia Tự, phường Tân Thịnh, TP Thái Nguyên. Diện tích 120m2, 4 phòng ngủ, 3 toilet. Nhà mới xây, nội thất đẹp.',
                    'property_type' => 'Nhà phố/Nhà riêng',
                    'price_text' => '2.5 tỷ',
                    'price_value' => 2500000000,
                    'area_text' => '120m2',
                    'area_value' => 120,
                    'frontage_texts' => [],
                    'frontage_count' => 0,
                    'depth_text' => null,
                    'direction' => null,
                    'address_text' => 'Đường Ngô Gia Tự, Phường Tân Thịnh, TP Thái Nguyên',
                    'ward' => 'Tân Thịnh',
                    'district' => 'Thái Nguyên',
                    'city' => 'Thái Nguyên',
                    'phones' => ['0981234567'],
                    'features' => ['Nhà mới xây', 'Nội thất đẹp', '4 phòng ngủ', '3 toilet'],
                    'legal_status' => null,
                    'confidence' => 0.95,
                ],
            ],
            [
                'input' => 'Đất nền p. Cam Giá, TP Thái Nguyên. DT 200m2, giá 900tr. Đất vuông vắn, hướng đông nam, full thổ cư. LH Ms Hà 0979876543',
                'output' => [
                    'title' => 'Đất nền phường Cam Giá, TP Thái Nguyên',
                    'description' => 'Đất nền p. Cam Giá, TP Thái Nguyên. DT 200m2, giá 900tr. Đất vuông vắn, hướng đông nam, full thổ cư.',
                    'property_type' => 'Đất nền/Đất thổ cư',
                    'price_text' => '900 triệu',
                    'price_value' => 900000000,
                    'area_text' => '200m2',
                    'area_value' => 200,
                    'frontage_texts' => [],
                    'frontage_count' => 0,
                    'depth_text' => null,
                    'direction' => 'đông nam',
                    'address_text' => 'Phường Cam Giá, TP Thái Nguyên',
                    'ward' => 'Cam Giá',
                    'district' => 'Thái Nguyên',
                    'city' => 'Thái Nguyên',
                    'phones' => ['0979876543'],
                    'features' => ['Vuông vắn', 'Hướng đông nam', 'Full thổ cư'],
                    'legal_status' => 'Full thổ cư',
                    'confidence' => 0.92,
                ],
            ],
        ];
    }

    /**
     * Default spam examples
     */
    private function getDefaultSpamExamples(): array
    {
        return [
            [
                'input' => 'BÁN NHÀ GIÁ SHOCK 500TR!!! Gọi ngay 0999999999 để được giá tốt nhất! Chỉ còn hôm nay!',
                'output' => [
                    'is_spam' => true,
                    'spam_score' => 0.85,
                    'reasons' => ['Giá shock', 'Yêu cầu gọi ngay', 'Số điện thoại lạ'],
                    'flags' => ['price_shock', 'urgency', 'suspicious_phone'],
                ],
            ],
            [
                'input' => 'Bán căn hộ chung cư 3PN, 2WC, diện tích 85m2 tại Quang Trung, Thái Nguyên. Giá 1.8 tỷ. Sổ đỏ chính chủ. LH: 0912345678',
                'output' => [
                    'is_spam' => false,
                    'spam_score' => 0.1,
                    'reasons' => [],
                    'flags' => [],
                ],
            ],
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
        return 'prompt';
    }

    /**
     * Get stage description
     */
    public function getDescription(): string
    {
        return 'Prompt Engineering - Template selection, few-shot examples, prompt construction';
    }

    /**
     * Get required input data keys
     */
    public function getRequiredInputs(): array
    {
        return ['preprocessed'];
    }

    /**
     * Get output data keys
     */
    public function getOutputs(): array
    {
        return ['system_prompt', 'user_prompt', 'prompt_version', 'examples_used'];
    }

    /**
     * Validate input data
     */
    public function validateInput(array $data): void
    {
        if (!isset($data['preprocessed'])) {
            throw new StageException(
                'PromptEngineeringStage',
                'Missing required input: preprocessed',
                StageException::VALIDATION_ERROR
            );
        }
    }
}
