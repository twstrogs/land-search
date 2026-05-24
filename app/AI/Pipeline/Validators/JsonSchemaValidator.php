<?php

namespace App\AI\Pipeline\Validators;

/**
 * JSON Schema Validator
 * 
 * Validates extracted data against expected schema
 */
class JsonSchemaValidator
{
    private array $schema = [];

    public function __construct()
    {
        $this->schema = $this->getExtractionSchema();
    }

    /**
     * Get the expected extraction schema
     */
    private function getExtractionSchema(): array
    {
        return [
            'fields' => [
                'title' => [
                    'type' => 'string|null',
                    'max_length' => 500,
                    'required' => false,
                ],
                'description' => [
                    'type' => 'string|null',
                    'max_length' => 10000,
                    'required' => false,
                ],
                'property_type' => [
                    'type' => 'string|null',
                    'allowed_values' => [
                        'Căn hộ chung cư',
                        'Nhà phố/Nhà riêng',
                        'Biệt thự (Villa)',
                        'Đất nền/Đất thổ cư',
                    ],
                    'required' => false,
                ],
                'price_text' => [
                    'type' => 'string|null',
                    'max_length' => 100,
                    'required' => false,
                ],
                'price_value' => [
                    'type' => 'number|null',
                    'min' => 0,
                    'max' => 1_000_000_000_000, // 1000 tỷ
                    'required' => false,
                ],
                'area_text' => [
                    'type' => 'string|null',
                    'max_length' => 100,
                    'required' => false,
                ],
                'area_value' => [
                    'type' => 'number|null',
                    'min' => 0,
                    'max' => 1000000, // 1 triệu m2
                    'required' => false,
                ],
                'frontage_texts' => [
                    'type' => 'array',
                    'items_type' => 'string',
                    'max_items' => 10,
                    'required' => false,
                ],
                'frontage_count' => [
                    'type' => 'number',
                    'min' => 0,
                    'max' => 10,
                    'required' => false,
                ],
                'depth_text' => [
                    'type' => 'string|null',
                    'max_length' => 50,
                    'required' => false,
                ],
                'direction' => [
                    'type' => 'string|null',
                    'allowed_values' => [
                        'dong', 'tay', 'nam', 'bac',
                        'dong_nam', 'dong_bac', 'tay_nam', 'tay_bac',
                        'Đông', 'Tây', 'Nam', 'Bắc',
                        'Đông Nam', 'Đông Bắc', 'Tây Nam', 'Tây Bắc',
                    ],
                    'required' => false,
                ],
                'address_text' => [
                    'type' => 'string|null',
                    'max_length' => 500,
                    'required' => false,
                ],
                'ward' => [
                    'type' => 'string|null',
                    'max_length' => 100,
                    'required' => false,
                ],
                'district' => [
                    'type' => 'string|null',
                    'max_length' => 100,
                    'required' => false,
                ],
                'city' => [
                    'type' => 'string|null',
                    'max_length' => 100,
                    'required' => false,
                ],
                'phones' => [
                    'type' => 'array',
                    'items_type' => 'string',
                    'max_items' => 20,
                    'required' => false,
                ],
                'features' => [
                    'type' => 'array',
                    'items_type' => 'string',
                    'max_items' => 50,
                    'required' => false,
                ],
                'legal_status' => [
                    'type' => 'string|null',
                    'max_length' => 100,
                    'required' => false,
                ],
                'confidence' => [
                    'type' => 'number',
                    'min' => 0,
                    'max' => 1,
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * Validate extraction against schema
     */
    public function validate(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Check required fields
        foreach ($this->schema['fields'] as $fieldName => $fieldSchema) {
            if (($fieldSchema['required'] ?? false) && !isset($data[$fieldName])) {
                $errors[] = "Missing required field: {$fieldName}";
            }
        }

        // Validate each field
        foreach ($data as $fieldName => $value) {
            $fieldSchema = $this->schema['fields'][$fieldName] ?? null;

            if (!$fieldSchema) {
                $warnings[] = "Unknown field: {$fieldName}";
                continue;
            }

            // Type validation
            $typeResult = $this->validateType($fieldName, $value, $fieldSchema);
            if (!$typeResult['valid']) {
                $errors = array_merge($errors, $typeResult['errors']);
            }

            // Value validation
            $valueResult = $this->validateValue($fieldName, $value, $fieldSchema);
            if (!$valueResult['valid']) {
                $warnings = array_merge($warnings, $valueResult['warnings']);
            }
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'validated_fields' => count($data),
            'schema_version' => '1.0',
        ];
    }

    /**
     * Validate field type
     */
    private function validateType(string $fieldName, $value, array $schema): array
    {
        $errors = [];
        $expectedType = $schema['type'];

        if ($value === null) {
            return ['valid' => true, 'errors' => []];
        }

        $isValid = match ($expectedType) {
            'string|null' => is_string($value) || $value === null,
            'string' => is_string($value),
            'number|null' => is_numeric($value) || $value === null,
            'number' => is_numeric($value),
            'array' => is_array($value),
            default => true,
        };

        if (!$isValid) {
            $errors[] = "Field '{$fieldName}' has invalid type. Expected: {$expectedType}";
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validate field value
     */
    private function validateValue(string $fieldName, $value, array $schema): array
    {
        $warnings = [];

        if ($value === null) {
            return ['valid' => true, 'warnings' => []];
        }

        // Max length check
        if (isset($schema['max_length']) && is_string($value)) {
            if (mb_strlen($value) > $schema['max_length']) {
                $warnings[] = "Field '{$fieldName}' exceeds max length ({$schema['max_length']})";
            }
        }

        // Min/max check
        if (isset($schema['min']) && is_numeric($value)) {
            if ($value < $schema['min']) {
                $warnings[] = "Field '{$fieldName}' is below minimum ({$schema['min']})";
            }
        }

        if (isset($schema['max']) && is_numeric($value)) {
            if ($value > $schema['max']) {
                $warnings[] = "Field '{$fieldName}' exceeds maximum ({$schema['max']})";
            }
        }

        // Allowed values check
        if (isset($schema['allowed_values']) && !in_array($value, $schema['allowed_values'])) {
            // Check case-insensitive for directions
            if (in_array(mb_strtolower($value), array_map('mb_strtolower', $schema['allowed_values']))) {
                // Accept lowercase versions
            } else {
                $warnings[] = "Field '{$fieldName}' has unexpected value: {$value}";
            }
        }

        // Array max items check
        if (isset($schema['max_items']) && is_array($value)) {
            if (count($value) > $schema['max_items']) {
                $warnings[] = "Field '{$fieldName}' exceeds max items ({$schema['max_items']})";
            }
        }

        return ['valid' => true, 'warnings' => $warnings];
    }

    /**
     * Get validator name
     */
    public function getName(): string
    {
        return 'schema';
    }
}
