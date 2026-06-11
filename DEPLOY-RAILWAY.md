# Deploying ReMarket to Railway

Your repo: `https://github.com/ikigaicoldemail-beep/Re_Market-`
Stack: Laravel + MySQL 8, built from the root `Dockerfile` (Railway reads `railway.toml`).

---

## 1. Commit and push your project

The build artifacts (`vendor`, `node_modules`, `public/build`, `.env`) are git-ignored on purpose — Railway rebuilds them. Commit everything else and push to your repo.

```bash
git add -A
git commit -m "Prepare for Railway deploy: footer pages, inline icons, lean Dockerfile"
git push -u mine main
```

### If the push fails with `403 ... denied to MaiSoklyna`
Your machine is logged into GitHub as **MaiSoklyna**, but the repo belongs to **ikigaicoldemail-beep**. Pick one:

- **Easiest** — sign into github.com as `ikigaicoldemail-beep` → repo **Settings → Collaborators** → add `MaiSoklyna` → accept the invite → `git push -u mine main` again.
- **Or use a token** — as `ikigaicoldemail-beep`: **Settings → Developer settings → Personal access tokens (classic)** → generate with the `repo` scope. Then when Git prompts for a password, paste the **token**. If it doesn't prompt, open Windows **Credential Manager → Windows Credentials**, delete the `git:https://github.com` entry, and push again.

---

## 2. Create the Railway project

1. Go to https://railway.app → **New Project → Deploy from GitHub repo**.
2. Authorize Railway for the `ikigaicoldemail-beep` account and pick **Re_Market-**.
3. Railway detects `railway.toml` and builds from your `Dockerfile`. Let the first build run (it will fail to boot until the database + env vars exist — that's expected).

## 3. Add a MySQL database

1. In the project canvas → **New → Database → Add MySQL**.
2. Railway provisions MySQL 8 (which supports the FULLTEXT search this app needs).

## 4. Set environment variables

Open your **web service → Variables** and add these. The `${{MySQL.*}}` values link to the database service automatically.

| Variable | Value |
|---|---|
| `APP_NAME` | `ReMarket` |
| `APP_ENV` | `production` |
| `APP_KEY` | `base64:t0fl0leT6sKLkdNsWc+loc0DYLWmlTE4P1BDHHA3fbE=` |
| `APP_DEBUG` | `true`  *(set to `false` once it works)* |
| `APP_URL` | *(your Railway URL — fill in after step 5)* |
| `APP_TIMEZONE` | `Asia/Singapore` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `JWT_SECRET` | `RucWIuDsQezV3XIm0RNDndmeXqYZjqTeGRS9Elm1kHsRQPyhStaPFTGUfCiLVGRA` |
| `AUTH_GUARD` | `api` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `FILESYSTEM_DISK` | `public` |
| `MAIL_MAILER` | `log` |
| `LOG_CHANNEL` | `stderr` |

`QUEUE_CONNECTION=sync` makes background jobs run inline — correct for a single service with no separate worker. Don't set `PORT`; Railway injects it and the Dockerfile already uses `$PORT`.

## 5. Generate a public URL

In the service → **Settings → Networking → Generate Domain**. Copy the `https://...up.railway.app` URL, paste it into the `APP_URL` variable from step 4, and redeploy.

## 6. Watch the deploy

On boot the container runs automatically:
```
config:cache  →  route:cache  →  migrate --force  →  serve on $PORT
```
Watch **Deployments → Logs**. When the health check at `/api/v1/health` passes, you're live. Visit your URL.

## 7. Load the seed data (once)

Migrations run automatically, but the marketplace **seed data does not**. Load it one time after the first successful deploy. With the Railway CLI installed (`npm i -g @railway/cli`, then `railway login` and `railway link`):

```bash
railway run php artisan db:seed --force
```

After seeding you can log in with:
- `ppm@gmail.com` / `password` (seller)
- `houradmin@gmail.com` / `hour1234` (admin)

---

## Notes
- **Uploaded images don't persist** on Railway's ephemeral filesystem across deploys. Seed data uses placeholder image URLs, so this is fine for a demo. For permanent uploads, use S3 (set the `AWS_*` / `FILESYSTEM_DISK=s3` variables).
- **Visual search is disabled** in the Dockerfile (the heavy torch/CLIP install). To re-enable, uncomment the `pip3 install ... requirements-visual-search.txt` line and set `AI_SIMILARITY_API_KEY`.
- **Facebook/TikTok posting** needs the `FACEBOOK_*` / `TIKTOK_*` variables and matching OAuth redirect URLs pointing at your Railway domain. Optional.
