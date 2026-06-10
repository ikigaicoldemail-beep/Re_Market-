# Local Setup And Stabilization Runbook

## Purpose

This runbook is the practical next step after the phase-by-phase backend build.

Use it to:

- configure the local environment
- migrate the database
- seed demo data
- run queues and scheduler
- execute tests
- validate the API manually

## Current blocker in this environment

During implementation, PHP execution was blocked by local Application Control, so these steps were documented but not executed here.

That means the first real stabilization step on a machine with working PHP is:

1. install/enable PHP CLI
2. confirm `php -v`
3. run the commands below in order

## 1. Environment setup

Start from:

- `.env.example`

Create:

- `.env`

Recommended local baseline:

```env
APP_NAME=MarketplaceBackend
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketplace_backend
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
MAIL_MAILER=log

AUTH_GUARD=api
JWT_SECRET=
```

## 2. Install dependencies

```bash
composer install
```

If the project does not already have an app key and JWT secret:

```bash
php artisan key:generate
php artisan jwt:secret
```

## 3. Database migration order

Because this repository began as a prototype and then evolved into a production-style schema, the first real migration run should be done on a clean database.

Recommended:

```bash
php artisan migrate:fresh
```

If you are using local demo data immediately after:

```bash
php artisan db:seed
```

## 4. Storage setup

Create the storage symlink:

```bash
php artisan storage:link
```

Product images use:

- `product-images` disk

## 5. Queue and scheduler setup

This project depends on queues for:

- AI embedding generation
- social publishing
- scheduled social publishing

Run a worker locally:

```bash
php artisan queue:work --queue=default,ai,social
```

Run scheduler locally in another terminal:

```bash
php artisan schedule:work
```

## 6. Seeded demo users

The demo seeder creates:

- seller: `seller@example.com`
- buyer: `buyer@example.com`

You may need to reset passwords manually or inspect seeded user records depending on your auth flow/testing needs.

## 7. Minimum manual validation checklist

Once migrations succeed, validate these flows in order:

### Auth

1. register
2. login
3. get `/api/v1/me`

### Seller flow

1. create store
2. create product
3. upload product images

### Commerce flow

1. create address
2. add product to cart
3. checkout
4. view order history

### Chat flow

1. create conversation
2. send message
3. read unread count

### AI flow

1. upload product image
2. confirm AI embedding job runs
3. call similarity search

### Social flow

1. connect social account
2. create social post
3. publish now
4. schedule post
5. confirm scheduled dispatcher runs

## 8. Test commands

Run full test suite:

```bash
php artisan test
```

Run only feature tests:

```bash
php artisan test --testsuite=Feature
```

Run only unit tests:

```bash
php artisan test --testsuite=Unit
```

## 9. Expected stabilization issues to check first

These are the most likely areas to verify after the first real migration/test run:

### Migration drift

Because the project evolved from early prototype migrations, verify that:

- later migration assumptions still match earlier tables
- altered columns succeed on your MySQL version
- nullable token changes for social accounts apply cleanly

### Policy resolution

Verify authorization works correctly for:

- products
- orders
- conversations
- scheduled posts
- social accounts/posts

### JWT auth behavior

Verify:

- login issues a valid token
- protected routes authenticate correctly with `Bearer` token
- logout invalidates token as expected

### Queue side effects

Verify jobs run successfully after DB commit:

- product image embedding job
- publish social post job
- publish scheduled post job

## 10. Cleanup already completed

The old prototype layer was removed:

- obsolete `Api/UserController`
- obsolete `Api/ProductController`
- obsolete `Api/MessageController`
- obsolete root `AuthController`
- obsolete `Message` model

The intended API surface is now the V1 module controllers only.

## 11. Recommended next cleanup if needed

After a successful local migration/test pass, consider:

1. introduce PHP enums for repeated statuses
2. split `routes/api/v1.php` into domain route files if it grows further
3. add policy/feature tests for ownership-sensitive endpoints
4. add real provider implementations for AI/social

## 12. Done condition for stabilization

You can consider the backend stabilized for local development when all of the following are true:

1. `php artisan migrate:fresh --seed` succeeds
2. `php artisan test` succeeds
3. queue worker runs without immediate job failures
4. scheduler dispatches due social posts
5. core manual flows work through Postman or frontend integration
