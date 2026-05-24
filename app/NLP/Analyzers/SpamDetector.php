<?php

namespace App\NLP\Analyzers;

use App\Models\Post;

/**
 * Spam Detector
 * 
 * Analyzes posts for spam indicators
 */
class SpamDetector
{
    private array $spamPatterns = [];
    private array $urgencyPatterns = [];
    private array $suspiciousPatterns = [];

    public function __construct()
    {
        $this->initializePatterns();
    }

    /**
     * Initialize spam detection patterns
     */
    private function initializePatterns(): void
    {
        // Price-based spam
        $this->spamPatterns['price'] = [
            '/giá\s*shock/i' => 20,
            '/sale\s*\d+%/i' => 20,
            '/giảm\s*giá/i' => 10,
            '/free\s*ship/i' => 15,
            '/miễn\s*phí/i' => 5,
            '/\d+%\s*off/i' => 15,
        ];

        // Urgency tactics
        $this->urgencyPatterns = [
            '/gọi\s*ngay/i' => 25,
            '/chỉ\s*còn\s*\d+/i' => 20,
            '/hết\s*hạn/i' => 20,
            '/hôm\s*nay/i' => 10,
            '/ngay\s*hôm/i' => 10,
            '/trong\s*hôm/i' => 10,
            '/limited/i' => 15,
            '/số\s*lượng\s*có\s*hạn/i' => 15,
        ];

        // Suspicious patterns
        $this->suspiciousPatterns = [
            '/https?:\/\/(?!www\.facebook\.com)/i' => 30,
            '/link\s*bit\.ly/i' => 20,
            '/link\s*tinyurl/i' => 20,
            '/\b\d{10,}\b/' => 15, // Too many digits
            '/[A-Z]{10,}/' => 10, // All caps words
        ];

        // Quality indicators (reduce spam score)
        $this->qualityIndicators = [
            'sổ đỏ' => -10,
            'sổ hồng' => -10,
            'chính chủ' => -15,
            'thực tế' => -10,
            '亲眼' => -20, // "nhìn thực" in Chinese - genuine sign
        ];
    }

    /**
     * Analyze post for spam
     */
    public function analyze(string|array $content): SpamAnalysisResult
    {
        $text = is_array($content) ? ($content['text'] ?? '') : $content;
        $text = mb_strtolower($text);

        $score = 0;
        $flags = [];
        $indicators = [];

        // Check spam patterns
        foreach ($this->spamPatterns['price'] as $pattern => $weight) {
            if (preg_match($pattern, $text)) {
                $score += $weight;
                $flags[] = 'price_spam';
                $indicators[] = "Matched price spam pattern: {$pattern}";
            }
        }

        // Check urgency patterns
        foreach ($this->urgencyPatterns as $pattern => $weight) {
            if (preg_match($pattern, $text)) {
                $score += $weight;
                $flags[] = 'urgency_tactic';
                $indicators[] = "Matched urgency pattern: {$pattern}";
            }
        }

        // Check suspicious patterns
        foreach ($this->suspiciousPatterns as $pattern => $weight) {
            if (preg_match($pattern, $text)) {
                $score += $weight;
                $flags[] = 'suspicious';
                $indicators[] = "Matched suspicious pattern: {$pattern}";
            }
        }

        // Check quality indicators (reduce score)
        foreach ($this->qualityIndicators as $indicator => $weight) {
            if (strpos($text, $indicator) !== false) {
                $score += $weight; // negative weight reduces score
                if ($weight < 0) {
                    $indicators[] = "Quality indicator found: {$indicator}";
                }
            }
        }

        // Check for duplicate content patterns
        if ($this->hasDuplicatePatterns($text)) {
            $score += 20;
            $flags[] = 'duplicate_content';
        }

        // Normalize score to 0-100
        $score = max(0, min(100, $score));

        return new SpamAnalysisResult(
            score: $score,
            isSpam: $score >= 50,
            confidence: $this->calculateConfidence($score, $flags),
            flags: array_unique($flags),
            indicators: $indicators
        );
    }

    /**
     * Check for duplicate content patterns
     */
    private function hasDuplicatePatterns(string $text): bool
    {
        // Check for repeated sentences
        $sentences = preg_split('/[.!?]+/', $text);
        $sentences = array_filter(array_map('trim', $sentences));

        if (count($sentences) < 3) {
            return false;
        }

        $unique = array_unique($sentences);
        $ratio = count($unique) / count($sentences);

        // If more than half are duplicates, flag as spam
        return $ratio < 0.5;
    }

    /**
     * Calculate detection confidence
     */
    private function calculateConfidence(int $score, array $flags): float
    {
        $baseConfidence = 0.5;

        // Higher score = higher confidence
        if ($score >= 70) {
            $baseConfidence += 0.3;
        } elseif ($score >= 50) {
            $baseConfidence += 0.2;
        }

        // Multiple flag types increase confidence
        $uniqueFlags = array_unique($flags);
        $baseConfidence += count($uniqueFlags) * 0.05;

        return min(1.0, max(0.0, $baseConfidence));
    }

    /**
     * Batch analyze multiple posts
     */
    public function analyzeBatch(array $posts): array
    {
        $results = [];

        foreach ($posts as $id => $content) {
            $results[$id] = $this->analyze($content);
        }

        return $results;
    }

    /**
     * Get spam statistics
     */
    public function getStatistics(array $results): array
    {
        $total = count($results);
        $spamCount = count(array_filter($results, fn($r) => $r->isSpam));
        $avgScore = array_sum(array_map(fn($r) => $r->score, $results)) / $total;

        // Count flag frequencies
        $flagCounts = [];
        foreach ($results as $result) {
            foreach ($result->flags as $flag) {
                $flagCounts[$flag] = ($flagCounts[$flag] ?? 0) + 1;
            }
        }

        return [
            'total' => $total,
            'spam_count' => $spamCount,
            'spam_rate' => $total > 0 ? $spamCount / $total : 0,
            'avg_score' => round($avgScore, 2),
            'flag_counts' => $flagCounts,
        ];
    }
}

/**
 * Spam Analysis Result
 */
class SpamAnalysisResult
{
    public function __construct(
        public readonly int $score,
        public readonly bool $isSpam,
        public readonly float $confidence,
        public readonly array $flags,
        public readonly array $indicators
    ) {}

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'is_spam' => $this->isSpam,
            'confidence' => $this->confidence,
            'flags' => $this->flags,
            'indicators' => $this->indicators,
        ];
    }
}
