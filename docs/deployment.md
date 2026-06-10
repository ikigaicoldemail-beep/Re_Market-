# Deployment Checklist

## Runtime Services

Run these as separate processes or containers:

- Web app
- Queue worker
- Scheduler
- MySQL or managed database

The local Compose file uses `php artisan serve` for convenience. Production should use a proper PHP runtime/web server setup for the target platform, or the root `Dockerfile` if deploying as a single app container.

## Required Environment

Set normal Laravel production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_FRONTEND_URL=https://your-domain.example
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
FILESYSTEM_DISK=public
```

Visual search defaults:

```env
VISUAL_SEARCH_PYTHON=python3
VISUAL_SEARCH_MODEL=ViT-B-32
VISUAL_SEARCH_PRETRAINED=openai
VISUAL_SEARCH_DEVICE=cpu
VISUAL_SEARCH_TIMEOUT=900
HF_HOME=/app/storage/app/model-cache/huggingface
TORCH_HOME=/app/storage/app/model-cache/torch
```

Use `VISUAL_SEARCH_DEVICE=cuda` only if the production image and host support CUDA PyTorch.

## Persistent Storage

Persist these paths:

- `storage/app/public` for uploaded product/chat/store images
- `storage/app/visual-search/faiss.index` for FAISS visual-search vectors
- `storage/app/model-cache/huggingface` for model cache
- `storage/app/model-cache/torch` for model cache

If `faiss.index` is not persisted, rebuild it after deploy:

```bash
php artisan visual-search:generate-embeddings
```

If model caches are not persisted, the first visual search/index rebuild will download OpenCLIP weights again.

## Build

The root `Dockerfile` installs:

- PHP extensions
- Composer dependencies
- Node dependencies and Vite build
- Python visual-search dependencies from `scripts/requirements-visual-search.txt`

Build example:

```bash
docker build -t remarket-app .
```

## Release Commands

Run during deployment:

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

After product imports, reseeds, or visual model changes:

```bash
php artisan visual-search:generate-embeddings
```

## Queue Worker

Run a worker with a timeout long enough for OpenCLIP indexing:

```bash
php artisan queue:work --queue=default,ai,social --tries=3 --timeout=900
```

Use Supervisor, systemd, a platform worker process, or a separate Docker service to keep this running.

## Scheduler

Run:

```bash
php artisan schedule:work
```

or configure a cron entry that runs:

```bash
php artisan schedule:run
```

## OpenStreetMap

In-app maps use Leaflet + OpenStreetMap tiles. For heavier production traffic, consider a dedicated tile provider or cache instead of relying on the public tile servers.

## Smoke Test

After deploy:

```bash
php artisan route:list
php artisan visual-search:generate-embeddings --limit=3
```

Then test:

- Sign in
- Browse products
- Upload visual-search image
- Open a store page map
- Send a chat message with an image
