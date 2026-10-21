# No-card CI/CD demonstration on Windows + WSL

This repository can demonstrate the full CI/CD story without a VPS. GitHub
Actions runs CI and publishes the image to Docker Hub. A self-hosted GitHub
Actions runner in WSL then pulls that tested image and deploys it with Docker
Compose on the Windows machine. A Cloudflare Quick Tunnel can expose the local
staging port temporarily for the validation demo.

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

After a successful `Deploy Production` workflow on `dev`, `Local CD Demo`
automatically runs on the WSL runner. It pulls the exact tested SHA image from
Docker Hub, runs migrations, starts Compose, and checks `/up`.

The runner must be online when the workflow starts. The repository must remain
private because a self-hosted runner executes repository workflow code.

## 4. Manual production demonstration

Use Actions → Local CD Demo → Run workflow, select `production`, and enter a
published Docker tag such as `sha-a7d650a`. The production environment can be
configured with a required reviewer for a visible approval step.

## 5. Temporary public URL

With staging running on port 8000, open another WSL terminal and run:

```bash
docker run --rm cloudflare/cloudflared:latest \
  tunnel --no-autoupdate --url http://host.docker.internal:8000
```

Cloudflare prints a temporary `https://....trycloudflare.com` URL. Keep this
terminal open during the demonstration. The URL stops working when the command
is stopped or the computer is turned off.
