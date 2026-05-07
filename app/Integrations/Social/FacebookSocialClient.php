<?php

namespace App\Integrations\Social;

use App\Contracts\SocialPlatformClientInterface;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Throwable;

class FacebookSocialClient implements SocialPlatformClientInterface
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
        ]);
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
            $endpoint = "https://graph.facebook.com/{$graphVersion}/{$account->provider_user_id}/feed";

            $response = $this->httpClient->post($endpoint, [
                'form_params' => [
                    'message' => $post->caption,
                    'access_token' => $account->access_token,
                ],
            ]);

            $payload = json_decode($response->getBody()->getContents(), true);

            if (! isset($payload['id'])) {
                throw new RuntimeException('Facebook API did not return a post id.');
            }

            return [
                'provider_post_id' => $payload['id'],
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
}
