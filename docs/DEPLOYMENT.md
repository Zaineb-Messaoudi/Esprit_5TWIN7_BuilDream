# SolarShare CI/CD deployment

## Pipeline

```text
Pull Request to main
    -> Tests, Pint, frontend build and security audit
Merge to main
    -> Docker image build
    -> Docker Hub push
    -> WSL self-hosted runner
    -> Docker Desktop / Docker Compose
    -> migrations and health check
```

## Docker Hub

Create a Docker Hub repository named `solarshare`. Configure these GitHub
repository secrets:

| Secret | Value |
| --- | --- |
| `DOCKERHUB_USERNAME` | Docker Hub username |
| `DOCKERHUB_TOKEN` | Docker Hub Read & Write access token |

The workflow publishes an immutable commit tag and the branch tag. The
deployment uses the commit tag, never an unverified floating image.

## Deployment target

The validation deployment target is a Windows machine running Docker Desktop
with a self-hosted GitHub Actions runner inside WSL. The runner must be online
when `main` is updated. The deployment directory is:

```text
$HOME/solarshare-production
```

Its `.env` file is private and is never committed. Docker Compose starts the
Laravel application, database, Redis, Meilisearch, queue, scheduler and Reverb
services. The deployment runs migrations and verifies `/up` before succeeding.

## Release rule

`main` is the release branch. A Pull Request must pass all CI checks before it
can be merged. A successful merge automatically builds, publishes and deploys
the tested image.

## Rollback

Every image has a commit SHA tag in Docker Hub. To roll back, select the last
known-good SHA image and redeploy it through the same Compose deployment after
reviewing any database migration impact.

This local target is suitable for the validation demonstration. A permanent
public production deployment would require an always-available external host.
