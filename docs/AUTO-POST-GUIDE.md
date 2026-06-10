# Auto-Post to Social Media - Implementation Guide

## Overview

Auto-posting allows sellers to automatically share their products to Facebook/TikTok when creating a product. This guide explains what you need and how to implement it.

---

## What You Need ✅

### 1. **Database Setup** (Already Done)
- ✅ `products` table with `auto_post` field
- ✅ `social_accounts` table - stores connected accounts
- ✅ `social_posts` table - stores created posts
- ✅ `jobs` table - for queue processing

### 2. **Queue System** (Already Done)
- ✅ Laravel Queue configured (database driver)
- ✅ Queue worker running in Docker
- ✅ Job classes: `PublishSocialPostJob`, `PublishScheduledPostJob`

### 3. **Social Platform Clients** (Already Done)
- ✅ `FacebookSocialClient` - posts to Facebook
- ✅ `TikTokSocialClient` - posts to TikTok
- ✅ `SocialPlatformManager` - handles provider routing

### 4. **Services** (Already Done)
- ✅ `SocialPostingService` - creates and publishes posts
- ✅ `SocialAccountService` - manages account connections

### 5. **Controllers** (Already Done)
- ✅ `SocialAccountController` - connect/disconnect accounts
- ✅ `SocialPostController` - create/publish posts

### 6. **Missing Piece** ❌
- ❌ **Event Listener** - Not triggered when product is created with `auto_post`
- ❌ **Logic to create social posts** when `auto_post` is set

---

## Implementation Steps

### **Step 1: Create Product Event**

Create a file: `app/Events/ProductCreated.php`

```php
<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Product $product)
    {
    }
}
```

---

### **Step 2: Create Event Listener**

Create a file: `app/Listeners/AutoPostProductToSocial.php`

```php
<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialPostingService;

class AutoPostProductToSocial
{
    public function __construct(private SocialPostingService $socialPostingService)
    {
    }

    public function handle(ProductCreated $event): void
    {
        $product = $event->product;

        // Check if auto_post is set
        if (empty($product->auto_post)) {
            return;
        }

        // Get user's connected accounts
        $platforms = $product->auto_post === 'all'
            ? ['facebook', 'tiktok']
            : [$product->auto_post];

        foreach ($platforms as $platform) {
            // Find first connected account for this platform
            $account = SocialAccount::where('user_id', $product->user_id)
                ->where('platform', $platform)
                ->where('status', 'active')
                ->first();

            if (!$account) {
                continue; // Skip if no account connected
            }

            // Create social post
            $post = SocialPost::create([
                'user_id' => $product->user_id,
                'product_id' => $product->id,
                'social_account_id' => $account->id,
                'platform' => $platform,
                'caption' => "{$product->title} - {$product->currency} {$product->price_amount}",
                'media_payload' => [
                    'image' => $product->images()->first()?->path,
                    'title' => $product->title,
                    'price_amount' => $product->price_amount,
                    'currency' => $product->currency,
                ],
                'status' => 'queued',
            ]);

            // Dispatch job to publish
            \App\Jobs\PublishSocialPostJob::dispatch($post->id);
        }
    }
}
```

---

### **Step 3: Register Event & Listener**

Update: `app/Providers/EventServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Events\ProductCreated;
use App\Listeners\AutoPostProductToSocial;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProductCreated::class => [
            AutoPostProductToSocial::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
```

---

### **Step 4: Update ProductService**

In `app/Services/ProductService.php`, fire event after creating product:

```php
public function create(User $user, array $data): Product
{
    // ... existing code ...

    return DB::transaction(function () use ($user, $data) {
        $product = Product::create([
            // ... existing fields ...
            'auto_post' => $data['auto_post'] ?? null,
        ]);

        // Fire event for auto-posting
        event(new \App\Events\ProductCreated($product));

        return $product->load(['images', 'store', 'category', 'condition', 'user.profile']);
    });
}
```

---

### **Step 5: Queue Configuration**

Ensure `.env` has correct queue settings:

```env
QUEUE_CONNECTION=database
DB_QUEUE_CONNECTION=mysql
DB_QUEUE_TABLE=jobs
```

---

## How Auto-Post Works

### **Flow Diagram**

```
1. Seller creates product with auto_post="facebook"
                ↓
2. ProductCreated event fires
                ↓
3. AutoPostProductToSocial listener triggered
                ↓
4. Finds seller's Facebook account
                ↓
5. Creates SocialPost record (status=queued)
                ↓
6. Dispatches PublishSocialPostJob
                ↓
7. Queue worker processes job
                ↓
8. FacebookSocialClient::publish() called
                ↓
9. Post published to Facebook
                ↓
10. SocialPost updated (status=posted)
```

---

## Testing Auto-Post

### **In Postman**

1. **Create Product with auto_post:**

```json
{
  "store_id": 1,
  "title": "Used iPhone 13",
  "description": "Great condition",
  "category_id": 1,
  "product_condition_id": 1,
  "price_amount": 50000,
  "currency": "USD",
  "stock_quantity": 1,
  "status": "published",
  "visibility": "public",
  "auto_post": "facebook"
}
```

2. **Check Queue Jobs:**

```bash
docker compose exec app php artisan queue:work --once
```

3. **Verify Social Post Created:**

```
GET /social/posts
```

---

## Configuration Options

### **Auto-Post Values**

| Value | Action |
|-------|--------|
| `null` | No auto-posting |
| `"facebook"` | Post to Facebook only |
| `"tiktok"` | Post to TikTok only |
| `"all"` | Post to all connected platforms |

---

## Error Handling

The `PublishSocialPostJob` has built-in retry logic:

```php
public int $tries = 3;  // Retry 3 times on failure

public function failed(?Throwable $exception): void
{
    // Mark post as failed
    $post->update([
        'status' => 'failed',
        'error_message' => $exception?->getMessage(),
    ]);
}
```

---

## Monitoring

### **Check Job Status**

```bash
# List pending jobs
docker compose exec app php artisan queue:clear

# Process jobs
docker compose exec app php artisan queue:work --once

# Check failed jobs
docker compose exec app php artisan queue:failed
```

### **Database Queries**

```sql
-- Pending jobs
SELECT * FROM jobs WHERE NOT processed;

-- Social posts status
SELECT id, status, platform FROM social_posts;

-- Failed posts
SELECT * FROM social_posts WHERE status = 'failed';
```

---

## Real Social Media Integration

Currently using **placeholder clients**. To integrate real APIs:

### **Facebook**

1. Create Facebook App at developers.facebook.com
2. Get API credentials
3. Update `FacebookSocialClient.php`:

```php
public function publish(SocialAccount $account, SocialPost $post): array
{
    $client = new FacebookAds\Api();
    $client->setAccessToken($account->access_token);

    // Real API call here
    $response = $client->post(
        '/{page-id}/feed',
        ['message' => $post->caption]
    );

    return [
        'provider_post_id' => $response->id,
        'response' => $response,
    ];
}
```

### **TikTok**

1. Apply for TikTok Business API
2. Get credentials
3. Similar implementation in `TikTokSocialClient.php`

---

## Required Environment Variables

```env
# Social Media Credentials (optional for development)
FACEBOOK_APP_ID=your_app_id
FACEBOOK_APP_SECRET=your_app_secret

TIKTOK_CLIENT_KEY=your_client_key
TIKTOK_CLIENT_SECRET=your_client_secret
```

---

## Summary Checklist

- ✅ Database tables created
- ✅ Queue system running
- ✅ Social clients available
- ✅ Services implemented
- ⚠️ **NEXT: Add Event & Listener** (Steps 1-4 above)
- ⚠️ **NEXT: Test with Postman**
- ⚠️ **FUTURE: Integrate real social media APIs**

---

## References

- [Laravel Events](https://laravel.com/docs/11.x/events)
- [Laravel Queues](https://laravel.com/docs/11.x/queues)
- [Facebook Graph API](https://developers.facebook.com/docs/graph-api)
- [TikTok Business API](https://developer.tiktok.com/)
