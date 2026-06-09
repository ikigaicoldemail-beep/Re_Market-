<?php

namespace Tests\Unit;

use App\Integrations\Social\FacebookSocialClient;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use RuntimeException;
use Tests\TestCase;

class ProductionGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        parent::tearDown();
    }

    public function test_placeholder_social_client_is_blocked_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);

        (new FacebookSocialClient)->publish(new SocialAccount([
            'provider_account_name' => 'Example Page',
        ]), new SocialPost([
            'caption' => 'Example caption',
        ]));
    }
}
