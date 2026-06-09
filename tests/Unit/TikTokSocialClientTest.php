<?php

namespace Tests\Unit;

use App\Integrations\Social\TikTokSocialClient;
use App\Models\SocialAccount;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use ReflectionClass;
use Tests\TestCase;

class TikTokSocialClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_access_token_stores_refreshed_tokens_encrypted(): void
    {
        config([
            'services.tiktok.client_key' => 'test-client-key',
            'services.tiktok.client_secret' => 'test-client-secret',
        ]);

        $account = SocialAccount::create([
            'user_id' => User::factory()->create()->id,
            'platform' => 'tiktok',
            'provider_user_id' => 'tiktok-user-123',
            'provider_account_name' => 'TikTok User',
            'access_token' => 'old-access-token',
            'refresh_token' => 'old-refresh-token',
            'token_expires_at' => now()->subMinute(),
            'status' => 'active',
        ]);

        $client = new TikTokSocialClient();
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'new-access-token',
                'refresh_token' => 'new-refresh-token',
                'expires_in' => 3600,
            ], JSON_THROW_ON_ERROR)),
        ]);

        $reflection = new ReflectionClass($client);
        $httpProperty = $reflection->getProperty('httpClient');
        $httpProperty->setAccessible(true);
        $httpProperty->setValue($client, new Client([
            'handler' => HandlerStack::create($mock),
        ]));

        $method = $reflection->getMethod('refreshAccessToken');
        $method->setAccessible(true);
        $method->invoke($client, $account);

        $account->refresh();

        $this->assertNotSame('new-access-token', $account->getRawOriginal('access_token'));
        $this->assertNotSame('new-refresh-token', $account->getRawOriginal('refresh_token'));
        $this->assertSame('new-access-token', Crypt::decryptString($account->getRawOriginal('access_token')));
        $this->assertSame('new-refresh-token', Crypt::decryptString($account->getRawOriginal('refresh_token')));
        $this->assertSame('new-access-token', $account->access_token);
        $this->assertSame('new-refresh-token', $account->refresh_token);
    }
}
