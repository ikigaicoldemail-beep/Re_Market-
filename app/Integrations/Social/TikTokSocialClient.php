<?php

namespace App\Integrations\Social;

use App\Contracts\SocialPlatformClientInterface;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Support\CircuitBreaker;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;

class TikTokSocialClient implements SocialPlatformClientInterface
{
    private const API_BASE_URL = 'https://open.tiktokapis.com/v1';
    private Client $httpClient;
    private CircuitBreaker $breaker;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
        ]);
        $this->breaker = new CircuitBreaker('tiktok-api', failureThreshold: 5, cooldownSeconds: 60);
    }

    public function platform(): string
    {
        return 'tiktok';
    }

    /**
     * Publish a product to TikTok
     * Requires access_token in social_account
     */
    public function publish(SocialAccount $account, SocialPost $post): array
    {
        return $this->breaker->call(fn () => $this->doPublish($account, $post));
    }

    private function doPublish(SocialAccount $account, SocialPost $post): array
    {
        try {
            // Verify account is active
            if ($account->status !== 'active' || ! $account->access_token) {
                throw new \Exception('TikTok account not properly connected');
            }

            // Check if token needs refresh
            if ($account->token_expires_at && $account->token_expires_at->isPast()) {
                $this->refreshAccessToken($account);
            }

            // Initialize video upload
            $uploadResponse = $this->initializeVideoUpload($account, $post);
            
            if (! isset($uploadResponse['data']['upload_url'])) {
                throw new \Exception('Failed to get upload URL from TikTok');
            }

            $uploadId = $uploadResponse['data']['upload_id'];

            // Create video post after upload
            $postResponse = $this->createVideoPost($account, $uploadId, $post);

            return [
                'provider_post_id' => $postResponse['data']['video_id'] ?? 'tt_'.uniqid(),
                'response' => [
                    'platform' => 'tiktok',
                    'mode' => 'live',
                    'account' => $account->provider_account_name,
                    'caption' => $post->caption,
                    'video_id' => $postResponse['data']['video_id'] ?? null,
                    'status' => 'posted',
                ],
            ];
        } catch (Throwable $e) {
            throw new \Exception("TikTok API Error: {$e->getMessage()}");
        }
    }

    /**
     * Initialize video upload to TikTok
     */
    private function initializeVideoUpload(SocialAccount $account, SocialPost $post): array
    {
        $payload = [
            'source_info' => [
                'source' => 'EXTERNAL_SOURCE',
                'platform' => 'web',
            ],
            'video_info' => [
                'description' => $post->caption,
                'title' => $post->media_payload['title'] ?? 'Product from marketplace',
                'privacy_level' => 'PUBLIC_TO_EVERYONE',
            ],
        ];

        try {
            $response = $this->httpClient->post(
                self::API_BASE_URL.'/video/upload/init/',
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$account->access_token,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to initialize TikTok upload: '.$e->getMessage());
        }
    }

    /**
     * Create the actual video post after upload
     */
    private function createVideoPost(SocialAccount $account, string $uploadId, SocialPost $post): array
    {
        $payload = [
            'data' => [
                'upload_id' => $uploadId,
                'video_info' => [
                    'description' => $post->caption,
                ],
                'post_info' => [
                    'disable_comment' => false,
                    'disable_duet' => false,
                    'disable_stitch' => false,
                ],
            ],
        ];

        try {
            $response = $this->httpClient->post(
                self::API_BASE_URL.'/video/publish/',
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$account->access_token,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to create TikTok post: '.$e->getMessage());
        }
    }

    /**
     * Refresh access token if expired
     */
    private function refreshAccessToken(SocialAccount $account): void
    {
        if (! $account->refresh_token) {
            throw new \Exception('No refresh token available for TikTok account');
        }

        try {
            $response = $this->httpClient->post(
                'https://open.tiktokapis.com/v1/oauth/token/refresh/',
                [
                    'form_params' => [
                        'client_key' => config('services.tiktok.client_key'),
                        'client_secret' => config('services.tiktok.client_secret'),
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $account->refresh_token,
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            // Update account with new token
            $account->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
                'token_expires_at' => now()->addSeconds($data['expires_in']),
            ]);
        } catch (GuzzleException $e) {
            throw new \Exception('Failed to refresh TikTok token: '.$e->getMessage());
        }
    }
}
