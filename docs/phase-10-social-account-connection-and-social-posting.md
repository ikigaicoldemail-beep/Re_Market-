# Phase 10: Social Account Connection and Social Posting

## What this phase builds

This phase adds the backend foundation for social-commerce integrations.

Implemented pieces:

- connect social account
- disconnect social account
- list connected accounts
- create social post from product data
- publish social post now
- manual product share tracking
- provider-specific posting behind interfaces
- placeholder Facebook and TikTok integration classes
- queue job for social publishing

## Main design choices

- provider-specific logic is not placed in controllers
- the backend uses a social platform manager plus client interface
- account and post state are persisted independently
- publishing is designed for queues from the start
- placeholder clients simulate provider publishing until real OAuth/API work is wired

## Schema added

New tables:

- `social_accounts`
- `social_posts`
- `shared_products`

## Main implementation files

### Contracts and integrations

- `app/Contracts/SocialPlatformClientInterface.php`
- `app/Integrations/Social/FacebookSocialClient.php`
- `app/Integrations/Social/TikTokSocialClient.php`
- `app/Services/SocialPlatformManager.php`

### Services

- `app/Services/SocialAccountService.php`
- `app/Services/SocialPostingService.php`

### Job

- `app/Jobs/PublishSocialPostJob.php`

### Controllers

- `app/Http/Controllers/Api/V1/SocialAccountController.php`
- `app/Http/Controllers/Api/V1/SocialPostController.php`

### Resources

- `app/Http/Resources/SocialAccountResource.php`
- `app/Http/Resources/SocialPostResource.php`
- `app/Http/Resources/SharedProductResource.php`

## Endpoints added

### Social accounts

- `GET /api/v1/social/accounts`
- `POST /api/v1/social/accounts`
- `DELETE /api/v1/social/accounts/{socialAccount}`

### Social posts

- `GET /api/v1/social/posts`
- `POST /api/v1/social/posts`
- `GET /api/v1/social/posts/{socialPost}`
- `POST /api/v1/social/posts/{socialPost}/publish`

### Manual share tracking

- `POST /api/v1/products/share`

## How the current flow works

### Connect account

The current implementation stores connected account metadata and encrypted tokens in `social_accounts`.

This is backend-ready for OAuth, but the actual OAuth redirect/callback flow is not implemented yet.

### Create social post

The user provides:

- `platform`
- `product_id`
- `social_account_id`
- optional `caption`
- optional `publish_now`

The backend creates a `social_posts` record using product metadata.

### Publish social post

Publishing can happen:

- immediately when `publish_now=true`
- manually by calling the publish endpoint
- through queue job execution

The post status transitions through:

- `draft`
- `queued`
- `processing`
- `posted`
- `failed`

### Manual share

The share endpoint records a user-driven share action in `shared_products`.

This is useful for analytics, audits, and social activity history even if the share happens on the frontend client.

## Required credentials for real providers

### Facebook

For a real Facebook integration you would typically need:

- Facebook App ID
- Facebook App Secret
- redirect URI
- required Graph API permissions/scopes
- possibly page access tokens depending on posting target

### TikTok

For a real TikTok integration you would typically need:

- TikTok client ID
- TikTok client secret
- redirect URI
- approved scopes for content publishing

## Current config added

### Services config

- `config/services.php`

Added sections:

- `facebook`
- `tiktok`

### Environment variables

- `FACEBOOK_CLIENT_ID`
- `FACEBOOK_CLIENT_SECRET`
- `FACEBOOK_REDIRECT_URI`
- `FACEBOOK_GRAPH_VERSION`
- `TIKTOK_CLIENT_ID`
- `TIKTOK_CLIENT_SECRET`
- `TIKTOK_REDIRECT_URI`

## Important limits of this phase

- OAuth redirect/callback flow is not implemented yet
- real provider HTTP API calls are not implemented yet
- provider webhooks are not implemented yet
- scheduled publishing is not implemented yet in this phase

Those pieces belong to the next scheduling/integration hardening phase.

## Why this is still production-oriented

Even though Facebook and TikTok are placeholders right now, the architecture is correct for production:

- contracts isolate provider behavior
- account ownership is enforced
- post intent and publish results are stored durably
- tokens are encrypted before storage
- publishing is queue-ready

## Outcome

At the end of Phase 10, the backend has a proper social integration architecture for connected accounts and product-driven social posting, with Facebook/TikTok placeholders ready to be replaced by real OAuth/API clients in a later pass.
