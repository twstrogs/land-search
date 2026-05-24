<?php

namespace App\AI\Pipeline\Validators;

/**
 * Cross-Reference Validator
 * 
 * Validates consistency between related fields
 */
class CrossReferenceValidator
{
    private array $rules = [];

    public function __construct()
    {
        $this->rules = $this->getValidationRules();
    }

    /**
     * Get cross-reference validation rules
     */
    private function getValidationRules(): array
    {
        return [
            // Price and area should both exist or neither
            [
                'name' => 'price_area_consistency',
                'fields' => ['price_value', 'area_value'],
                'check' => 'both_or_neither',
            ],
            // Address should have district
            [
                'name' => 'address_has_district',
                'fields' => ['address_text', 'district'],
                'check' => 'required_if',
                'condition' => fn($data) => !empty($data['address_text']),
            ],
            // Address should have city
            [
                'name' => 'address_has_city',
                'fields' => ['address_text', 'city'],
                'check' => 'required_if',
                'condition' => fn($data) => !empty($data['address_text']),
            ],
            // Phone count should be reasonable
            [
                'name' => 'phone_count',
                'fields' => ['phones'],
                'check' => 'max_array_length',
                'limit' => 10,
            ],
            // Property type should match content
            [
                'name' => 'property_type_match',
                'fields' => ['property_type', 'title', 'description'],
                'check' => 'context_match',
            ],
            // Price should be consistent with property type
            [
                'name' => 'price_property_type',
                'fields' => ['price_value', 'property_type', 'area_value'],
                'check' => 'price_range_by_type',
            ],
        ];
    }

    /**
     * Validate all cross-references
     */
    public function validate(array $data): array
    {
        $errors = [];
        $warnings = [];

        foreach ($this->rules as $rule) {
            $result = $this->applyRule($rule, $data);
            $errors = array_merge($errors, $result['errors']);
            $warnings = array_merge($warnings, $result['warnings']);
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Apply a single validation rule
     */
    private function applyRule(array $rule, array $data): array
    {
        $errors = [];
        $warnings = [];

        $check = $rule['check'];

        switch ($check) {
            case 'both_or_neither':
                $result = $this->checkBothOrNeither($rule['fields'], $data);
                break;

            case 'required_if':
                $result = $this->checkRequiredIf($rule, $data);
                break;

            case 'max_array_length':
                $result = $this->checkMaxArrayLength($rule, $data);
                break;

            case 'context_match':
                $result = $this->checkContextMatch($rule, $data);
                break;

            case 'price_range_by_type':
                $result = $this->checkPriceRangeByType($rule, $data);
                break;

            default:
                $result = ['errors' => [], 'warnings' => []];
        }

        return $result;
    }

    /**
     * Check both fields exist or neither
     */
    private function checkBothOrNeither(array $fields, array $data): array
    {
        $errors = [];
        $hasFirst = isset($data[$fields[0]]) && !empty($data[$fields[0]]);
        $hasSecond = isset($data[$fields[1]]) && !empty($data[$fields[1]]);

        if ($hasFirst !== $hasSecond) {
            $errors[] = "Fields '{$fields[0]}' and '{$fields[1]}' should both exist or both be empty";
        }

        return ['errors' => $errors, 'warnings' => []];
    }

    /**
     * Check field is required if condition is met
     */
    private function checkRequiredIf(array $rule, array $data): array
    {
        $errors = [];
        $conditionMet = ($rule['condition'])($data);

        if ($conditionMet) {
            $dependentField = $rule['fields'][1];
            if (empty($data[$dependentField])) {
                $errors[] = "'{$dependentField}' is required when '{$rule['fields'][0]}' is present";
            }
        }

        return ['errors' => $errors, 'warnings' => []];
    }

    /**
     * Check array doesn't exceed max length
     */
    private function checkMaxArrayLength(array $rule, array $data): array
    {
        $warnings = [];
        $field = $rule['fields'][0];
        $limit = $rule['limit'];

        if (isset($data[$field]) && is_array($data[$field])) {
            if (count($data[$field]) > $limit) {
                $warnings[] = "Field '{$field}' has too many items ({$limit})";
            }
        }

        return ['errors' => [], 'warnings' => $warnings];
    }

    /**
     * Check field matches context
     */
    private function checkContextMatch(array $rule, array $data): array
    {
        $warnings = [];
        $propertyType = $data['property_type'] ?? '';
        $title = mb_strtolower(($data['title'] ?? '') . ' ' . ($data['description'] ?? ''));

        if (empty($propertyType) || empty($title)) {
            return ['errors' => [], 'warnings' => []];
        }

        // Check if property type matches content
        $typeIndicators = [
            'Căn hộ chung cư' => ['căn hộ', 'chung cư', 'apartment', 'flat'],
            'Nhà phố/Nhà riêng' => ['nhà', 'nhà phố', 'nhà riêng', 'mặt tiền'],
            'Biệt thự (Villa)' => ['biệt thự', 'villa', 'biet thu', 'duplex'],
            'Đất nền/Đất thổ cư' => ['đất nền', 'đất thổ cư', 'dat nen', 'thổ cư'],
        ];

        $expectedIndicators = $typeIndicators[$propertyType] ?? [];
        $hasIndicator = false;

        foreach ($expectedIndicators as $indicator) {
            if (strpos($title, $indicator) !== false) {
                $hasIndicator = true;
                break;
            }
        }

        // Only warn if we found strong evidence against
        $contradictingTypes = array_diff(array_keys($typeIndicators), [$propertyType]);
        foreach ($contradictingTypes as $otherType) {
            $otherIndicators = $typeIndicators[$otherType];
            foreach ($otherIndicators as $indicator) {
                if (strpos($title, $indicator) !== false) {
                    $warnings[] = "Property type '{$propertyType}' may not match content";
                    return ['errors' => [], 'warnings' => $warnings];
                }
            }
        }

        return ['errors' => [], 'warnings' => $warnings];
    }

    /**
     * Check price is reasonable for property type
     */
    private function checkPriceRangeByType(array $rule, array $data): array
    {
        $warnings = [];
        $price = $data['price_value'] ?? 0;
        $type = $data['property_type'] ?? '';

        if ($price <= 0 || empty($type)) {
            return ['errors' => [], 'warnings' => []];
        }

        // Typical price ranges by property type (in VND)
        $typicalRanges = [
            'Căn hộ chung cư' => [
                'min' => 500_000_000,
                'max' => 10_000_000_000,
            ],
            'Nhà phố/Nhà riêng' => [
                'min' => 500_000_000,
                'max' => 50_000_000_000,
            ],
            'Biệt thự (Villa)' => [
                'min' => 5_000_000_000,
                'max' => 100_000_000_000,
            ],
            'Đất nền/Đất thổ cư' => [
                'min' => 50_000_000,
                'max' => 50_000_000_000,
            ],
        ];

        $range = $typicalRanges[$type] ?? null;

        if (!$range) {
            return ['errors' => [], 'warnings' => []];
        }

        // Only warn if price is way off
        if ($price < $range['min'] / 10 || $price > $range['max'] * 10) {
            $warnings[] = "Price may be unrealistic for property type '{$type}'";
        }

        return ['errors' => [], 'warnings' => $warnings];
    }

    /**
     * Get validator name
     */
    public function getName(): string
    {
        return 'cross_ref';
    }
}
