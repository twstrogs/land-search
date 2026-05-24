<?php

namespace App\Contracts;

interface AiProviderInterface
{
    public function extract(string $content): array;

    public function checkHealth(): array;

    public function getName(): string;

    public function getModel(): string;
}
