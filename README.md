# ReMarket

Second-hand ecommerce marketplace built with Laravel, Alpine.js, MySQL, Docker, queues, scheduled jobs, chat, stores, product listings, and local/free visual search.

## Stack

- Laravel 12 / PHP 8.2
- MySQL 8.4
- Alpine.js + Vite + Tailwind
- Docker Compose for local development
- OpenCLIP `ViT-B-32` + FAISS for visual search
- Leaflet + OpenStreetMap for in-app maps

## Quick Start With Docker

```bash
cp .env.example .env
docker compose build
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate --force
docker compose run --rm app php artisan jwt:secret --force
docker compose run --rm app php artisan migrate:fresh --seed
docker compose up -d app queue scheduler
npm install
npm run build
```

Open:

```text
http://localhost:8000
```

## Visual Search Setup

Visual search is local and free. It uses OpenCLIP image embeddings plus FAISS.

Docker Compose already sets:

```env
VISUAL_SEARCH_PYTHON=python3
VISUAL_SEARCH_MODEL=ViT-B-32
VISUAL_SEARCH_PRETRAINED=openai
VISUAL_SEARCH_DEVICE=cpu
VISUAL_SEARCH_TIMEOUT=900
```

The first OpenCLIP run downloads model weights. Warm the model once:

```bash
docker compose exec app python3 -c "import open_clip; m, _, _ = open_clip.create_model_and_transforms('ViT-B-32', pretrained='openai', device='cpu'); print(m.visual.output_dim)"
```

Then build/rebuild the FAISS index:

```bash
docker compose exec app php artisan visual-search:generate-embeddings
```

Run that command after reseeding, bulk product imports, or changing `VISUAL_SEARCH_MODEL` / `VISUAL_SEARCH_PRETRAINED`.

## Useful Commands

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f queue
docker compose exec app php artisan test
docker compose exec app php artisan route:list
docker compose exec app php artisan visual-search:generate-embeddings
```

## Deployment Notes

- Use the root `Dockerfile` for deploy builds.
- Run a real queue worker in production; visual search and social jobs depend on queues.
- Run Laravel scheduler as a separate process/container.
- Persist `storage/app/public` for uploaded images.
- Persist `storage/app/visual-search/faiss.index` or rebuild it after deploy.
- Persist OpenCLIP model cache if possible:
  - `HF_HOME`
  - `TORCH_HOME`
- Keep `VISUAL_SEARCH_DEVICE=cpu` unless your deploy image has CUDA support.
- Run `php artisan visual-search:generate-embeddings` after production imports or model changes.

More detail:

- [Docker local development](docs/docker-local-development.md)
- [Visual search with OpenCLIP](docs/visual-search-openclip.md)
- [Current codebase notes](docs/current-codebase.md)
- [Deployment checklist](docs/deployment.md)
