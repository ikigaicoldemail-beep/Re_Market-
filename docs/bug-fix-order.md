# Bug Fix Order

Generated during project audit on 2026-06-02.

This document orders the known bugs and production blockers by the safest fix sequence. Fix the items from top to bottom unless a later item becomes urgent because of a release requirement.

## Current Verification

- `php artisan route:list` loads successfully.
- `php artisan migrate:status` shows all migrations as ran.
- `php artisan test` passes with 19 tests and 62 assertions.
- Test coverage is too small for the project size, so passing tests do not prove production readiness.
- Current dirty file: `app/Models/User.php`.

## Fix Order

### 1. Restore the `User::scheduledPosts()` relationship

Status: bug introduced in current worktree.

Files:

- `app/Models/User.php`

Problem:

- The `scheduledPosts()` relationship was removed.
- `use App\Models\socal\ScheduledPost;` is invalid and unused.
- `use Illuminate\Database\Eloquent\Relations\HasManyThrough;` is unused after the relationship removal.

Risk:

- Any code, resource, or future feature that expects `$user->scheduledPosts()` will fail.
- The typo namespace can confuse static analysis and future edits.

Expected fix:

- Remove the invalid `App\Models\socal\ScheduledPost` import.
- Either restore `scheduledPosts(): HasManyThrough` correctly or remove both imports if the relationship is genuinely not needed.
- If restored, use `App\Models\ScheduledPost`.

Verification:

- `php artisan test`
- `php artisan route:list`

### 2. Fix checkout stock handling for variants

Status: confirmed bug.

Files:

- `app/Services/CheckoutService.php`
- Add or update tests in `tests/Feature/Api/V1/CheckoutApiTest.php`

Problem:

- Checkout checks variant stock when a cart item has a variant.
- It then decrements the variant stock and also decrements the parent product stock.
- The parent product can be marked `sold` even when other variants still have stock.

Risk:

- Products with variants can become unavailable incorrectly.
- Inventory can drift between parent product stock and variant stock.

Expected fix:

- Define the inventory rule:
  - If variants own stock, decrement only the variant and derive product availability from variants.
  - If parent stock is aggregate stock, enforce a clear invariant and update both consistently.
- Add a test for checkout with two variants where one variant is purchased and the other remains available.

Verification:

- `php artisan test --filter=CheckoutApiTest`
- `php artisan test`

### 3. Validate checkout payment inputs

Status: confirmed bug.

Files:

- `app/Http/Requests/Checkout/CheckoutRequest.php`
- `tests/Feature/Api/V1/CheckoutApiTest.php`

Problem:

- `provider` and `payment_method` accept any string.

Risk:

- Bad payment values can enter the database.
- Later payment reconciliation becomes harder and less trustworthy.

Expected fix:

- Add `Rule::in(...)` validation for supported providers and methods.
- For the current placeholder flow, valid values may be `manual` and `cash_on_delivery`.
- When a real gateway is added, extend the allowed values intentionally.

Verification:

- Add tests rejecting unknown provider and payment method.
- `php artisan test --filter=CheckoutApiTest`

### 4. Add real payment gateway flow

Status: production blocker.

Files:

- `app/Services/CheckoutService.php`
- `app/Http/Controllers/Api/V1/PaymentController.php`
- New `app/Services/PaymentService.php`
- New webhook controller and routes
- Payment tests

Problem:

- Checkout creates a `manual` pending payment record only.
- No money movement happens.
- No initiate-payment endpoint exists.
- No webhook callback handler exists.

Risk:

- Orders can be created without confirmed payment.
- Admin payment status can be manually changed but cannot be reconciled with a provider.

Expected fix:

- Choose a payment provider: Stripe, ABA Pay, PayPal, or another required gateway.
- Create payment intent/initiation flow.
- Store provider reference and raw safe provider payload.
- Add webhook endpoint with signature verification.
- Make webhook handling idempotent.
- Update order and payment statuses only after provider confirmation.

Verification:

- Feature tests for checkout, payment initiation, webhook success, webhook failure, duplicate webhook.
- Manual sandbox provider test.

### 5. Add refund, cancellation, and payment reconciliation rules

Status: production blocker.

Files:

- `app/Http/Controllers/Api/V1/OrderController.php`
- `app/Http/Controllers/Api/V1/AdminController.php`
- Payment service and webhook files from item 4
- New refund/dispute models if needed

Problem:

- Orders can be cancelled only before paid status.
- There is no refund flow.
- There is no dispute mechanism.
- Admin can set payment status directly, but provider reconciliation is missing.

Risk:

- Paid order failures cannot be handled safely.
- Accounting and customer support workflows are incomplete.

Expected fix:

- Add refund model or payment refund record.
- Add provider refund API integration.
- Record refund status transitions.
- Separate fulfillment status from payment status.

Verification:

- Tests for buyer cancellation before payment.
- Tests for paid order refund request/admin refund.
- Provider sandbox refund test.

### 6. Implement shipping and discount logic or remove them from checkout totals

Status: production gap.

Files:

- `app/Services/CheckoutService.php`
- `app/Services/CartService.php`
- New coupon/shipping services if needed

Problem:

- `discount_amount` is hardcoded to `0`.
- `shipping_amount` is hardcoded to `0`.
- Database columns exist, but no real calculation exists.

Risk:

- Checkout totals do not reflect real business rules.
- Future payment totals can mismatch order totals if shipping/discount is added later without care.

Expected fix:

- Add a simple production-ready rule first:
  - flat shipping by city/country, or
  - no shipping feature with explicit pickup/local delivery wording.
- Add coupon table/service only if coupons are in scope.

Verification:

- Tests for checkout total calculation.
- Tests for invalid coupon or unsupported shipping destination if implemented.

### 7. Enforce email verification before marketplace actions

Status: production security/account gap.

Files:

- `app/Services/AuthService.php`
- `app/Http/Middleware/EnsureAccountIsActive.php`
- Routes or middleware groups as needed

Problem:

- Users are registered as `active`.
- `email_verified_at` exists but is not enforced for marketplace operations.

Risk:

- Spam accounts can create stores, chat, checkout, and post content without verified ownership of email.

Expected fix:

- Decide which actions require verification.
- Add middleware or checks for store creation, product creation, checkout, social connection, and possibly chat.
- Add email verification notification flow if missing.

Verification:

- Tests for unverified user blocked from protected marketplace actions.
- Tests for verified user allowed.

### 8. Fix review eligibility logic

Status: confirmed behavior mismatch.

Files:

- `app/Http/Controllers/Api/V1/ProductController.php`
- `app/Http/Controllers/Api/V1/ProductReviewController.php`

Problem:

- `reviewEligibility()` returns eligible when the user has a conversation about the product.
- Actual review creation requires a paid order.

Risk:

- Frontend can show a review form that fails on submit.

Expected fix:

- Make eligibility use the same paid-order rule as review creation.
- Keep conversation-based language only if the product decision is intentionally changed.

Verification:

- Tests for no paid order, paid order, self-review, existing review.

### 9. Make product search database-safe

Status: portability bug.

Files:

- `app/Services/ProductService.php`
- `database/migrations/2026_05_19_150000_add_search_indexes_to_products.php`

Problem:

- Public product search uses MySQL `MATCH ... AGAINST`.
- The default test database is SQLite.
- The code can break in SQLite/Postgres environments.

Risk:

- Local or non-MySQL deployments can fail for normal search queries.

Expected fix:

- Branch by database driver:
  - MySQL: use full-text search.
  - Other drivers: fall back to `LIKE` search or a portable search service.
- Add tests for search with a 3+ character term in the test SQLite environment.

Verification:

- `php artisan test --filter=ProductApiTest`
- Manual `GET /api/v1/products?search=phone`

### 10. Make admin report ordering database-safe

Status: portability bug.

Files:

- `app/Http/Controllers/Api/V1/AdminController.php`

Problem:

- Admin reports use MySQL `FIELD(status, 'open')`.

Risk:

- Admin reports fail in SQLite/Postgres.

Expected fix:

- Use a driver-specific expression or portable `CASE WHEN status = 'open' THEN 0 ELSE 1 END`.

Verification:

- Add admin reports test in SQLite.
- `php artisan test --filter=AdminApiTest`

### 11. Encrypt refreshed TikTok tokens consistently

Status: security bug.

Files:

- `app/Integrations/Social/TikTokSocialClient.php`
- `app/Services/SocialAccountService.php`
- `app/Models/SocialAccount.php`

Problem:

- Manual/social connect encrypts tokens.
- TikTok refresh writes refreshed tokens directly.
- The model accessor can still read plain tokens, but storage becomes inconsistent and weaker.

Risk:

- Secret handling becomes inconsistent.
- Future code may assume all stored tokens are encrypted.

Expected fix:

- Centralize token storage through a method that always encrypts before saving.
- Avoid direct `env()` calls in the client; prefer `config('services.tiktok...')`.

Verification:

- Unit test token refresh persistence.
- Assert stored database value is encrypted, not plain token.

### 12. Add max quantity validation for cart updates

Status: validation gap.

Files:

- `app/Http/Requests/Cart/AddToCartRequest.php`
- `app/Http/Requests/Cart/UpdateCartItemRequest.php`

Problem:

- Quantity has `min:1` but no upper bound.

Risk:

- Extremely large input reaches service/database logic.
- Error handling depends on later stock checks.

Expected fix:

- Add a practical max, such as `max:999` or a config-driven value.

Verification:

- Tests for too-large add and update quantities.

### 13. Require positive product prices for paid marketplace listings

Status: business-rule gap.

Files:

- `app/Http/Requests/Product/StoreProductRequest.php`
- `app/Http/Requests/Product/UpdateProductRequest.php`

Problem:

- Product and variant prices allow `min:0`.

Risk:

- Free listings can break checkout/payment expectations if the marketplace is not intended to support free products.

Expected fix:

- If free listings are not supported, use `min:1`.
- If free listings are supported, define payment and checkout rules for zero-total orders.

Verification:

- Tests for zero price rejected or accepted according to product decision.

### 14. Strengthen auth input validation

Status: security hardening.

Files:

- `app/Http/Requests/Auth/RegisterRequest.php`
- `app/Http/Requests/Auth/ResetPasswordRequest.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Social/ConnectSocialAccountRequest.php`

Problem:

- Password rules require letters and numbers, but not symbols.
- Login email/password have no max length.
- Social token fields have no max length.

Risk:

- Weak passwords.
- Unbounded input sizes on sensitive endpoints.

Expected fix:

- Add symbol requirement or stronger password default.
- Add max lengths to login and token fields.

Verification:

- Auth request tests.
- Social connect request tests.

### 15. Add missing feature tests for low-coverage modules

Status: production blocker for confidence.

Files:

- New tests under `tests/Feature/Api/V1`

Problem:

- Only 19 tests exist.
- Cart, order, notification, wishlist, review, upload, AI, social, scheduler, and several policies are under-tested.

Risk:

- Regressions can ship while tests stay green.

Expected fix:

- Add focused tests in this order:
  1. Cart add/update/remove/clear, including variants.
  2. Checkout and order status transitions.
  3. Product reviews and eligibility.
  4. Wishlist.
  5. File uploads.
  6. Admin reports and moderation.
  7. Social account/post/schedule.
  8. AI similarity search.

Verification:

- `php artisan test`
- Add CI command later if not present.

## Production Integration Work After Bug Fixes

These are not all code bugs, but they block production launch:

1. Replace fake AI embedding provider with real provider or vector database.
2. Complete Facebook and TikTok provider approval, OAuth, posting, token refresh, and failure reconciliation.
3. Add monitoring for failed jobs, scheduler, payment webhooks, social posting, and AI provider failures.
4. Use production web serving instead of `php artisan serve`.
5. Move production media storage to object storage such as S3.
6. Rotate any credentials that were ever shared or committed.
7. Complete API documentation for commerce, chat, social, AI, and admin.

## Suggested First Sprint

Fix in this exact order:

1. `User.php` relationship/import regression.
2. Checkout variant inventory bug.
3. Checkout payment validation.
4. Product search portability.
5. Admin report ordering portability.
6. Review eligibility mismatch.
7. Cart quantity max validation.
8. Add tests for every fix above.

After that, start the payment gateway work.
