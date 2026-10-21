# Automatic CI/CD on Windows + WSL

This is the final no-VPS deployment model for the validation environment.
GitHub Actions runs CI, builds and publishes the Docker image to Docker Hub,
then a self-hosted GitHub Actions runner in WSL pulls that exact image and
deploys it to Docker Desktop with Docker Compose.

## Flow

```text
Pull Request to main
    -> CI: tests, quality, security, frontend build
Merge to main
    -> Docker image build and Docker Hub push
    -> self-hosted WSL runner
    -> Docker Desktop / Docker Compose
    -> migrations and /up health check
```

## 1. Prepare the local production environment

In WSL, create the deployment directory and copy the private environment file:

```bash
mkdir -p "$HOME/solarshare-production"
cp .env "$HOME/solarshare-production/.env"
```

Set these values in that private file:

```env
APP_ENV=production
APP_DEBUG=false
APP_PORT=8000
FORWARD_DB_PORT=3306
```

The file remains outside Git.

## 2. Install the self-hosted runner once

Open GitHub repository Settings → Actions → Runners → New self-hosted runner.
Choose Linux and x64, then copy GitHub's generated installation commands into
WSL. Do not invent or commit the temporary registration token.

Start the runner and keep it online:

```bash
./run.sh
```

The runner must show `Listening for Jobs`. Docker Desktop must also be running.

## 3. Automatic deployment

After the Pull Request is merged into `main`, the `Deploy Production` workflow
automatically:

1. runs the complete CI suite;
2. builds the Docker image;
3. pushes the image to Docker Hub;
4. checks out the tested source on the WSL runner;
5. pulls the immutable commit image;
6. starts Docker Compose;
7. runs Laravel migrations;
8. checks `/up`.

No deployment button or second deployment command is needed.

## 4. Local verification

The application is available at `http://localhost:8000` while Docker Desktop
and the runner machine are running.

The deployment target is intentionally local for validation. A permanent
public deployment would require a continuously available external server.
