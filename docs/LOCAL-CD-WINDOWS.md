# No-card automatic CI/CD demonstration on Windows + WSL

This repository can demonstrate the full CI/CD story without a VPS. GitHub
Actions runs CI and publishes the image to Docker Hub. A self-hosted GitHub
Actions runner in WSL then pulls that tested image and deploys it with Docker
Compose on the Windows machine. Staging automatically starts a Cloudflare Quick
Tunnel and prints its public URL.

This is a demonstration/staging setup, not permanent production hosting.

## 1. Prepare local deployment directories

In WSL, from the repository:

```bash
mkdir -p "$HOME/solarshare-staging" "$HOME/solarshare-production"
cp .env "$HOME/solarshare-staging/.env"
cp .env "$HOME/solarshare-production/.env"
```

Edit each `.env` separately. Staging should use `APP_ENV=staging`,
`APP_DEBUG=false`, `APP_PORT=8000`, and `FORWARD_DB_PORT=3307`. Production
should use `APP_ENV=production`, `APP_DEBUG=false`, `APP_PORT=8002`, and
`FORWARD_DB_PORT=3308`. Keep both files outside Git.

## 2. Install a self-hosted runner

Open GitHub repository Settings → Actions → Runners → New self-hosted runner.
Choose Linux and x64. Copy the commands GitHub shows into WSL. GitHub gives a
temporary registration token, so do not invent one or commit it.

Keep the runner process open:

```bash
./run.sh
```

Before leaving it running, verify Docker is available in that same WSL shell:

```bash
docker version
docker compose version
```

## 3. Automatic staging deployment

After the test and Docker build jobs succeed on a push to `dev`, the same
`Deploy Production` workflow automatically runs its staging job on the WSL
runner. It pulls the exact tested SHA image from Docker Hub, runs migrations,
starts Compose, starts the public tunnel, and checks `/up`.

The runner must be online when the workflow starts. The repository must remain
private because a self-hosted runner executes repository workflow code.

## 4. Automatic production deployment

Push a version tag after staging validation:

```bash
git tag v1.0.0
git push origin v1.0.0
```

The same workflow automatically deploys the tested production image to the
production WSL directory. The production environment can still be configured
with a required reviewer if the team wants a controlled release gate.

## 5. Temporary public URL

The staging deployment starts the tunnel automatically. Find its URL in the
`Deploy to Staging` job logs. It looks like:

```text
https://random-words.trycloudflare.com
```

The URL is temporary and changes when the staging stack is recreated. It stops
working when Docker Desktop or the computer is turned off.
