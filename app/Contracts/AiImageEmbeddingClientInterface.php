<?php

namespace App\Contracts;

interface AiImageEmbeddingClientInterface
{
    public function provider(): string;

    /**
     * @return array{vector: array<int, float|int>, model: ?string, version: ?string, metadata?: array<string, mixed>}
     */
    public function embedFromPath(string $absolutePath): array;
}
