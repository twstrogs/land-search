<?php

namespace App\Services;

use Illuminate\Support\Str;

class NormalizeService
{
    private array $propertyTypes = [
        'Căn hộ chung cư',
        'Nhà phố/Nhà riêng',
        'Biệt thự (Villa)',
        'Đất nền/Đất thổ cư',
    ];

    public function normalize(array $extracted): array
    {
        return [
            'title' => $this->normalizeTitle($extracted['title'] ?? null),
            'description' => $this->normalizeText($extracted['description'] ?? null),
            'price_text' => $this->normalizeText($extracted['price_text'] ?? null),
            'price_value' => $this->normalizePrice($extracted['price_text'] ?? null),
            'area_text' => $this->normalizeText($extracted['area_text'] ?? null),
            'area_value' => $this->normalizeArea($extracted['area_text'] ?? null),
            'frontage_texts' => $this->normalizeFrontageTexts($extracted['frontage_texts'] ?? []),
            'frontage_count' => $extracted['frontage_count'] ?? 0,
            'depth_text' => $this->normalizeText($extracted['depth_text'] ?? null),
            'address_text' => $this->normalizeText($extracted['address_text'] ?? null),
            'ward' => $this->normalizeText($extracted['ward'] ?? null),
            'district' => $this->normalizeText($extracted['district'] ?? null),
            'city' => $this->normalizeText($extracted['city'] ?? null) ?: 'Thái Nguyên',
            'property_type' => $this->normalizePropertyType($extracted['property_type'] ?? null),
            'phones' => $this->normalizePhones($extracted['phones'] ?? []),
            'features' => $this->normalizeFeatures($extracted['features'] ?? []),
            'facebook_url' => $this->normalizeUrl($extracted['facebook_url'] ?? null),
            'confidence' => $this->normalizeConfidence($extracted['confidence'] ?? null),
        ];
    }

    private function normalizeTitle(?string $title): ?string
    {
        if (!$title) {
            return null;
        }
        $title = trim($title);
        return empty($title) ? null : $title;
    }

    private function normalizeText(?string $text): ?string
    {
        if (!$text) {
            return null;
        }
        $text = trim($text);
        return empty($text) ? null : $text;
    }

    public function normalizePrice(?string $priceText): ?float
    {
        if (!$priceText) {
            return null;
        }

        $priceText = trim(mb_strtolower($priceText));
        $priceText = preg_replace('/\s+/', ' ', $priceText);

        $numberPattern = '/[\d,\.]+(?:\.\d+)?/';
        
        if (preg_match('/(\d[\d,\.]*)\s*(tỷ|ty|tỉ)/i', $priceText, $matches)) {
            $number = (float) str_replace(',', '.', $matches[1]);
            return $number * 1_000_000_000;
        }

        if (preg_match('/(\d[\d,\.]*)\s*(tr|triệu|củ)/i', $priceText, $matches)) {
            $number = (float) str_replace(',', '.', $matches[1]);
            return $number * 1_000_000;
        }

        if (preg_match('/(\d+)t\b/', $priceText, $matches)) {
            $number = (int) $matches[1];
            if ($number < 100) {
                return $number * 1_000_000_000;
            }
            return $number * 1_000_000;
        }

        if (preg_match($numberPattern, $priceText, $matches)) {
            $number = (float) str_replace(',', '.', $matches[0]);
            
            if (stripos($priceText, 'tỷ') !== false || stripos($priceText, 'ty') !== false) {
                return $number * 1_000_000_000;
            }
            if (stripos($priceText, 'tr') !== false || stripos($priceText, 'triệu') !== false || stripos($priceText, 'củ') !== false) {
                return $number * 1_000_000;
            }
            
            return $number;
        }

        return null;
    }

    public function normalizeArea(?string $areaText): ?float
    {
        if (!$areaText) {
            return null;
        }

        $patterns = [
            '/(\d+[\d,\.]*)\s*m2/i',
            '/(\d+[\d,\.]*)\s*m(?!\w)/i',
            '/(\d+[\d,\.]*)\s* mét/i',
            '/diện tích\s*[:\-]?\s*(\d+[\d,\.]*)/i',
        ];

        $areas = [];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $areaText, $matches)) {
                foreach ($matches[1] as $match) {
                    $area = (float) str_replace(',', '.', $match);
                    if ($area > 0 && $area < 100000) {
                        $areas[] = $area;
                    }
                }
            }
        }

        if (!empty($areas)) {
            return max($areas);
        }

        return null;
    }

    private function normalizeFrontageTexts(array $frontageTexts): array
    {
        return array_map(function ($text) {
            return $this->normalizeText($text);
        }, array_filter($frontageTexts));
    }

    private function normalizePropertyType(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        $type = trim($type);
        
        foreach ($this->propertyTypes as $allowed) {
            if (stripos($type, explode(' ', $allowed)[0]) !== false) {
                return $allowed;
            }
        }

        $typeLower = mb_strtolower($type);
        
        $mapping = [
            'căn hộ' => 'Căn hộ chung cư',
            'chung cư' => 'Căn hộ chung cư',
            'nhà phố' => 'Nhà phố/Nhà riêng',
            'nhà riêng' => 'Nhà phố/Nhà riêng',
            'nhà mặt' => 'Nhà phố/Nhà riêng',
            'biệt thự' => 'Biệt thự (Villa)',
            'villa' => 'Biệt thự (Villa)',
            'đất nền' => 'Đất nền/Đất thổ cư',
            'đất thổ' => 'Đất nền/Đất thổ cư',
            'thổ cư' => 'Đất nền/Đất thổ cư',
            'đất' => 'Đất nền/Đất thổ cư',
        ];

        foreach ($mapping as $key => $value) {
            if (strpos($typeLower, $key) !== false) {
                return $value;
            }
        }

        return null;
    }

    public function normalizePhones(array $phones): array
    {
        $normalized = [];
        
        foreach ($phones as $phone) {
            $phone = preg_replace('/[^\d]/', '', (string) $phone);
            
            if (strlen($phone) >= 9 && strlen($phone) <= 12) {
                if (strpos($phone, '0') === 0) {
                    $normalized[] = $phone;
                } elseif (strlen($phone) == 9 || strlen($phone) == 10) {
                    $normalized[] = '0' . $phone;
                }
            }
        }
        
        return array_unique($normalized);
    }

    private function normalizeFeatures(array $features): array
    {
        // Return raw features for now - will be normalized and synced to DB by FeatureExtractionService
        return array_values(array_unique(array_filter(array_map(function ($feature) {
            $feature = trim($feature);
            if (strlen($feature) > 2 && strlen($feature) < 100) {
                return $feature;
            }
            return null;
        }, $features))));
    }

    private function normalizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        
        if (preg_match('/(https?:\/\/[^\s]+facebook\.com[^\s]*)/i', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function normalizeConfidence($confidence): ?float
    {
        if ($confidence === null) {
            return null;
        }
        
        $value = (float) $confidence;
        
        if ($value < 0) {
            return 0;
        }
        if ($value > 1) {
            return 1;
        }
        
        return round($value, 4);
    }

    public function generateHash(array $data, ?string $rawContent = null): string
    {
        // Nếu có raw_content, hash từ raw_content để tránh trùng lặp
        if ($rawContent) {
            return hash('sha256', mb_strtolower(trim($rawContent)));
        }
        
        // Fallback: hash từ các trường đã trích xuất
        $hashContent = implode('|', [
            $data['title'] ?? '',
            $data['price_text'] ?? '',
            $data['area_text'] ?? '',
            $data['address_text'] ?? '',
            implode(',', $data['phones'] ?? []),
        ]);
        
        return hash('sha256', mb_strtolower(trim($hashContent)));
    }
    
    public function generateRawContentHash(string $rawContent): string
    {
        return hash('sha256', mb_strtolower(trim($rawContent)));
    }
}
