<?php

namespace App\DTOs;

class SearchResultDTO
{
    public function __construct(
        public readonly array $posts,
        public readonly string $keyword,
        public readonly int $totalResults,
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly string $sessionToken,
        public readonly \DateTimeInterface $expiresAt,
    ) {}

    public static function create(
        array $posts,
        string $keyword,
        int $totalResults,
        int $currentPage,
        int $perPage,
    ): self {
        $sessionToken = bin2hex(random_bytes(16));
        $expiresAt = now()->addMinutes(30);

        return new self(
            posts: $posts,
            keyword: $keyword,
            totalResults: $totalResults,
            currentPage: $currentPage,
            perPage: $perPage,
            sessionToken: $sessionToken,
            expiresAt: $expiresAt,
        );
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expiresAt);
    }

    public function getNextPageUrl(): ?string
    {
        if ($this->currentPage * $this->perPage >= $this->totalResults) {
            return null;
        }
        
        return url("/search?keyword=" . urlencode($this->keyword) . "&page=" . ($this->currentPage + 1));
    }
}
