<?php

namespace App\Integrations\Social;

use App\Contracts\SocialPlatformClientInterface;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class FacebookSocialClient implements SocialPlatformClientInterface
{
    private Client $httpClient;

    public function __construct()
    {
        $config = ['timeout' => 30];

        if (class_exists(CaBundle::class)) {
            $config['verify'] = CaBundle::getBundledCaBundlePath();
        }

        $this->httpClient = new Client($config);
    }

    public function platform(): string
    {
        return 'facebook';
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        try {
            if ($account->status !== 'active' || ! $account->access_token) {
                throw new RuntimeException('Facebook account is not properly connected.');
            }

            if (! $account->provider_user_id) {
                throw new RuntimeException('Facebook page id is missing from the connected account.');
            }

            $graphVersion = config('services.facebook.graph_version', 'v22.0');
            $baseUrl = "https://graph.facebook.com/{$graphVersion}/{$account->provider_user_id}";

            $imageStream = $this->openImageStream($post);
            $imageUrl = $post->media_payload['image_url'] ?? $post->media_payload['image'] ?? null;

            if ($imageStream !== null) {
                $response = $this->httpClient->post($baseUrl.'/photos', [
                    'multipart' => [
                        ['name' => 'message', 'contents' => (string) $post->caption],
                        ['name' => 'access_token', 'contents' => $account->access_token],
                        ['name' => 'source', 'contents' => $imageStream['resource'], 'filename' => $imageStream['filename']],
                    ],
                ]);
            } elseif ($imageUrl && ! $this->looksLikePrivateUrl($imageUrl)) {
                $response = $this->httpClient->post($baseUrl.'/photos', [
                    'form_params' => [
                        'message' => $post->caption,
                        'url' => $imageUrl,
                        'access_token' => $account->access_token,
                    ],
                ]);
            } else {
                $response = $this->httpClient->post($baseUrl.'/feed', [
                    'form_params' => [
                        'message' => $post->caption,
                        'access_token' => $account->access_token,
                    ],
                ]);
            }

            $payload = json_decode($response->getBody()->getContents(), true);

            $providerId = $payload['post_id'] ?? $payload['id'] ?? null;

            if (! $providerId) {
                throw new RuntimeException('Facebook API did not return a post id.');
            }

            return [
                'provider_post_id' => $providerId,
                'response' => [
                    'platform' => 'facebook',
                    'mode' => 'live',
                    'account' => $account->provider_account_name,
                    'caption' => $post->caption,
                    'response' => $payload,
                ],
            ];
        } catch (GuzzleException $exception) {
            throw new RuntimeException('Facebook API error: '.$exception->getMessage());
        } catch (Throwable $exception) {
            throw new RuntimeException('Facebook publish failed: '.$exception->getMessage());
        }
    }

    private function openImageStream(SocialPost $post): ?array
    {
        $path = $post->media_payload['image_path'] ?? null;
        $disk = $post->media_payload['image_disk'] ?? null;

        if (! $path || ! $disk) {
            return null;
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return null;
        }

        $resource = $storage->readStream($path);

        if (! is_resource($resource)) {
            return null;
        }

        return [
            'resource' => $resource,
            'filename' => basename($path),
        ];
    }

    private function looksLikePrivateUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            return true;
        }

        return in_array(strtolower($host), ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true);
    }
}
