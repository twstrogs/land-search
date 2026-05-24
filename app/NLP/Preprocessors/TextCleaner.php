<?php

namespace App\NLP\Preprocessors;

/**
 * Text Cleaner
 * 
 * Comprehensive text cleaning for Vietnamese real estate data
 */
class TextCleaner
{
    private array $patterns = [];

    public function __construct()
    {
        $this->initializePatterns();
    }

    /**
     * Initialize cleaning patterns
     */
    private function initializePatterns(): void
    {
        $this->patterns = [
            // URLs
            '/https?:\/\/[^\s]+/i' => '',
            
            // Email addresses
            '/[\w.+-]+@[\w-]+\.[\w.-]+/i' => '',
            
            // Multiple spaces
            '/\s{2,}/' => ' ',
            
            // Special characters to preserve
            '/[^\p{L}\p{N}\p{P}\p{Zs}\n\r\p{M}]/u' => '',
            
            // Control characters
            '/[\x00-\x1F\x7F]/' => '',
            
            // Multiple newlines
            '/\n{3,}/' => "\n\n",
        ];
    }

    /**
     * Clean text
     */
    public function clean(string $text): string
    {
        // Remove emojis
        $text = $this->removeEmojis($text);

        // Apply patterns
        foreach ($this->patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        // Trim
        $text = trim($text);

        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return $text;
    }

    /**
     * Remove emojis
     */
    public function removeEmojis(string $text): string
    {
        return preg_replace(
            '/[\x{1F600}-\x{1F64F}'
            . '\x{1F300}-\x{1F5FF}'
            . '\x{1F680}-\x{1F6FF}'
            . '\x{1F1E0}-\x{1F1FF}]/u',
            '',
            $text
        );
    }

    /**
     * Truncate text to max length
     */
    public function truncate(string $text, int $maxLength, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength - mb_strlen($suffix)) . $suffix;
    }
}
