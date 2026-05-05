# Production Scope and Readiness Roadmap

## Source Scope From Image

The handwritten scope describes a second-hand marketplace with these features:

1. Chat system
2. AI filter to find similar products when a user uploads images
3. Full e-commerce features
4. Auto-post to Facebook, TikTok, and any connected social platform
5. Schedule posts and manage post timing, including post now or post later
6. User-created product pages/store pages
7. Users can share products to social platforms

This scope is valid for the project. The only adjustment is that "full e-commerce" and "everything that can connect" are too broad for one production release, so they must be split into MVP, production release, and later expansion.

## Product Definition

This project is an API backend for a second-hand social-commerce marketplace.

It supports:

- buyers browsing, saving, chatting about, and ordering second-hand products
- sellers creating store pages and product listings
- private buyer-seller chat
- image-based product similarity search
- social account connection and product sharing/posting workflows
- scheduled social posting
- admin moderation and operational controls

It does not include:

- frontend web or mobile UI
- an admin dashboard UI
- warehouse management
- point-of-sale features
- advanced accounting
- tax filing
- courier-provider integration unless added in a later phase

## Production Release Scope

### 1. Identity, Access, and Admin Control

Required for production:

- JWT login, logout, and password reset
- role-based access control for `user`, `seller`, and `admin`
- admin-only management APIs for users, stores, products, and orders
- active/suspended account enforcement
- no public self-registration as admin without a configured admin key
- activity logging for sensitive write actions

Current status:

- Mostly implemented.
- Admin APIs now exist under `/api/v1/admin/*`.
- Remaining work: define a stronger admin creation process for production, preferably seed/console-only or identity-provider managed.

### 2. Store and Product Pages

Required for production:

- sellers can create and update store pages
- sellers can create, update, publish, archive, and delete products
- products support images, price, stock, category, condition, location, status, and visibility
- public store pages only show public published products
- admin can moderate stores and products

Current status:

- Implemented as API backend.
- Remaining work: image optimization, malware scanning if needed, moderation workflow polish, and category-management endpoints.

### 3. Chat System

Required for production:

- users can start buyer-seller conversations
- conversations can be linked to products
- users can send messages only in conversations they belong to
- unread counts and seen state are tracked
- admin/support moderation path is defined for abuse reports

Current status:

- REST-based chat is implemented.
- Remaining work: real-time chat, notifications, reporting/blocking, retention rules, and moderation tooling.

### 4. AI Similar Product Search

Required for production:

- users can upload an image for similarity search
- product images generate embeddings asynchronously
- search returns ranked similar products
- requests and results are logged for audit/debugging
- provider errors are handled safely

Current status:

- API and service structure exist.
- Current embedding client is a deterministic fake provider, suitable for development only.
- The fake embedding client is blocked in production.
- Remaining work: integrate a real AI embedding provider or vector database, tune ranking, monitor cost/latency, and add abuse controls.

### 5. Full E-Commerce

Required for production:

- cart management
- wishlist/favorites
- checkout
- order creation
- order history
- address management
- stock reservation or atomic inventory decrement
- payment gateway integration
- payment webhooks with signature verification
- refunds and payment reconciliation
- order cancellation and fulfillment status flow

Current status:

- Cart, wishlist, checkout, orders, addresses, and payment status records exist.
- Payment is currently placeholder status tracking, not a live payment gateway.
- Checkout now locks cart and product rows, rejects stale cart prices, and prevents sellers from checking out their own products.
- Remaining work: real payment provider, webhook verification, refunds, fulfillment updates, taxes/shipping if required.

### 6. Social Account Connection and Product Sharing

Required for production:

- users can connect social accounts using provider-approved OAuth flows
- users can create social posts from products
- users can publish posts now
- provider tokens are encrypted and scrubbed on disconnect
- provider errors are stored and visible to users/admins

Current status:

- Social account/posting model and API structure exist.
- Tokens are encrypted.
- Facebook and TikTok clients are placeholders.
- Placeholder Facebook and TikTok publishing clients are blocked in production.
- Remaining work: real OAuth flows, real provider clients, provider permissions review, token refresh, and webhook/result reconciliation if supported.

### 7. Scheduled Posting

Required for production:

- users can schedule posts for future publishing
- users can update or cancel scheduled posts
- scheduler dispatches due posts reliably
- queue retries and failure states are visible
- duplicate posting is prevented

Current status:

- Implemented with scheduler and queue jobs.
- Remaining work: idempotency hardening, retry policies per provider, alerting for repeated failures, and timezone UX rules.

## Out Of Scope For First Production Release

These are valid future features but should not block the first stable backend release:

- real-time chat with websockets
- recommendations beyond image similarity
- multi-currency checkout
- tax/VAT automation
- shipping carrier label generation
- dispute resolution center
- seller subscription plans
- advertising and boosting
- advanced analytics dashboard
- mobile push notifications
- content moderation AI
- cross-posting to every social platform

## Production Blockers

The project should not be treated as production-ready until these are complete:

1. Replace placeholder payment status tracking with a real payment provider integration.
2. Add payment webhook signature verification and idempotent reconciliation.
3. Add explicit inventory reservation expiry if checkout should hold stock before payment completion.
4. Replace fake AI embedding client with a real provider.
5. Replace placeholder Facebook/TikTok clients with real OAuth and publishing integrations.
6. Remove real-looking secrets from example/config files and rotate any exposed credentials.
7. Harden Docker/deployment for production instead of using `php artisan serve`.
8. Ensure test suite passes in CI and the Docker runtime.
9. Add monitoring for queues, scheduler, failed jobs, errors, and payment/social webhook failures.
10. Finalize admin operation policy, audit retention, and incident response process.

## Production Best Practices

### API and Security

- All sensitive routes must require authentication.
- Admin routes must require explicit admin role checks.
- Suspended or inactive accounts must be blocked even if they already have a token.
- Use Form Request validation or dedicated request classes for complex endpoints.
- Never trust frontend role or status values.
- Use policies for ownership checks.
- Apply rate limits to auth, public search, chat, AI, and social routes.

### Data Integrity

- Use integer minor units for money.
- Use database transactions for checkout and order creation.
- Use atomic stock updates or row locking for inventory.
- Keep order items as snapshots of product title, price, image, and condition at checkout time.
- Keep payment attempts separate from orders.
- Make external callback handling idempotent.

### Integrations

- Keep provider logic behind contracts/interfaces.
- Store provider tokens encrypted.
- Refresh/revoke tokens safely.
- Queue slow provider calls.
- Record provider request status and failure reasons.
- Do not mark external actions successful until the provider confirms them.

### Operations

- Use production web serving such as Nginx plus PHP-FPM, Laravel Octane, or platform-managed PHP runtime.
- Use Redis or another production-grade queue backend when traffic grows.
- Run queue workers and scheduler as separate processes.
- Configure structured logs and error tracking.
- Monitor failed jobs and webhook failures.
- Use object storage such as S3 for product images in production.
- Use secret management rather than committed environment values.

## Recommended Release Plan

### Release 1: Safe Marketplace Core

- Auth, roles, profiles, stores, products, image uploads
- Public browse/search/filter
- Wishlist, cart, checkout, order history
- REST chat
- Admin moderation APIs
- Docker local development
- CI tests passing

### Release 2: Production Commerce

- Inventory locking/reservation
- Real payment gateway
- Payment webhooks
- Refunds and cancellation flow
- Seller fulfillment status updates
- Operational monitoring

### Release 3: Real Social Commerce

- Real Facebook OAuth and publishing
- Real TikTok OAuth and publishing, depending on provider access
- Token refresh and disconnect hardening
- Scheduled post idempotency and retry tuning

### Release 4: Real AI Similarity

- Real embedding provider or vector search
- Product image embedding backfill
- Similarity ranking tuning
- AI cost controls and abuse prevention

### Release 5: Scale and UX Enhancements

- Real-time chat
- Notifications
- Advanced moderation
- Analytics
- Promotions and seller tools

## Final Scope Decision

The image scope is in scope for the overall product vision.

For a real production project, it must be delivered in phases. The backend currently covers many MVP endpoints, but live payments, production-grade social publishing, production AI similarity, and inventory race protection are still required before launch.
