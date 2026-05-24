<?php

namespace App\NLP\Extractors;

/**
 * Area Extractor
 * 
 * Extracts and normalizes area information from Vietnamese real estate text
 */
class AreaExtractor
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
            // Standard m2
            'm2_standard' => '/(\d+(?:[.,]\d+)?)\s*m2/iu',
            
            // m only
            'm_only' => '/(\d+(?:[.,]\d+)?)\s*m(?!\w)/iu',
            
            // Diện tích
            'dt_prefix' => '/diện\s*tích\s*:?\s*(\d+(?:[.,]\d+)?)/iu',
            
            // Sào (1 sào = 360m2)
            'sao' => '/(\d+(?:[.,]\d+)?)\s*sào/iu',
            
            // Hecta (1 hecta = 10000m2)
            'hecta' => '/(\d+(?:[.,]\d+)?)\s*ha/iu',
            
            // Multiple dimensions (width x depth)
            'dimensions' => '/(\d+(?:[.,]\d+)?)\s*m\s*[xX×]\s*(\d+(?:[.,]\d+)?)/iu',
        ];
    }

    /**
     * Extract area from text
     */
    public function extract(string $text): array
    {
        $areas = [];
        $textLower = mb_strtolower($text);

        // Standard m2
        if (preg_match_all($this->patterns['m2_standard'], $text, $matches)) {
            foreach ($matches[1] as $i => $value) {
                $areas[] = [
                    'raw' => $matches[0][$i],
                    'value' => (float) str_replace(',', '.', $value),
                    'unit' => 'm2',
                    'pattern' => 'm2_standard',
                ];
            }
        }

        // Sào
        if (preg_match_all($this->patterns['sao'], $text, $matches)) {
            foreach ($matches[1] as $i => $value) {
                $sao = (float) str_replace(',', '.', $value);
                $areas[] = [
                    'raw' => $matches[0][$i],
                    'value' => $sao * 360, // Convert to m2
                    'unit' => 'm2',
                    'original_unit' => 'sào',
                    'original_value' => $sao,
                    'pattern' => 'sao',
                ];
            }
        }

        // Hecta
        if (preg_match_all($this->patterns['hecta'], $text, $matches)) {
            foreach ($matches[1] as $i => $value) {
                $hecta = (float) str_replace(',', '.', $value);
                $areas[] = [
                    'raw' => $matches[0][$i],
                    'value' => $hecta * 10000, // Convert to m2
                    'unit' => 'm2',
                    'original_unit' => 'hecta',
                    'original_value' => $hecta,
                    'pattern' => 'hecta',
                ];
            }
        }

        // Diện tích prefix
        if (preg_match_all($this->patterns['dt_prefix'], $text, $matches)) {
            foreach ($matches[1] as $i => $value) {
                $areas[] = [
                    'raw' => $matches[0][$i],
                    'value' => (float) str_replace(',', '.', $value),
                    'unit' => 'm2',
                    'pattern' => 'dt_prefix',
                ];
            }
        }

        // Dimensions (width x depth)
        if (preg_match_all($this->patterns['dimensions'], $text, $matches)) {
            foreach ($matches[1] as $i => $width) {
                $depth = $matches[2][$i];
                $width = (float) str_replace(',', '.', $width);
                $depth = (float) str_replace(',', '.', $depth);
                $areas[] = [
                    'raw' => $matches[0][$i],
                    'value' => $width * $depth,
                    'unit' => 'm2',
                    'width' => $width,
                    'depth' => $depth,
                    'pattern' => 'dimensions',
                ];
            }
        }

        // Get primary area (largest reasonable value)
        $primary = $this->getPrimaryArea($areas);

        return [
            'areas' => $areas,
            'primary' => $primary,
            'area_text' => $primary['raw'] ?? null,
            'area_value' => $primary['value'] ?? null,
        ];
    }

    /**
     * Get primary area (largest reasonable value)
     */
    private function getPrimaryArea(array $areas): array
    {
        if (empty($areas)) {
            return [];
        }

        // Filter reasonable values (10m2 to 100000m2)
        $reasonable = array_filter($areas, function ($area) {
            return $area['value'] >= 10 && $area['value'] <= 100000;
        });

        if (empty($reasonable)) {
            // Use first area if no reasonable one found
            return $areas[0];
        }

        // Return largest
        usort($reasonable, fn($a, $b) => $b['value'] <=> $a['value']);
        return $reasonable[0];
    }

    /**
     * Validate area reasonableness
     */
    public function validate(float $area, string $propertyType = null): array
    {
        $warnings = [];

        // Typical ranges by property type
        $ranges = [
            'Căn hộ chung cư' => ['min' => 30, 'max' => 300],
            'Nhà phố/Nhà riêng' => ['min' => 40, 'max' => 500],
            'Biệt thự (Villa)' => ['min' => 200, 'max' => 2000],
            'Đất nền/Đất thổ cư' => ['min' => 50, 'max' => 10000],
        ];

        $range = $ranges[$propertyType] ?? ['min' => 10, 'max' => 100000];

        if ($area < $range['min']) {
            $warnings[] = "Area seems too small for the property type";
        }

        if ($area > $range['max']) {
            $warnings[] = "Area seems unusually large";
        }

        return [
            'is_valid' => empty($warnings),
            'warnings' => $warnings,
        ];
    }
}
