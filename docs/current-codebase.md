# Marketplace Current Codebase

This is the single source of documentation for the current project state.

## Project Summary

This project is a Laravel 12 second-hand marketplace with a Blade/Alpine frontend and a JWT API.

Current active scope:

- User registration, login, logout, forgot/reset password
- User profile, avatar, and cover upload
- Seller store/page creation and management
- Product catalog, categories, brands, conditions, product images, variants, and public browsing
- Wishlist, reviews, reports, notifications, and compare page
- Buyer-seller conversations and messages
- Simulated Facebook product sharing, auto-posting, and scheduled posts
- Admin management for users, stores, products, categories, brands, promo banners, and reports

Removed from active scope:

- Visual search / AI similarity search
- Cart, checkout, orders, payments, delivery, saved addresses, refunds, shipping providers, and discount systems
- Non-Facebook social publishing

## Tech Stack

- PHP `^8.2`
- Laravel `^12`
- JWT auth via `tymon/jwt-auth`
- Laravel Reverb package installed for broadcasting support
- Blade templates
- Alpine.js
- Tailwind/Vite
- MySQL for normal development
- SQLite in-memory for tests

Use XAMPP PHP on this machine:

```bash
C:\xampp\php\php.exe
```

The `C:\php\php.exe` binary previously failed because `mbstring` and `openssl` were blocked/missing.

## Setup

```bash
composer install
npm install
copy .env.example .env
C:\xampp\php\php.exe artisan key:generate
C:\xampp\php\php.exe artisan jwt:secret
C:\xampp\php\php.exe artisan migrate
C:\xampp\php\php.exe artisan db:seed
npm run build
```

Run the app:

```bash
C:\xampp\php\php.exe artisan serve
```

Optional workers:

```bash
C:\xampp\php\php.exe artisan queue:work --queue=social,default
```

## Useful Commands

Clear generated/cache state:

```bash
C:\xampp\php\php.exe artisan optimize:clear
```

Run tests:

```bash
C:\xampp\php\php.exe artisan test
```

Build frontend:

```bash
npm run build
```

List routes:

```bash
C:\xampp\php\php.exe artisan route:list
```

## Important Environment Values

Required for app auth/runtime:

```env
APP_KEY=
JWT_SECRET=
APP_URL=http://127.0.0.1:8000
AUTH_GUARD=api
```

Database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketplace_backend
DB_USERNAME=
DB_PASSWORD=
```

Facebook:

The current classroom scope uses simulated Facebook posting, so these values are optional unless real OAuth connection is demonstrated.

```env
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI=
FACEBOOK_GRAPH_VERSION=v22.0
```

Rate limits:

```env
API_RATE_LIMIT=120
AUTH_RATE_LIMIT=20
CHAT_RATE_LIMIT=60
PUBLIC_SEARCH_RATE_LIMIT=90
SOCIAL_RATE_LIMIT=30
REPORT_RATE_LIMIT=5
```

## Main Web Pages

- `/`
- `/login`
- `/register`
- `/products/{id}`
- `/wishlist`
- `/compare`
- `/profile`
- `/me/store`
- `/me/products`
- `/me/products/new`
- `/me/products/{id}/edit`
- `/me/scheduled-listings`
- `/stores`
- `/stores/{id}`
- `/categories`
- `/categories/{slug}`
- `/messages`
- `/messages/{id}`
- `/social/accounts`
- `/social/scheduled-posts`
- `/admin`
- `/admin/users`
- `/admin/stores`
- `/admin/products`
- `/admin/categories`
- `/admin/brands`
- `/admin/banners`
- `/admin/reports`

## Main API Areas

All API routes are under `/api/v1`.

Public:

- `GET /health`
- `GET /products`
- `GET /products/{product}`
- `GET /stores`
- `GET /stores/{store}`
- `GET /stores/{store}/products`
- `GET /categories`
- `GET /brands`
- `GET /product-conditions`
- `GET /promo-banners`

Auth:

- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout`
- `POST /auth/forgot-password`
- `POST /auth/reset-password`

Marketplace authenticated areas:

- Profile: `/me`, `/me/avatar`, `/me/cover`
- Stores: `/stores`, `/stores/{store}`, `/stores/{store}/follow`
- Products: `/products`, `/products/{product}`, `/products/{product}/images`
- Wishlist: `/wishlist`
- Reviews: `/products/{product}/reviews`, `/reviews/{review}`
- Reports: `/products/{product}/report`, `/product-reports/reasons`
- Notifications: `/notifications`
- Conversations: `/conversations`
- Social accounts/posts: `/social/accounts`, `/social/posts`, `/scheduled-posts`
- Admin: `/admin/*`

## Social Posting Scope

Facebook is the supported social auto-post target for the current version. Posting is simulated by default for the classroom scope: the backend creates social post records and marks due posts as posted without requiring live Facebook API credentials.

Current product form behavior:

- `auto_post=facebook` is accepted
- unsupported values such as `instagram` or `all` are rejected
- create flow blocks Facebook auto-post unless a product image is attached

## Visual Search

Visual search is integrated into the marketplace API and home search UI.

Current flow:

- seller product image upload indexes the listing image through OpenCLIP `ViT-B-32` + FAISS
- `POST /api/search/visual` and `POST /api/v1/search/visual` accept multipart `image`
- search results return regular product resources with a `similarity` score
- FAISS vectors live in `storage/app/visual-search/faiss.index`
- SQL only stores the lightweight `listing_visual_index` mapping from listing/image to FAISS id
- CLIP concept filtering prevents obvious cross-type matches, for example phone queries returning laptops
- Docker Compose persists model downloads in `huggingface_cache` and `torch_cache`

Local AI dependencies:

```bash
pip3 install --break-system-packages -r scripts/requirements-visual-search.txt
```

Backfill/rebuild existing listings:

```bash
php artisan visual-search:generate-embeddings
```

Docker:

```bash
docker compose exec app php artisan visual-search:generate-embeddings
```

## Production Notes

Before production:

- Configure real mail provider
- Configure production storage for uploaded images
- Configure queue worker supervisor
- Configure real Facebook OAuth/app credentials only if business scope changes from simulated posting
- Add cart/checkout/payment/delivery only if business scope changes
- Run tests and frontend build in CI
- Serve with a real web server instead of `php artisan serve`

Current verification baseline:

```bash
C:\xampp\php\php.exe artisan test
npm run build
```
