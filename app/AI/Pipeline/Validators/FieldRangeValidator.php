<?php

namespace App\AI\Pipeline\Validators;

/**
 * Field Range Validator
 * 
 * Validates field values against reasonable ranges
 */
class FieldRangeValidator
{
    private array $ranges = [];

    public function __construct()
    {
        $this->ranges = $this->getValidationRanges();
    }

    /**
     * Get validation ranges for real estate data
     */
    private function getValidationRanges(): array
    {
        return [
            'price_value' => [
                'min' => 10_000_000,           // 10 triệu
                'max' => 500_000_000_000,      // 500 tỷ
                'typical_min' => 100_000_000,   // 100 triệu
                'typical_max' => 50_000_000_000, // 50 tỷ
                'unit' => 'VND',
            ],
            'area_value' => [
                'min' => 10,                    // 10 m2
                'max' => 100000,                // 10 hecta
                'typical_min' => 30,             // 30 m2
                'typical_max' => 5000,           // 5000 m2
                'unit' => 'm²',
            ],
            'frontage_count' => [
                'min' => 0,
                'max' => 5,
                'typical_max' => 3,
            ],
            'price_per_m2' => [
                'min' => 500_000,                // 500k/m2
                'max' => 500_000_000,            // 500 triệu/m2
                'typical_min' => 1_000_000,       // 1 triệu/m2
                'typical_max' => 100_000_000,     // 100 triệu/m2
                'unit' => 'VND/m²',
            ],
        ];
    }

    /**
     * Validate all fields against ranges
     */
    public function validate(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Validate price
        if (isset($data['price_value'])) {
            $result = $this->validateRange('price_value', $data['price_value']);
            $errors = array_merge($errors, $result['errors']);
            $warnings = array_merge($warnings, $result['warnings']);
        }

        // Validate area
        if (isset($data['area_value'])) {
            $result = $this->validateRange('area_value', $data['area_value']);
            $errors = array_merge($errors, $result['errors']);
            $warnings = array_merge($warnings, $result['warnings']);
        }

        // Validate frontage count
        if (isset($data['frontage_count'])) {
            $result = $this->validateRange('frontage_count', $data['frontage_count']);
            $errors = array_merge($errors, $result['errors']);
            $warnings = array_merge($warnings, $result['warnings']);
        }

        // Validate price per m2
        if (isset($data['price_value']) && isset($data['area_value']) && $data['area_value'] > 0) {
            $pricePerM2 = $data['price_value'] / $data['area_value'];
            $result = $this->validateRange('price_per_m2', $pricePerM2);
            $errors = array_merge($errors, $result['errors']);
            $warnings = array_merge($warnings, $result['warnings']);
        }

        // Validate phone count
        if (isset($data['phones']) && is_array($data['phones'])) {
            if (count($data['phones']) > 5) {
                $warnings[] = 'Unusually high number of phone numbers: ' . count($data['phones']);
            }
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate a single range
     */
    private function validateRange(string $fieldName, $value): array
    {
        $errors = [];
        $warnings = [];
        $range = $this->ranges[$fieldName] ?? null;

        if (!$range || $value === null) {
            return ['errors' => [], 'warnings' => []];
        }

        // Hard limits (errors)
        if (isset($range['min']) && $value < $range['min']) {
            $errors[] = "{$fieldName} below minimum ({$this->formatValue($value, $range)} < {$this->formatValue($range['min'], $range)})";
        }

        if (isset($range['max']) && $value > $range['max']) {
            $errors[] = "{$fieldName} exceeds maximum ({$this->formatValue($value, $range)} > {$this->formatValue($range['max'], $range)})";
        }

        // Soft limits (warnings)
        if (isset($range['typical_min']) && $value < $range['typical_min']) {
            $warnings[] = "{$fieldName} unusually low: {$this->formatValue($value, $range)} (typical: {$this->formatValue($range['typical_min'], $range)} - {$this->formatValue($range['typical_max'], $range)})";
        }

        if (isset($range['typical_max']) && $value > $range['typical_max']) {
            $warnings[] = "{$fieldName} unusually high: {$this->formatValue($value, $range)} (typical: {$this->formatValue($range['typical_min'], $range)} - {$this->formatValue($range['typical_max'], $range)})";
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Format value with unit
     */
    private function formatValue($value, array $range): string
    {
        if (!isset($range['unit'])) {
            return number_format($value, 0);
        }

        $unit = $range['unit'];

        if ($unit === 'VND') {
            if ($value >= 1_000_000_000) {
                return round($value / 1_000_000_000, 2) . ' tỷ';
            }
            return round($value / 1_000_000, 0) . ' triệu';
        }

        if ($unit === 'VND/m²') {
            if ($value >= 1_000_000) {
                return round($value / 1_000_000, 0) . ' triệu/m²';
            }
            return round($value / 1_000, 0) . ' nghìn/m²';
        }

        return number_format($value, 0) . ' ' . $unit;
    }

    /**
     * Get validator name
     */
    public function getName(): string
    {
        return 'range';
    }
}
