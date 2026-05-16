<?php

namespace App\Integrations\Ai;

use App\Contracts\AiImageEmbeddingClientInterface;
use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class HuggingFaceImageEmbeddingClient implements AiImageEmbeddingClientInterface
{
    private const DEFAULT_MODEL = 'facebook/data2vec-vision-base';

    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 60,
            'connect_timeout' => 10,
            'verify' => CaBundle::getBundledCaBundlePath(),
        ]);
    }

    public function provider(): string
    {
        return 'huggingface-clip';
    }

    public function embedFromPath(string $absolutePath): array
    {
        $apiKey = (string) config('services.ai_similarity.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('AI_SIMILARITY_API_KEY is not set for HuggingFace embedding client.');
        }

        if (! is_readable($absolutePath)) {
            throw new RuntimeException("Image file is not readable: {$absolutePath}");
        }

        $model = (string) (config('services.ai_similarity.model') ?: self::DEFAULT_MODEL);
        $endpoint = (string) config('services.ai_similarity.endpoint')
            ?: "https://router.huggingface.co/hf-inference/models/{$model}/pipeline/image-feature-extraction";

        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $body = file_get_contents($absolutePath);

        if ($body === false) {
            throw new RuntimeException("Failed to read image bytes from: {$absolutePath}");
        }

        try {
            $response = $this->httpClient->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => $mimeType,
                    'Accept' => 'application/json',
                ],
                'body' => $body,
            ]);
        } catch (GuzzleException $exception) {
            throw new RuntimeException('HuggingFace embedding request failed: '.$exception->getMessage(), 0, $exception);
        }

        $payload = json_decode($response->getBody()->getContents(), true);

        $vector = $this->flattenVector($payload);

        if ($vector === null || count($vector) === 0) {
            throw new RuntimeException('HuggingFace response did not contain a usable embedding vector.');
        }

        return [
            'vector' => $vector,
            'model' => $model,
            'version' => 'v1',
            'metadata' => [
                'provider' => 'huggingface',
                'endpoint' => $endpoint,
                'dimensions' => count($vector),
            ],
        ];
    }

    /**
     * HuggingFace feature-extraction responses come back in several shapes:
     *   - flat: [0.1, 0.2, ...]
     *   - one row: [[0.1, 0.2, ...]]
     *   - token-level: [[[..tokens..], [..tokens..]]] (need mean pooling)
     */
    private function flattenVector(mixed $payload): ?array
    {
        if (! is_array($payload) || count($payload) === 0) {
            return null;
        }

        if (is_numeric($payload[0] ?? null)) {
            return array_map('floatval', $payload);
        }

        $first = $payload[0];

        if (is_array($first) && is_numeric($first[0] ?? null)) {
            return array_map('floatval', $first);
        }

        if (is_array($first) && is_array($first[0] ?? null)) {
            $tokens = $first;
            $dim = count($tokens[0]);
            $sum = array_fill(0, $dim, 0.0);
            $count = 0;

            foreach ($tokens as $token) {
                if (! is_array($token) || count($token) !== $dim) {
                    continue;
                }
                for ($i = 0; $i < $dim; $i++) {
                    $sum[$i] += (float) $token[$i];
                }
                $count++;
            }

            if ($count === 0) {
                return null;
            }

            return array_map(fn ($v) => $v / $count, $sum);
        }

        return null;
    }
}
