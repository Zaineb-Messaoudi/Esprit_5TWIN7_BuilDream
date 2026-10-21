# SolarShare Docker development

## Prerequisites

- Docker Desktop for Windows with the WSL 2 engine enabled.
- A local `.env` copied from `.env.example`.
- Ollama installed on Windows only if local Ollama AI is needed.

## Start the complete stack

From PowerShell at the repository root:

```powershell
docker compose up -d --build
```

The application is available at `http://localhost:8000`.

Supporting services:

- Mailpit: `http://localhost:8025`
- Meilisearch: `http://localhost:7700`
- Reverb WebSocket: port `8081`

## Verify the stack

```powershell
docker compose ps
docker compose logs -f app
docker compose exec app php artisan about
docker compose exec app php artisan migrate:status
```

Run a focused test inside the application container with:

```powershell
docker compose exec app php artisan test --filter=FrontOfficeTest
```

## Stop and reset

```powershell
docker compose down
```

To remove local database, Redis, and Meilisearch data as well:

```powershell
docker compose down -v
```

## Local Ollama from inside Docker

Use this value in the Docker-local `.env` when Ollama runs on Windows:

```env
AI_BASE_URL=http://host.docker.internal:11434
```

Never commit `.env`, API keys, Stripe secrets, or OAuth secrets.

## CI/CD deployment contract

The deployment workflow builds and publishes the image to GitHub Container
Registry. To enable its SSH deployment jobs, configure these GitHub Environment
secrets for both `staging` and `production`:

- `DEPLOY_HOST` — server hostname or IP address.
- `DEPLOY_USER` — deployment user with Docker Compose access.
- `DEPLOY_SSH_KEY` — private key whose public key is installed on the server.
- `DEPLOY_PATH` — directory containing the production `.env` and Compose file.

The server must already have Docker Engine, Compose, the checked-out Compose
files, and its private `.env`. The workflow pulls the immutable SHA-tagged image,
starts the stack without rebuilding, and runs migrations.
