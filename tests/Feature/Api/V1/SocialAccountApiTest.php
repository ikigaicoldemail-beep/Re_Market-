<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialAccountApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_account_connection_rejects_oversized_tokens(): void
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/social/accounts', [
                'platform' => 'facebook',
                'provider_user_id' => 'provider-user-123',
                'provider_account_name' => 'Demo Account',
                'access_token' => str_repeat('a', 4097),
                'refresh_token' => str_repeat('b', 4097),
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['access_token', 'refresh_token']);
    }

    public function test_social_account_tokens_are_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($user);

        Http::fake([
            'graph.facebook.com/*/debug_token*' => Http::response([
                'data' => [
                    'is_valid' => true,
                    'type' => 'PAGE',
                    'profile_id' => 'provider-user-123',
                    'scopes' => ['pages_manage_posts'],
                ],
            ]),
        ]);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/social/accounts', [
                'platform' => 'facebook',
                'provider_user_id' => 'provider-user-123',
                'provider_account_name' => 'Demo Account',
                'access_token' => 'plain-access-token',
                'refresh_token' => 'plain-refresh-token',
            ])
            ->assertCreated();

        $account = $user->socialAccounts()->firstOrFail();

        $this->assertNotSame('plain-access-token', $account->getRawOriginal('access_token'));
        $this->assertNotSame('plain-refresh-token', $account->getRawOriginal('refresh_token'));
        $this->assertSame('plain-access-token', Crypt::decryptString($account->getRawOriginal('access_token')));
        $this->assertSame('plain-refresh-token', Crypt::decryptString($account->getRawOriginal('refresh_token')));
        $this->assertSame('plain-access-token', $account->access_token);
        $this->assertSame('plain-refresh-token', $account->refresh_token);
    }
}
