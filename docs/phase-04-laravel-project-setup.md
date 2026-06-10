# Phase 4: Laravel Project Setup

## 1. Goal

This phase defines and establishes the backend foundation for the marketplace API. The purpose is to prepare the Laravel project for the implementation phases by standardizing:

- API-only project structure
- authentication baseline
- environment configuration
- queue and storage setup
- CORS and API versioning
- error handling and logging

This phase does not yet implement the full marketplace domain. It prepares the project so the next feature phases can be built cleanly.

## 2. Current Repository State

The repository already contains:

- Laravel 12
- `tymon/jwt-auth`
- early prototype controllers and routes
- basic jobs table migration

Because of that, we are not doing a fresh install from scratch in this repository. Instead, we are upgrading the existing scaffold into a production-ready API baseline.

## 3. Fresh Install Reference

If this project had to be recreated from zero, the baseline commands would be:

```bash
composer create-project laravel/laravel marketplace-backend
cd marketplace-backend
composer require tymon/jwt-auth
php artisan jwt:secret
php artisan storage:link
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

For this repository, the package already exists and the queue table migration already exists.

## 4. Package Strategy

### Installed

- `laravel/framework`
- `tymon/jwt-auth`

### Recommended later

Add only when needed:

- `laravel/reverb` for realtime chat
- `spatie/laravel-permission` if admin roles become more complex
- API documentation tooling if we want generated docs

For now, we should avoid package sprawl and keep the core API foundation lean.

## 5. API-Only Structure

### Recommended structure

- `app/Http/Controllers/Api/V1`
- `app/Http/Requests`
- `app/Http/Resources`
- `app/Services`
- `app/Actions`
- `app/Jobs`
- `app/Policies`
- `app/Events`
- `app/Notifications`
- `app/Contracts`
- `app/Integrations`

### Why

This keeps:

- controllers thin
- business logic out of controllers
- external providers isolated
- versioned API evolution manageable

## 6. Authentication Setup

### Chosen approach

Use JWT authentication with `tymon/jwt-auth`.

### Setup decisions

- default auth guard is now `api`
- protected endpoints should use `auth:api`
- JWT middleware aliases are registered for future use

### Why JWT here

- stateless API-friendly auth
- works well for separate frontend/mobile clients
- avoids session coupling for the API layer

### Important next-step note

Phase 5 will implement:

- register
- login
- logout
- forgot password
- reset password
- current user profile

## 7. Environment Configuration

### Main application settings

The `.env.example` was adjusted to reflect marketplace API defaults:

- MySQL as the primary database
- public filesystem by default for uploaded assets
- marketplace-focused logging stack
- JWT and CORS placeholders
- API rate limit controls

### Recommended local values

- `DB_CONNECTION=mysql`
- `DB_DATABASE=marketplace_backend`
- `QUEUE_CONNECTION=database`
- `FILESYSTEM_DISK=public`

### Recommended production values

- MySQL managed database
- Redis for queue/cache
- S3-compatible object storage
- strict allowed CORS origins
- daily + stderr structured logging

## 8. Queue Configuration

### Current baseline

- queue connection remains configurable via `QUEUE_CONNECTION`
- `database` queue is suitable for local development

### Production recommendation

Use Redis-backed queues in production for:

- better throughput
- cleaner worker scaling
- improved handling for social posting, notifications, and AI jobs

### Queue categories we will use later

- `default`
- `social`
- `ai`
- `notifications`
- `media`

## 9. Filesystem and Storage Setup

### Current decisions

- default disk is now `public`
- a `product-images` disk was added for marketplace assets

### Why

Product images are a first-class feature in this system, so it helps to give them an explicit storage target from the beginning.

### Production recommendation

Switch uploaded media to S3-compatible storage while keeping Laravel’s disk abstraction unchanged.

## 10. CORS Setup

### Current decisions

CORS is now environment-driven:

- `CORS_ALLOWED_ORIGINS`
- `CORS_ALLOWED_HEADERS`
- `CORS_SUPPORTS_CREDENTIALS`

### Why

This is safer than hard-coding `*` for production environments and matches an API consumed by separate frontend clients.

## 11. API Versioning Strategy

### Current decisions

The API is now versioned under:

- `/api/v1/...`

Routes are loaded from:

- `routes/api.php`
- `routes/api/v1.php`

### Why

This makes future breaking changes manageable without disrupting existing clients.

## 12. Rate Limiting Strategy

The project now has named rate limiters for:

- `api`
- `auth`
- `chat`
- `public-search`

These are defined in `AppServiceProvider`.

### Initial defaults

- API: 120 requests/minute
- Auth: 20 requests/minute
- Chat: 60 requests/minute
- Public search: 90 requests/minute

These values are environment-driven and can be tuned later.

## 13. Logging and Error Handling Strategy

### Logging

A `marketplace` log stack was added to combine:

- daily file logs
- stderr output

This works well for local debugging and containerized production deployments.

### Error handling

API requests now consistently return JSON for common failures:

- validation errors
- unauthenticated requests
- unauthorized actions
- missing resources
- missing endpoints

### Why

This prevents HTML error pages from leaking into API responses and gives the frontend a predictable contract.

## 14. Routing Baseline Implemented

The route structure now follows:

- `/api/v1/health`
- `/api/v1/register`
- `/api/v1/login`
- `/api/v1/products`
- `/api/v1/messages`

This still points to prototype controllers for now, but the route layer is now versioned and grouped properly.

## 15. Recommended Immediate Next Steps

After this setup phase, the next implementation priority should be:

1. replace prototype auth logic with validated request classes and API resources
2. introduce `Api/V1` controller namespaces
3. create profile and store models/migrations
4. start Phase 5 user management endpoints cleanly

## 16. Files Updated in This Phase

The following foundation updates were made:

- versioned API routes
- JWT-oriented middleware aliases
- API-first auth default
- environment-driven CORS
- public/product image filesystem defaults
- marketplace logging stack
- JSON exception rendering for API routes
- named rate limiters

## 17. Phase 4 Outcome

At the end of Phase 4, the Laravel project now has a more production-ready API baseline:

- versioned routing
- API-auth-first configuration
- queue/storage defaults aligned with marketplace behavior
- predictable JSON error handling
- environment-driven operational settings

This is the setup foundation for Phase 5 authentication and user management.
