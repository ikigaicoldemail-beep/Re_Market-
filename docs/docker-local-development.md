# Docker Local Development

## Why this exists

Local `php.exe` is blocked by Windows Application Control on this machine, so the supported local workflow is Docker-based.

This setup gives you:

- Laravel app container
- MySQL container
- queue worker container
- scheduler container
- a safe way to run Artisan and tests without local PHP

## Files added

- `docker-compose.yml`
- `docker/php/Dockerfile`
- `docker/php/entrypoint.sh`
- `.dockerignore`

## First-time setup

### 0. Make sure Docker Desktop is running

If you see an error like:

```text
open //./pipe/dockerDesktopLinuxEngine: The system cannot find the file specified
```

then Docker Desktop is installed but the Linux engine is not running yet.

Start Docker Desktop first, wait until it shows as running, then continue.

### 1. Copy env file

In PowerShell:

```powershell
Copy-Item .env.example .env
```

### 2. Build containers

```powershell
docker compose build
```

### 3. Install dependencies inside container

```powershell
docker compose run --rm app composer install
```

### 4. Generate app key

```powershell
docker compose run --rm app php artisan key:generate --force
```

### 5. Generate JWT secret

```powershell
docker compose run --rm app php artisan jwt:secret --force
```

### 6. Run migrations and seeders

```powershell
docker compose run --rm app php artisan migrate:fresh --seed
```

### 7. Start the stack

```powershell
docker compose up -d app queue scheduler
```

The API will be available at:

- `http://localhost:8000`

## Useful commands

### Show running containers

```powershell
docker compose ps
```

### View app logs

```powershell
docker compose logs -f app
```

### View queue logs

```powershell
docker compose logs -f queue
```

### View scheduler logs

```powershell
docker compose logs -f scheduler
```

### Run tests

```powershell
docker compose run --rm app php artisan test
```

### Run a single Artisan command

```powershell
docker compose run --rm app php artisan route:list
```

### Stop everything

```powershell
docker compose down
```

### Stop and remove database volume too

```powershell
docker compose down -v
```

## Database connection used by containers

Inside Docker, Laravel uses:

- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_DATABASE=marketplace_backend`
- `DB_USERNAME=marketplace`
- `DB_PASSWORD=marketplace`

MySQL is exposed to the host on:

- `localhost:33061`

## Notes

- the entrypoint auto-copies `.env.example` to `.env` if needed
- the entrypoint auto-runs `composer install` if `vendor/` is missing
- queue and scheduler are separate containers so async features work locally
- tests run inside the app container and do not require local Windows PHP
