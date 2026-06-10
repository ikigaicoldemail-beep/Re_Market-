<?php

namespace App\Providers;

use App\Contracts\AiImageEmbeddingClientInterface;
use App\Integrations\Ai\FakeImageEmbeddingClient;
use App\Integrations\Ai\HuggingFaceImageEmbeddingClient;
use App\Integrations\Social\FacebookSocialClient;
use App\Integrations\Social\TikTokSocialClient;
use App\Services\SocialPlatformManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiImageEmbeddingClientInterface::class, function () {
            $provider = config('services.ai_similarity.provider', 'fake-image-embedding');

            return match ($provider) {
                'huggingface-clip', 'huggingface' => $this->app->make(HuggingFaceImageEmbeddingClient::class),
                default => $this->app->make(FakeImageEmbeddingClient::class),
            };
        });

        $this->app->singleton(FacebookSocialClient::class);
        $this->app->singleton(TikTokSocialClient::class);
        $this->app->tag([FacebookSocialClient::class, TikTokSocialClient::class], 'social.platform.clients');
        $this->app->singleton(SocialPlatformManager::class, function ($app) {
            return new SocialPlatformManager($app->tagged('social.platform.clients'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The default /broadcasting/auth route ships with ['web', 'auth']
        // middleware, which doesn't fit our JWT-only stack. Re-register it
        // under the JWT guard so Echo private-channel auth works.
        Broadcast::routes(['middleware' => ['auth:api']]);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute((int) env('API_RATE_LIMIT', 120))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute((int) env('AUTH_RATE_LIMIT', 20))
                ->by($request->ip());
        });

        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute((int) env('CHAT_RATE_LIMIT', 60))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('public-search', function (Request $request) {
            return Limit::perMinute((int) env('PUBLIC_SEARCH_RATE_LIMIT', 90))
                ->by($request->ip());
        });

        RateLimiter::for('social', function (Request $request) {
            return Limit::perMinute((int) env('SOCIAL_RATE_LIMIT', 30))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute((int) env('AI_RATE_LIMIT', 20))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('report-submit', function (Request $request) {
            // Spam-prevention: a user can submit at most N report submissions per hour.
            return Limit::perHour((int) env('REPORT_RATE_LIMIT', 5))
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
