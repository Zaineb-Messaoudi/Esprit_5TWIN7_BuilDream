# SolarShare deployment runbook

This project builds and publishes an immutable Docker image to Docker Hub. A
remote Linux Docker host can optionally pull that image over SSH. The
repository does not contain registry credentials, deployment credentials, or a
production `.env`.

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

If the Docker Hub repository is private, log in once on the server with a
Docker Hub access token having read permission:

```bash
echo "$DOCKERHUB_READ_TOKEN" | docker login -u YOUR_DOCKERHUB_USERNAME --password-stdin
```

## 2. Configure Docker Hub and GitHub secrets

Create a Docker Hub repository named `solarshare` and add these repository
secrets in GitHub Settings → Secrets and variables → Actions:

| Secret | Value |
| --- | --- |
| `DOCKERHUB_USERNAME` | Your Docker Hub username |
| `DOCKERHUB_TOKEN` | A Docker Hub access token with Read & Write permission |

Every successful test/build run publishes these tags:

```text
YOUR_DOCKERHUB_USERNAME/solarshare:latest
YOUR_DOCKERHUB_USERNAME/solarshare:sha-COMMIT
```

## 3. Configure GitHub Environments for optional remote deployment

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

Remote deployment is intentionally disabled by default. To enable automatic
deployment, add an environment or repository variable named
`DEPLOY_STAGING_ENABLED` or `DEPLOY_PRODUCTION_ENABLED` with the value `true`.
Manual dispatch can also deploy remotely by selecting the target environment
and setting `deploy_remote` to `true`.

From Windows, after installing GitHub CLI and running `gh auth login`, the
repository helper can configure both environments without printing the private
key:

```powershell
.\scripts\deploy\Set-GitHubDeploymentSecrets.ps1
```

The helper asks for the server host, SSH user, deployment path, and local
private-key path. It never stores the key in the repository.

## 4. Deployment behavior

- Pushes to `dev` build, test, and publish to Docker Hub.
- A tag such as `v1.0.0` builds, tests, and publishes to Docker Hub.
- Remote staging/production deployment only runs when explicitly enabled.
- The server pulls the immutable commit SHA image, starts the stack without a
  source rebuild, runs migrations, and verifies `/up`.

The image tag is derived from the commit that passed CI, so the server never
deploys an untested floating `latest` image.

## 5. Rollback

Find the previous successful commit SHA in the workflow run, then on the
server run:

```bash
cd /opt/solarshare
APP_IMAGE=YOUR_DOCKERHUB_USERNAME/solarshare:sha-PREVIOUS docker compose pull
APP_IMAGE=YOUR_DOCKERHUB_USERNAME/solarshare:sha-PREVIOUS docker compose up -d --no-build
APP_IMAGE=YOUR_DOCKERHUB_USERNAME/solarshare:sha-PREVIOUS docker compose exec -T app php artisan migrate:status
```

Do not roll back application code past migrations that have already changed
production data without a reviewed database rollback plan.
