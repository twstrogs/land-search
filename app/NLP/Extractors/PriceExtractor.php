<?php

namespace App\NLP\Extractors;

/**
 * Price Extractor
 * 
 * Extracts and normalizes price information from Vietnamese real estate text
 */
class PriceExtractor
{
    private array $patterns = [];

    public function __construct()
    {
        $this->initializePatterns();
    }

    /**
     * Initialize extraction patterns
     */
    private function initializePatterns(): void
    {
        $this->patterns = [
            // Tỷ patterns
            'ty_full' => '/(\d+(?:[.,]\d+)?)\s*(?:tỷ|ty|tỉ)/iu',
            'ty_abbreviated' => '/(\d{1,2})t\b/',
            
            // Triệu patterns
            'trieu_full' => '/(\d+(?:[.,]\d+)?)\s*(?:triệu|tr)/iu',
            'trieu_cu' => '/(\d+(?:[.,]\d+)?)\s*củ/iu',
            
            // Price per m2
            'per_m2' => '/(\d+(?:[.,]\d+)?)\s*(?:triệu|tr)\s*\/\s*m2/iu',
            
            // X patterns (6xx, 8xx)
            'x_pattern' => '/(\d)xx\s*(?:tr|triệu)/iu',
        ];
    }

    /**
     * Extract price from text
     */
    public function extract(string $text): array
    {
        $prices = [];
        $textLower = mb_strtolower($text);

        // Try each pattern
        foreach ($this->patterns as $name => $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[0] as $i => $match) {
                    $prices[] = [
                        'raw' => $match,
                        'normalized' => $this->parsePrice($match),
                        'type' => $this->getPriceType($match),
                        'pattern' => $name,
                    ];
                }
            }
        }

        // Get the primary (highest) price
        $primary = $this->getPrimaryPrice($prices);

        return [
            'prices' => $prices,
            'primary' => $primary,
            'price_text' => $primary['raw'] ?? null,
            'price_value' => $primary['normalized'] ?? null,
        ];
    }

    /**
     * Parse price value
     */
    private function parsePrice(string $text): ?float
    {
        $text = mb_strtolower(trim($text));

        // Check for tỷ
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:tỷ|ty|tỉ)/iu', $text, $m)) {
            $number = (float) str_replace(',', '.', $m[1]);
            return $number * 1_000_000_000;
        }

        // Check for triệu/củ/tr
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:triệu|tr|củ)/iu', $text, $m)) {
            $number = (float) str_replace(',', '.', $m[1]);
            return $number * 1_000_000;
        }

        // Check for "X" pattern (e.g., 6xx tr)
        if (preg_match('/(\d)xx\s*(?:tr|triệu)/iu', $text, $m)) {
            $number = (int) $m[1] * 100;
            return $number * 1_000_000;
        }

        // Check for single digit + t (e.g., 6t)
        if (preg_match('/(\d{1,2})t\b/iu', $text, $m)) {
            $number = (int) $m[1];
            // 1-2 digits before "t" means tỷ
            return $number * 1_000_000_000;
        }

        // Check for number followed by large number (e.g., 600t)
        if (preg_match('/(\d{3,})t\b/iu', $text, $m)) {
            $number = (int) $m[1];
            // 3+ digits before "t" means triệu
            return $number * 1_000_000;
        }

        return null;
    }

    /**
     * Get price type
     */
    private function getPriceType(string $text): string
    {
        $text = mb_strtolower($text);

        if (strpos($text, 'tỷ') !== false || strpos($text, 'ty') !== false || strpos($text, 'tỉ') !== false) {
            return 'tỷ';
        }

        if (strpos($text, 'triệu') !== false || strpos($text, 'tr') !== false || strpos($text, 'củ') !== false) {
            return 'triệu';
        }

        if (preg_match('/\d+t\b/', $text)) {
            return 'auto_detect';
        }

        return 'unknown';
    }

    /**
     * Get primary price (highest value)
     */
    private function getPrimaryPrice(array $prices): array
    {
        if (empty($prices)) {
            return [];
        }

        $primary = $prices[0];
        foreach ($prices as $price) {
            if (($price['normalized'] ?? 0) > ($primary['normalized'] ?? 0)) {
                $primary = $price;
            }
        }

        return $primary;
    }

    /**
     * Validate price reasonableness
     */
    public function validate(float $price, string $propertyType = null): array
    {
        $warnings = [];

        // Typical ranges by property type
        $ranges = [
            'Căn hộ chung cư' => ['min' => 500_000_000, 'max' => 10_000_000_000],
            'Nhà phố/Nhà riêng' => ['min' => 500_000_000, 'max' => 50_000_000_000],
            'Biệt thự (Villa)' => ['min' => 5_000_000_000, 'max' => 100_000_000_000],
            'Đất nền/Đất thổ cư' => ['min' => 50_000_000, 'max' => 50_000_000_000],
        ];

        $range = $ranges[$propertyType] ?? ['min' => 10_000_000, 'max' => 500_000_000_000];

        if ($price < $range['min']) {
            $warnings[] = "Price seems too low for the property type";
        }

        if ($price > $range['max']) {
            $warnings[] = "Price seems unusually high";
        }

        return [
            'is_valid' => empty($warnings),
            'warnings' => $warnings,
        ];
    }
}
