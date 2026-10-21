# SolarShare deployment runbook

This project deploys an immutable Docker image from GitHub Container Registry
to a Linux Docker host over SSH. The repository does not contain deployment
credentials or a production `.env`.

## 1. Prepare the server once

The server needs:

- Docker Engine and the Docker Compose plugin.
- An SSH user that can run Docker Compose (normally a member of the `docker`
  group).
- A directory for the deployment, for example `/opt/solarshare`.
- `docker-compose.yml` and `.env` in that directory.
- The public key matching `DEPLOY_SSH_KEY` in the user's
  `~/.ssh/authorized_keys`.

For a fresh Ubuntu host, the repository includes a one-time bootstrap script:

```bash
sudo bash scripts/deploy/bootstrap-ubuntu.sh
```

If the repository is not checked out yet, copy that script to the server first
or download it from the `dev` branch. Review scripts before running them on a
production host.

Copy only the deployment files to the server:

```bash
mkdir -p /opt/solarshare
cd /opt/solarshare
curl -O https://raw.githubusercontent.com/Zaineb-Messaoudi/Esprit_5TWIN7_BuilDream/dev/docker-compose.yml
```

Create the server's private `.env` from `.env.example`. For production, set at
least:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=your-generated-production-key
DB_DATABASE=SolarShare
DB_USERNAME=solarshare
DB_PASSWORD=use-a-long-random-password
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
```

Use real values for the mail, OAuth, Stripe, AI, Reverb, and Meilisearch
settings required by the features enabled in that environment. Never commit
this file.

If the GHCR package is private, log in once on the server with a GitHub token
having only `read:packages` permission:

```bash
echo "$GHCR_READ_TOKEN" | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin
```

## 2. Configure GitHub Environments

Create two environments in repository Settings → Environments:
`staging` and `production`.

Add these secrets to each environment:

| Secret | Value |
| --- | --- |
| `DEPLOY_HOST` | Server DNS name or IP address |
| `DEPLOY_USER` | SSH deployment username |
| `DEPLOY_SSH_KEY` | Private OpenSSH key; never the `.pub` file |
| `DEPLOY_PATH` | Absolute path containing the server `.env` and Compose file |

Protect `production` with a required reviewer before enabling real production
deployments. The workflow does not need the private `.env` as a GitHub secret;
that file remains on the server.

From Windows, after installing GitHub CLI and running `gh auth login`, the
repository helper can configure both environments without printing the private
key:

```powershell
.\scripts\deploy\Set-GitHubDeploymentSecrets.ps1
```

The helper asks for the server host, SSH user, deployment path, and local
private-key path. It never stores the key in the repository.

## 3. Deployment behavior

- Pushes to `dev` build, test, publish, and deploy to `staging`.
- A tag such as `v1.0.0` builds, tests, publishes, and deploys to `production`.
- Manual dispatch can select either environment.
- The server pulls the immutable commit SHA image, starts the stack without a
  source rebuild, runs migrations, and verifies `/up`.

The image tag is derived from the commit that passed CI, so the server never
deploys an untested floating `latest` image.

## 4. Rollback

Find the previous successful commit SHA in the workflow run, then on the
server run:

```bash
cd /opt/solarshare
APP_IMAGE=ghcr.io/zaineb-messaoudi/esprit_5twin7_buildream:sha-PREVIOUS docker compose pull
APP_IMAGE=ghcr.io/zaineb-messaoudi/esprit_5twin7_buildream:sha-PREVIOUS docker compose up -d --no-build
APP_IMAGE=ghcr.io/zaineb-messaoudi/esprit_5twin7_buildream:sha-PREVIOUS docker compose exec -T app php artisan migrate:status
```

Do not roll back application code past migrations that have already changed
production data without a reviewed database rollback plan.
