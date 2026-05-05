<?php

namespace App\Integrations\Ai;

use App\Contracts\AiImageEmbeddingClientInterface;
use RuntimeException;

class FakeImageEmbeddingClient implements AiImageEmbeddingClientInterface
{
    public function provider(): string
    {
        return 'fake-image-embedding';
    }

    public function embedFromPath(string $absolutePath): array
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Fake AI image embedding client cannot be used in production.');
        }

        $hash = hash_file('sha256', $absolutePath) ?: hash('sha256', $absolutePath);
        $chunks = str_split(substr($hash, 0, 64), 4);
        $vector = array_map(function (string $chunk): float {
            return hexdec($chunk) / 65535;
        }, $chunks);

        return [
            'vector' => $vector,
            'model' => 'fake-hash-v1',
            'version' => 'v1',
            'metadata' => [
                'source' => 'deterministic-placeholder',
            ],
        ];
    }
}
