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
    -> temporary staging stack on GitHub-hosted Linux
    -> self-hosted WSL runner
    -> Docker Desktop / Docker Compose
    -> migrations, /up health check and rollback protection
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
4. starts an isolated staging stack on a GitHub-hosted runner;
5. checks the staging `/up` endpoint and destroys the temporary stack;
6. checks out the tested source on the WSL runner;
7. pulls the immutable commit image;
8. starts Docker Compose;
9. runs Laravel migrations;
10. checks `/up`, with rollback protection if the rollout fails.

No deployment button or second deployment command is needed after the merge.

## 4. Local verification

The application is available at `http://localhost:8000` while Docker Desktop
and the runner machine are running.

The deployment target is intentionally local for validation. A permanent
public deployment would require a continuously available external server.
