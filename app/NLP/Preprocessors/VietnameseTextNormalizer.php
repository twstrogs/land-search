<?php

namespace App\NLP\Preprocessors;

/**
 * Vietnamese Text Normalizer
 * 
 * Normalizes Vietnamese text for processing
 */
class VietnameseTextNormalizer
{
    private array $unicodeMap = [];

    public function __construct()
    {
        $this->initializeUnicodeMap();
    }

    /**
     * Initialize Unicode normalization map
     */
    private function initializeUnicodeMap(): void
    {
        $this->unicodeMap = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
            'đ' => 'd',
            'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A',
            'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A',
            'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A',
            'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E',
            'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O',
            'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O',
            'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U',
            'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U',
            'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y',
            'Đ' => 'D',
        ];
    }

    /**
     * Normalize to ASCII (remove diacritics)
     */
    public function toAscii(string $text): string
    {
        return strtr($text, $this->unicodeMap);
    }

    /**
     * Normalize whitespace
     */
    public function normalizeWhitespace(string $text): string
    {
        // Normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Normalize case
     */
    public function normalizeCase(string $text, string $mode = 'lower'): string
    {
        return match ($mode) {
            'lower' => mb_strtolower($text),
            'upper' => mb_strtoupper($text),
            'title' => mb_convert_case($text, MB_CASE_TITLE, 'UTF-8'),
            'first_upper' => $this->firstCharUpper($text),
            default => $text,
        };
    }

    /**
     * First character uppercase
     */
    private function firstCharUpper(string $text): string
    {
        $first = mb_substr($text, 0, 1);
        $rest = mb_substr($text, 1);
        return mb_strtoupper($first) . mb_strtolower($rest);
    }

    /**
     * Normalize number format
     */
    public function normalizeNumbers(string $text): string
    {
        // Remove spaces in numbers
        $text = preg_replace('/(\d)\s+(\d{3})/', '$1$2', $text);

        // Normalize decimal separator
        $text = preg_replace('/(\d),(\d)/', '$1.$2', $text);

        return $text;
    }

    /**
     * Full normalization pipeline
     */
    public function normalize(string $text, array $options = []): string
    {
        $options = array_merge([
            'remove_diacritics' => false,
            'lowercase' => false,
            'normalize_whitespace' => true,
            'normalize_numbers' => true,
        ], $options);

        // Whitespace
        if ($options['normalize_whitespace']) {
            $text = $this->normalizeWhitespace($text);
        }

        // Numbers
        if ($options['normalize_numbers']) {
            $text = $this->normalizeNumbers($text);
        }

        // Case
        if ($options['lowercase']) {
            $text = $this->normalizeCase($text, 'lower');
        }

        // Diacritics
        if ($options['remove_diacritics']) {
            $text = $this->toAscii($text);
        }

        return $text;
    }
}
