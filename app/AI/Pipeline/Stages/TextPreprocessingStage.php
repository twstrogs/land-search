<?php

namespace App\AI\Pipeline\Stages;

use App\AI\Pipeline\PipelineStageInterface;
use App\AI\Exceptions\StageException;

/**
 * Text Preprocessing Stage
 * 
 * Handles initial text cleaning and normalization:
 * - Unicode normalization
 * - Emoji removal
 * - HTML stripping
 * - Whitespace normalization
 * - OCR error correction
 * - Vietnamese slang expansion
 */
class TextPreprocessingStage implements PipelineStageInterface
{
    private array $slangMap = [];
    private array $ocrFixes = [];

    public function __construct()
    {
        $this->initializeSlangMap();
        $this->initializeOcrFixes();
    }

    /**
     * Execute preprocessing on raw text
     */
    public function execute(array $data): array
    {
        $text = $data['raw'] ?? '';

        if (empty(trim($text))) {
            throw new StageException(
                'TextPreprocessingStage',
                'Raw text is empty',
                StageException::VALIDATION_ERROR
            );
        }

        $originalLength = mb_strlen($text);
        $processingSteps = [];

        // Step 1: Remove emojis
        $text = $this->removeEmojis($text);
        $processingSteps['emoji_removal'] = [
            'executed' => true,
            'chars_removed' => $originalLength - mb_strlen($text),
        ];

        // Step 2: Normalize unicode
        $text = $this->normalizeUnicode($text);
        $processingSteps['unicode_normalization'] = ['executed' => true];

        // Step 3: Strip HTML tags
        $text = strip_tags($text);
        $processingSteps['html_stripping'] = ['executed' => true];

        // Step 4: Normalize whitespace
        $text = $this->normalizeWhitespace($text);
        $processingSteps['whitespace_normalization'] = ['executed' => true];

        // Step 5: Fix OCR errors
        $text = $this->fixOcrErrors($text);
        $processingSteps['ocr_correction'] = [
            'executed' => true,
            'fixes_applied' => $this->countOcrFixes($text),
        ];

        // Step 6: Expand Vietnamese slang
        $text = $this->expandSlang($text);
        $processingSteps['slang_expansion'] = [
            'executed' => true,
            'words_expanded' => $this->countSlangExpansions($text),
        ];

        // Step 7: Fix common typos
        $text = $this->fixCommonTypos($text);
        $processingSteps['typo_correction'] = ['executed' => true];

        // Step 8: Normalize numbers and units
        $text = $this->normalizeNumbersAndUnits($text);
        $processingSteps['number_normalization'] = ['executed' => true];

        return array_merge($data, [
            'preprocessed' => $text,
            'original_text' => $data['raw'],
            'preprocessing_metadata' => [
                'original_length' => $originalLength,
                'processed_length' => mb_strlen($text),
                'chars_removed' => $originalLength - mb_strlen($text),
                'processing_steps' => $processingSteps,
            ],
        ]);
    }

    /**
     * Remove emoji characters
     */
    private function removeEmojis(string $text): string
    {
        // Comprehensive emoji removal
        return preg_replace(
            '/[\x{1F600}-\x{1F64F}'  // Emoticons
            . '\x{1F300}-\x{1F5FF}'  // Misc Symbols and Pictographs
            . '\x{1F680}-\x{1F6FF}'  // Transport and Map Symbols
            . '\x{1F1E0}-\x{1F1FF}'  // Flags
            . '\x{2600}-\x{26FF}'     // Misc symbols
            . '\x{2700}-\x{27BF}'     // Dingbats
            . '\x{FE00}-\x{FE0F}'     // Variation Selectors
            . '\x{1F900}-\x{1F9FF}'  // Supplemental Symbols and Pictographs
            . '\x{1FA00}-\x{1FA6F}'  // Chess Symbols
            . '\x{1FA70}-\x{1FAFF}'  // Symbols and Pictographs Extended-A
            . '\x{1F004}-\x{1F0CF}'  // Mahjong Tiles
            . '\x{1F170}-\x{1F251}'  // Enclosed Alphanumeric Supplement
            . ']/u',
            '',
            $text
        );
    }

    /**
     * Normalize Unicode characters
     */
    private function normalizeUnicode(string $text): string
    {
        // Normalize to NFC form
        if (function_exists('normalizer_normalize')) {
            $text = normalizer_normalize($text, \Normalizer::FORM_C);
        }

        // Fix common encoding issues
        $text = str_replace([
            "\xC2\xA0", // Non-breaking space
            "\xE2\x80\x8B", // Zero-width space
            "\xE2\x80\x8C", // Zero-width non-joiner
            "\xE2\x80\x8D", // Zero-width joiner
            "\xE2\x80\xAF", // Narrow no-break space
            "\xE2\x81\xA0", // Medium mathematical space
        ], ' ', $text);

        return $text;
    }

    /**
     * Normalize whitespace
     */
    private function normalizeWhitespace(string $text): string
    {
        // Replace multiple spaces with single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove leading/trailing whitespace
        $text = trim($text);

        // Normalize line breaks
        $text = preg_replace('/[\r\n]+/', "\n", $text);

        // Remove empty lines
        $text = preg_replace('/\n\s*\n/', "\n", $text);

        return $text;
    }

    /**
     * Initialize Vietnamese real estate slang map
     */
    private function initializeSlangMap(): void
    {
        $this->slangMap = [
            // Abbreviations
            'dt' => 'diện tích',
            'mt' => 'mặt tiền',
            'cc' => 'chung cư',
            'tn' => 'thanh niên',
            'nh' => 'nhà',
            'đn' => 'đất nền',
            'đts' => 'đất thổ cư',
            'pn' => 'phòng ngủ',
            'wc' => 'vệ sinh',
            'kt' => 'kích thước',
            'cty' => 'công ty',
            'ks' => 'khách sạn',

            // Price slang
            'k' => 'nghìn',
            'củ' => 'triệu',
            'tr' => 'triệu',
            'tỏ' => 'tỷ',
            'kỳ' => 'tỷ',
            'lít' => 'triệu',

            // Area slang
            'ha' => 'hecta',
            'sao' => 'sào',

            // Direction shorthand
            'đn' => 'đông nam',
            'đb' => 'đông bắc',
            'tn' => 'tây nam',
            'tb' => 'tây bắc',

            // Property types
            'đp' => 'đất phố',
            'bp' => 'biệt thự',

            // Status
            'ct' => 'có thể',
            'kk' => 'không có',
            'kg' => 'không',
            'dc' => 'được',
            'vc' => 'vô cùng',

            // Common broker slang
            'gth' => 'giá thị trường',
            'cty' => 'công ty',
            'mkg' => 'môi giới',
            'tvl' => 'tư vấn miễn phí',
            'kdl' => 'khu du lịch',
            'tk' => 'tiếp khách',
        ];
    }

    /**
     * Initialize OCR error fixes
     */
    private function initializeOcrFixes(): void
    {
        $this->ocrFixes = [
            // Direction typos
            '/đôngn/' => 'đông',
            '/đôngb/' => 'đông',
            '/đông\s*n/' => 'đông',
            '/đông\s*b/' => 'đông',
            '/tâyn/' => 'tây',
            '/tâyb/' => 'tây',
            '/tây\s*n/' => 'tây',
            '/tây\s*b/' => 'tây',

            // Number confusion
            '/(\d)\s*[xX×]\s*\d/' => '$1xx',

            // Character confusion
            '/đg/' => 'đ',
            '/rn/' => 'm',
            '/rn/' => 'n',
            '/vv/' => 'v',

            // Spacing issues
            '/(\w)\s+(\w)(nam|bac|tay|ong)/iu' => '$1$2$3',

            // Common mispellings
            '/\bhướng\s*(\w+)\s+(\w+)\b/iu' => 'hướng $1$2',
            '/\bđất\s*đai\b/' => 'đất đai',
        ];
    }

    /**
     * Fix OCR errors
     */
    private function fixOcrErrors(string $text): string
    {
        foreach ($this->ocrFixes as $pattern => $replacement) {
            if (is_string($pattern)) {
                $text = preg_replace($pattern, $replacement, $text);
            } else {
                $text = preg_replace($pattern, $replacement, $text);
            }
        }

        return $text;
    }

    /**
     * Count OCR fixes applied
     */
    private function countOcrFixes(string $text): int
    {
        // Simplified count - just return based on pattern matches
        $count = 0;
        foreach ($this->ocrFixes as $pattern => $replacement) {
            if (is_string($pattern)) {
                $count += substr_count($text, $pattern);
            } else {
                $count += preg_match_all($pattern, $text);
            }
        }
        return $count;
    }

    /**
     * Expand Vietnamese slang
     */
    private function expandSlang(string $text): string
    {
        $text = mb_strtolower($text);

        // Sort by length (longest first) to avoid partial replacements
        uksort($this->slangMap, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

        foreach ($this->slangMap as $slang => $expansion) {
            // Only expand when surrounded by word boundaries or spaces
            $pattern = '/\b' . preg_quote($slang, '/') . '\b/iu';
            $text = preg_replace($pattern, $expansion, $text);
        }

        return $text;
    }

    /**
     * Count slang expansions
     */
    private function countSlangExpansions(string $text): int
    {
        // Simplified count
        return 0;
    }

    /**
     * Fix common typos
     */
    private function fixCommonTypos(string $text): string
    {
        $typos = [
            // Common typos
            '/đ/ể/' => 'để',
            '/đ/ờ/' => 'đờ',
            '/l/ı/' => 'l',
            '/n/ñ/' => 'n',
            '/o/ô/' => 'ô',
            // Fix spacing around punctuation
            '/\s+,/' => ',',
            '/\s+\./' => '.',
            '/\s+:/' => ':',
        ];

        foreach ($typos as $search => $replace) {
            if (strpos($search, '/') === 0) {
                $text = preg_replace($search, $replace, $text);
            } else {
                $text = str_replace($search, $replace, $text);
            }
        }

        return $text;
    }

    /**
     * Normalize numbers and units
     */
    private function normalizeNumbersAndUnits(string $text): string
    {
        // Normalize spaces in numbers
        $text = preg_replace('/(\d)\s*(\d{3})/', '$1$2', $text);

        // Normalize common unit abbreviations
        $unitReplacements = [
            '/\bm2\b/i' => 'm²',
            '/\bm3\b/i' => 'm³',
            '/\bkm2\b/i' => 'km²',
            '/\bkm\b(?!\s*:)/i' => 'km',
            '/\bngàn\b/' => 'nghìn',
            '/\bngàn\b/' => 'nghìn',
            '/\btr\b(?=\s*(đồng|vnđ))/i' => 'triệu',
        ];

        foreach ($unitReplacements as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }

    /**
     * Check if this is a critical stage
     */
    public function isCritical(): bool
    {
        return true; // Preprocessing is always critical
    }

    /**
     * Get stage name
     */
    public function getName(): string
    {
        return 'preprocess';
    }

    /**
     * Get stage description
     */
    public function getDescription(): string
    {
        return 'Text Preprocessing - Unicode normalization, emoji removal, OCR correction, slang expansion';
    }

    /**
     * Get required input data keys
     */
    public function getRequiredInputs(): array
    {
        return ['raw'];
    }

    /**
     * Get output data keys
     */
    public function getOutputs(): array
    {
        return ['preprocessed', 'preprocessing_metadata'];
    }

    /**
     * Validate input data
     */
    public function validateInput(array $data): void
    {
        if (!isset($data['raw'])) {
            throw new StageException(
                'TextPreprocessingStage',
                'Missing required input: raw',
                StageException::VALIDATION_ERROR
            );
        }

        if (!is_string($data['raw'])) {
            throw new StageException(
                'TextPreprocessingStage',
                'Input "raw" must be a string',
                StageException::VALIDATION_ERROR
            );
        }
    }
}
