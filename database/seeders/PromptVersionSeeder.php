<?php

namespace Database\Seeders;

use App\Models\PromptVersion;
use Illuminate\Database\Seeder;

class PromptVersionSeeder extends Seeder
{
    /**
     * Seed the prompt versions table with default extraction prompts
     */
    public function run(): void
    {
        // Only seed if no prompts exist
        if (PromptVersion::count() > 0) {
            return;
        }

        $prompts = [
            [
                'name' => 'real_estate_extraction',
                'version' => 'v1.0',
                'system_prompt' => $this->getRealEstateExtractionPrompt(),
                'few_shot_examples' => $this->getRealEstateExamples(),
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string'],
                        'description' => ['type' => 'string'],
                        'price_text' => ['type' => 'string'],
                        'price_value' => ['type' => 'number'],
                        'area_text' => ['type' => 'string'],
                        'area_value' => ['type' => 'number'],
                        'address_text' => ['type' => 'string'],
                        'city' => ['type' => 'string'],
                        'district' => ['type' => 'string'],
                        'ward' => ['type' => 'string'],
                        'phones' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'property_type' => ['type' => 'string', 'enum' => ['nhà', 'đất', 'căn hộ', 'đất nền', 'shophouse', 'khác']],
                        'direction' => ['type' => 'string'],
                        'features' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'distance_to_road' => ['type' => 'string'],
                        'juridical' => ['type' => 'string'],
                        'confidence' => ['type' => 'number'],
                    ],
                    'required' => ['price_value', 'area_value'],
                ],
                'is_active' => true,
                'description' => 'Prompt mặc định trích xuất thông tin bất động sản từ tiếng Việt',
            ],
            [
                'name' => 'real_estate_extraction',
                'version' => 'v1.1',
                'system_prompt' => $this->getRealEstateExtractionPromptV2(),
                'few_shot_examples' => $this->getRealEstateExamples(),
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string'],
                        'description' => ['type' => 'string'],
                        'price_text' => ['type' => 'string'],
                        'price_value' => ['type' => 'number'],
                        'area_text' => ['type' => 'string'],
                        'area_value' => ['type' => 'number'],
                        'address_text' => ['type' => 'string'],
                        'city' => ['type' => 'string'],
                        'district' => ['type' => 'string'],
                        'ward' => ['type' => 'string'],
                        'phones' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'property_type' => ['type' => 'string'],
                        'direction' => ['type' => 'string'],
                        'features' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'distance_to_road' => ['type' => 'string'],
                        'juridical' => ['type' => 'string'],
                        'confidence' => ['type' => 'number'],
                    ],
                    'required' => ['price_value', 'area_value'],
                ],
                'is_active' => false,
                'description' => 'Phiên bản cải tiến với khả năng trích xuất địa chỉ tốt hơn',
            ],
            [
                'name' => 'phone_extraction',
                'version' => 'v1.0',
                'system_prompt' => 'Trích xuất tất cả số điện thoại từ văn bản. Trả về JSON array các số điện thoại hợp lệ.',
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'phones' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ],
                    'required' => ['phones'],
                ],
                'is_active' => true,
                'description' => 'Trích xuất số điện thoại',
            ],
        ];

        foreach ($prompts as $prompt) {
            PromptVersion::create($prompt);
        }

        $this->command->info('Seeded ' . count($prompts) . ' prompt versions.');
    }

    private function getRealEstateExtractionPrompt(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia trích xuất thông tin bất động sản từ tiếng Việt.
Nhiệm vụ: Đọc văn bản bài đăng và trích xuất thông tin theo JSON schema.

QUY TẮC QUAN TRỌNG:
1. Giá: Trích xuất giá trị số (VNĐ). Đơn vị: "tỷ" = 1,000,000,000, "tr" = 1,000,000
2. Diện tích: Trích xuất số (m²)
3. Số điện thoại: Tìm tất cả số điện thoại Việt Nam (10-11 số, bắt đầu 0)
4. Địa chỉ: Tách thành city, district, ward nếu có
5. Hướng nhà: Bắc, Nam, Đông, Tây, Đông Bắc, Tây Bắc, Đông Nam, Tây Nam
6. Nếu không tìm thấy giá trị, trả về null

Đặc điểm tiếng Việt cần xử lý:
- "nhỉnh X tr/m" = X triệu/m²
- "một tỷ" = 1,000,000,000
- "vài trăm triệu" = ước tính 500,000,000
- "sổ đỏ", "sổ hồng", "chính chủ" = juridical
- "đường nhựa", "đường bê tông" = road type
PROMPT;
    }

    private function getRealEstateExtractionPromptV2(): string
    {
        return <<<'PROMPT'
Bạn là chuyên gia trích xuất thông tin bất động sản từ tiếng Việt.
Nhiệm vụ: Đọc văn bản bài đăng và trích xuất thông tin theo JSON schema.

QUY TẮC QUAN TRỌNG:
1. Giá: Trích xuất giá trị số (VNĐ). Đơn vị: "tỷ" = 1,000,000,000, "tr" = 1,000,000
2. Diện tích: Trích xuất số (m²), chú ý "cây số" khác "mét"
3. Số điện thoại: Tìm tất cả số điện thoại Việt Nam (10-11 số, bắt đầu 0)
4. Địa chỉ: Tách thành city, district, ward. Ưu tiên tỉnh/thành phố
5. Hướng nhà: Bắc, Nam, Đông, Tây, Đông Bắc, Tây Bắc, Đông Nam, Tây Nam
6. Property types: nhà, đất, căn hộ, đất nền, shophouse, đất thổ cư, nhà phố
7. Nếu không tìm thấy giá trị, trả về null

Đặc điểm tiếng Việt cần xử lý:
- "nhỉnh X tr/m" = X triệu/m²
- "một tỷ hai" = 1,200,000,000
- "vài trăm triệu" = ước tính 500,000,000
- "sổ đỏ", "sổ hồng", "chính chủ" = juridical
- "ODT", "đường quy hoạch" = đường ô tô
- "KDC" = khu dân cư
- "thổ cư" = đất thổ cư
PROMPT;
    }

    private function getRealEstateExamples(): array
    {
        return [
            [
                'input' => 'Bán đất 200m2, đường nhựa, giá 3 tỷ. LH: 0901234567',
                'output' => [
                    'area_value' => 200,
                    'price_value' => 3000000000,
                    'phones' => ['0901234567'],
                    'property_type' => 'đất',
                    'distance_to_road' => 'đường nhựa',
                ],
            ],
            [
                'input' => 'Nhà 3 tầng, 80m2, hướng Đông Nam, giá 4.5 tỷ. Địa chỉ: 123 Nguyễn Trãi, Quận 1, HCM. Tel: 0912345678',
                'output' => [
                    'area_value' => 80,
                    'price_value' => 4500000000,
                    'phones' => ['0912345678'],
                    'property_type' => 'nhà',
                    'direction' => 'Đông Nam',
                    'city' => 'Hồ Chí Minh',
                    'district' => 'Quận 1',
                    'address_text' => '123 Nguyễn Trãi, Quận 1, HCM',
                ],
            ],
        ];
    }
}
