<?php

namespace App\NLP\Extractors;

/**
 * Phone Extractor
 * 
 * Extracts and normalizes phone numbers from Vietnamese real estate text
 */
class PhoneExtractor
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
            // Standard Vietnamese phone numbers
            '/0\d{9,10}/',
            
            // Phone with separators
            '/0[\d\s\.\-]{9,12}/',
            
            // Common prefixes
            '/\b(?:098|097|096|090|091|094|093|092|088|086|085|084|083|082|081|080|079|077|076|075|074|073|072|071|070|069|068|067|066|065|064|063|062|061|060|059|058|057|056|055|054|053|052|051|050|049|048|047|046|045|044|043|042|041|040|039|038|037|036|035|034|033|032|031|030|029|028|027|026|025|024|023|022|021|020)\s*[\d\s\.\-]{6,8}/',
        ];
    }

    /**
     * Extract phone numbers from text
     */
    public function extract(string $text): array
    {
        $phones = [];
        $uniquePhones = [];

        foreach ($this->patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[0] as $match) {
                    $normalized = $this->normalizePhone($match);
                    if ($this->isValidVietnamesePhone($normalized)) {
                        $phones[] = $normalized;
                    }
                }
            }
        }

        // Remove duplicates
        $phones = array_unique($phones);

        // Remove invalid numbers
        $phones = array_values(array_filter($phones, fn($p) => $this->isValidVietnamesePhone($p)));

        return [
            'phones' => $phones,
            'count' => count($phones),
        ];
    }

    /**
     * Normalize phone number
     */
    public function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^\d]/', '', $phone);

        // Ensure starts with 0
        if (!str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        // Limit to 11 digits
        return substr($phone, 0, 11);
    }

    /**
     * Check if phone is valid Vietnamese format
     */
    public function isValidVietnamesePhone(string $phone): bool
    {
        // Must be 10-11 digits starting with 0
        if (!preg_match('/^0\d{9,10}$/', $phone)) {
            return false;
        }

        // Valid prefixes
        $validPrefixes = [
            '032', '033', '034', '035', '036', '037', '038', '039', // Viettel
            '070', '079', '077', '076', '078', // Mobifone
            '081', '082', '083', '084', '085', // Vinaphone
            '056', '058', // Vietnamobile
            '059', // Gmobile
            '090', '093', '089', // Sài Gòn
            '091', '094', '088', // Viettel
            '096', '097', '098', // Viettel
            '086', // Viettel
        ];

        $prefix = substr($phone, 0, 3);

        // Check if prefix is valid or if it's a reasonable 10-digit number
        if (in_array($prefix, $validPrefixes)) {
            return true;
        }

        // Additional check: 10 digits starting with 0
        return preg_match('/^0[2-9]\d{8}$/', $phone);
    }

    /**
     * Check if phone is suspicious (potential spam/fake)
     */
    public function isSuspicious(string $phone): array
    {
        $flags = [];

        // Repeated digits (e.g., 0911111111)
        if (preg_match('/^0(\d)\1{8,}$/', $phone)) {
            $flags[] = 'repeated_digits';
        }

        // Sequential digits
        if (preg_match('/^0(01234|12345|23456|34567|45678|56789|67890|78901|89012|90123)/', $phone)) {
            $flags[] = 'sequential_digits';
        }

        // Non-existent prefix
        $validPrefixes = ['032', '033', '034', '035', '036', '037', '038', '039', '070', '079', '077', '076', '078', '081', '082', '083', '084', '085', '090', '091', '093', '094', '096', '097', '098'];
        $prefix = substr($phone, 0, 3);
        if (!in_array($prefix, $validPrefixes) && strlen($phone) == 10) {
            $flags[] = 'unknown_prefix';
        }

        return [
            'is_suspicious' => !empty($flags),
            'flags' => $flags,
        ];
    }

    /**
     * Extract phone from any format
     */
    public function extractFirst(string $text): ?string
    {
        $result = $this->extract($text);
        return $result['phones'][0] ?? null;
    }
}
