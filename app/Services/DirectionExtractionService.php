<?php

namespace App\Services;

/**
 * Specialized service for extracting and normalizing Vietnamese real estate directions.
 * Handles all the chaotic ways Vietnamese people write directions on Facebook posts.
 */
class DirectionExtractionService
{
    /**
     * Unicode normalization map for Vietnamese diacritics
     */
    protected array $unicodeMap = [
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
    ];

    /**
     * Direction patterns with all possible variations
     */
    protected array $directionPatterns = [
        // 4 main directions + combined
        'dong' => [
            'patterns' => [
                '/\bhướng\s*d(ong|bắc|nam)?\b/iu',
                '/\b(d(ong|bắc|nam)?)\b(?=.*mát|.*thoáng|.*đẹp|.*hợp)/iu',
                '/\bd(ong)?(?=\s|$|[,;:.!~*])/iu',
                '/\bDN\b/',
                '/\bD\b(?=.*hướng)/iu',
            ],
            'keywords' => ['đông', 'dong', 'd'],
            'normalize' => ['hướng đông', 'huong dong', 'dong'],
        ],
        'tay' => [
            'patterns' => [
                '/\bhướng\s*t(ay|ây)?\b/iu',
                '/\bt(ay|ây)?(?=\s|$|[,;:.!~*])/iu',
                '/\bTN\b/',
            ],
            'keywords' => ['tây', 'tay', ' tâ'],
            'normalize' => ['hướng tây', 'huong tay', 'tay'],
        ],
        'nam' => [
            'patterns' => [
                '/\bhướng\s*nam\b/iu',
                '/\bnam(?=\s|$|[,;:.!~*])/iu',
                '/\bN\b(?=.*hướng)/iu',
            ],
            'keywords' => ['nam'],
            'normalize' => ['hướng nam', 'huong nam', 'nam'],
        ],
        'bac' => [
            'patterns' => [
                '/\bhướng\s*bắc\b/iu',
                '/\bbắc(?=\s|$|[,;:.!~*])/iu',
                '/\bB\b(?=.*hướng)/iu',
            ],
            'keywords' => ['bắc', 'bac'],
            'normalize' => ['hướng bắc', 'huong bac', 'bac'],
        ],
        'dong_nam' => [
            'patterns' => [
                '/\bhướng\s*(đông\s*nam|đông\s*nam)\b/iu',
                '/\b(đông|đông)\s*nam\b/iu',
                '/\bd(ong)?\s*nam\b/iu',
                '/\bDN\b/',
                '/\bĐN\b/',
                '/\bđn\b/',
            ],
            'keywords' => ['đông nam', 'dong nam', 'đôngnam', 'dongnam', 'đông-nam'],
            'normalize' => ['đông nam', 'dong nam', 'đôngnam', 'dn', 'ĐN'],
        ],
        'dong_bac' => [
            'patterns' => [
                '/\bhướng\s*(đông\s*bắc|đông\s*bắc)\b/iu',
                '/\b(đông|đông)\s*bắc\b/iu',
                '/\bd(ong)?\s*bac\b/iu',
                '/\bDB\b/',
                '/\bĐB\b/',
            ],
            'keywords' => ['đông bắc', 'dong bac', 'đôngbắc', 'dongbac'],
            'normalize' => ['đông bắc', 'dong bac', 'đôngbắc', 'db', 'ĐB'],
        ],
        'tay_nam' => [
            'patterns' => [
                '/\bhướng\s*(tây\s*nam|tay\s*nam)\b/iu',
                '/\b(tây|tay)\s*nam\b/iu',
                '/\bt(ay)?\s*nam\b/iu',
                '/\bTN\b/',
            ],
            'keywords' => ['tây nam', 'tay nam', 'tâynam', 'taynam'],
            'normalize' => ['tây nam', 'tay nam', 'tâynam', 'tn'],
        ],
        'tay_bac' => [
            'patterns' => [
                '/\bhướng\s*(tây\s*bắc|tay\s*bac)\b/iu',
                '/\b(tây|tay)\s*bắc\b/iu',
                '/\bt(ay)?\s*bac\b/iu',
                '/\bTB\b/',
            ],
            'keywords' => ['tây bắc', 'tay bac', 'tâybắc', 'taybac'],
            'normalize' => ['tây bắc', 'tay bac', 'tâybắc', 'tb'],
        ],
    ];

    /**
     * OCR error / typo patterns
     */
    protected array $ocrCorrections = [
        'đg' => 'đ',
        'hg' => 'h',
        'hướng' => 'hướng',
        '/h\s+/' => 'hướng ',
        '/\bhuớng\b/' => 'hướng',
        '/\bhương\b/' => 'hướng',
        '/\bhuong\b/' => 'hướng',
        '/\bdogn\b/' => 'dong',
    ];

    /**
     * Extract directions from raw text
     * Returns array of normalized direction codes: ['dong_nam', 'tay']
     */
    public function extract(string $text): array
    {
        if (empty(trim($text))) {
            return [];
        }

        // Preprocess text
        $processed = $this->preprocess($text);

        $foundDirections = [];

        // Check each direction pattern
        foreach ($this->directionPatterns as $code => $config) {
            if ($this->matchesDirection($processed, $code)) {
                $foundDirections[] = $code;
            }
        }

        // Remove duplicates and return
        return array_values(array_unique($foundDirections));
    }

    /**
     * Preprocess text: normalize unicode, lowercase, clean
     */
    protected function preprocess(string $text): string
    {
        // Remove emojis
        $text = $this->removeEmoji($text);

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Apply OCR corrections
        $text = $this->applyOcrCorrections($text);

        // Lowercase
        $text = mb_strtolower($text);

        return trim($text);
    }

    /**
     * Remove emoji characters
     */
    protected function removeEmoji(string $text): string
    {
        // Remove emoji using Unicode ranges
        return preg_replace(
            '/[\x{1F600}-\x{1F64F}'
            . '\x{1F300}-\x{1F5FF}'
            . '\x{1F680}-\x{1F6FF}'
            . '\x{1F1E0}-\x{1F1FF}'
            . '\x{2600}-\x{26FF}]/u',
            '',
            $text
        );
    }

    /**
     * Apply OCR error corrections
     */
    protected function applyOcrCorrections(string $text): string
    {
        // Common OCR fixes
        $fixes = [
            'đg' => 'đ',
            'đôngn' => 'đông',
            'đôngb' => 'đông',
            'tâyn' => 'tây',
            'tâyb' => 'tây',
            // Fix spacing issues
            '/(\w)\s+(\w)\s+(\w)(nam|bac|tay|ong)/iu' => '$1$2$3$4',
            // Fix common typos
            '/\bhướng\s*(\w+)\s+(\w+)\b/iu' => 'hướng $1$2',
        ];

        foreach ($fixes as $search => $replace) {
            if (is_string($search)) {
                $text = str_replace($search, $replace, $text);
            } else {
                $text = preg_replace($search, $replace, $text);
            }
        }

        return $text;
    }

    /**
     * Check if text matches a specific direction
     */
    protected function matchesDirection(string $text, string $code): bool
    {
        $config = $this->directionPatterns[$code] ?? null;

        if (!$config) {
            return false;
        }

        // Check regex patterns first
        foreach ($config['patterns'] as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        // Check keywords (normalized text)
        foreach ($config['keywords'] as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize unicode to ASCII (remove diacritics)
     */
    public function normalizeToAscii(string $text): string
    {
        return strtr(mb_strtolower($text), $this->unicodeMap);
    }

    /**
     * Get human-readable name for direction code
     */
    public function getDirectionName(string $code): string
    {
        $names = [
            'dong' => 'Hướng Đông',
            'tay' => 'Hướng Tây',
            'nam' => 'Hướng Nam',
            'bac' => 'Hướng Bắc',
            'dong_nam' => 'Hướng Đông Nam',
            'dong_bac' => 'Hướng Đông Bắc',
            'tay_nam' => 'Hướng Tây Nam',
            'tay_bac' => 'Hướng Tây Bắc',
        ];

        return $names[$code] ?? ucfirst(str_replace('_', ' ', $code));
    }

    /**
     * Get compass degrees for direction
     */
    public function getDirectionDegrees(string $code): ?int
    {
        $degrees = [
            'dong' => 90,
            'dong_nam' => 135,
            'nam' => 180,
            'tay_nam' => 225,
            'tay' => 270,
            'tay_bac' => 315,
            'bac' => 0,
            'dong_bac' => 45,
        ];

        return $degrees[$code] ?? null;
    }

    /**
     * Check if direction is "good" based on phong thủy
     * In Vietnamese real estate, certain directions are considered favorable
     */
    public function isFavorableDirection(array $directions): bool
    {
        // Đông tứ mệnh: Đông, Đông Nam, Nam, Bắc
        // Tây tứ mệnh: Tây, Tây Nam, Tây Bắc, Đông Bắc
        $eastGroup = ['dong', 'dong_nam', 'nam', 'bac'];
        $westGroup = ['tay', 'tay_nam', 'tay_bac', 'dong_bac'];

        foreach ($directions as $dir) {
            if (in_array($dir, $eastGroup) || in_array($dir, $westGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get direction group (Đông tứ or Tây tứ)
     */
    public function getDirectionGroup(string $code): ?string
    {
        $eastGroup = ['dong', 'dong_nam', 'nam', 'bac'];
        $westGroup = ['tay', 'tay_nam', 'tay_bac', 'dong_bac'];

        if (in_array($code, $eastGroup)) {
            return 'dong_tu';
        }
        if (in_array($code, $westGroup)) {
            return 'tay_tu';
        }

        return null;
    }

    /**
     * Validate direction code
     */
    public function isValidDirection(string $code): bool
    {
        return isset($this->directionPatterns[$code]);
    }

    /**
     * Extract direction with confidence score
     */
    public function extractWithConfidence(string $text): array
    {
        $directions = $this->extract($text);
        $confidence = count($directions) > 0 ? 1.0 : 0.0;

        // Lower confidence if direction appears in negative context
        $negativePatterns = [
            '/không\s+hướng/i',
            '/hướng\s+nào\s+cũng\s*được/i',
        ];

        foreach ($negativePatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                $confidence *= 0.5;
            }
        }

        return [
            'directions' => $directions,
            'confidence' => round($confidence, 2),
            'primary_direction' => $directions[0] ?? null,
            'group' => isset($directions[0]) ? $this->getDirectionGroup($directions[0]) : null,
        ];
    }
}
