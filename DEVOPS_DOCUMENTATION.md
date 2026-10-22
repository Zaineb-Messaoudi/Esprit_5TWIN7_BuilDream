# SolarShare - DevOps Documentation

**Project:** SolarShare - Solar Equipment Rental Marketplace  
**Stack:** Laravel 12 · Tailwind CSS 4 · Alpine.js · MySQL 8 · Redis 7 · Meilisearch · Reverb WebSockets  
**CI/CD:** GitHub Actions · Docker · Self-hosted Runner  
**Date:** October 2026

---

## 1. DevOps Process Architecture

```mermaid
flowchart TD
    subgraph DEV["👨‍💻 Development"]
        A1[Local Development\nphp artisan serve\nnpm run dev]
        A2[Feature Branches\ngit flow]
        A3[Conventional Commits]
    end

    subgraph CI["⚙️ Continuous Integration\nGitHub Actions (test.yml)"]
        B1[Security Audit\ncomposer/npm audit\nTrivy scan]
        B2[Code Style\nLaravel Pint\nParallel]
        B3[Frontend Build\nVite + TypeScript\nESLint]
        B4[PHP Tests\nPest Parallel\nMySQL 8 + Redis]
        B5[Validation Smoke\nFull HTTP flows]
    end

    subgraph BUILD["🐳 Docker Build\n(deploy.yml)"]
        C1[Multi-stage Build\nPHP 8.3 + Nginx]
        C2[Buildx Multi-arch\nlinux/amd64]
        C3[Push to Docker Hub\nTag: sha-<commit>]
        C4[Trivy Image Scan\nCRITICAL/HIGH only]
    end

    subgraph STAGING["🧪 Staging Smoke Test"]
        D1[Deploy Stack\nDocker Compose]
        D2[Wait MySQL Ready]
        D3[Migrate + Seed\nphp artisan migrate --force\ndb:seed --force]
        D4[Cache Config\nRoute + View cache]
        D5[Health Check\ncurl /up → 200 OK]
        D6[Tear Down\nClean volumes]
    end

    subgraph CD["🚀 Continuous Deployment\n(deploy-production)"]
        E1[Self-hosted Runner\nWSL / Production Server]
        E2[Pull Images\nDocker Hub]
        E4[Backup Database]
        E5[Deploy + Migrate + Seed]
        E6[Health Check\n/up endpoint]
        E7[Rollback on Failure\nPrevious image]
    end

    subgraph MONITOR["📊 Observability"]
        F1[Laravel Telescope\nDev only]
        F2[Sentry\nErrors + Performance]
        F3[Health Endpoint\n/up]
        F4[Backup Command\nDaily DB backups]
    end

    A1 --> A2 --> A3
    A3 -->|Push/PR| B1
    B1 --> B2
    B2 --> B3
    B3 --> B4
    B4 --> B5
    B5 -->|Main branch| C1
    C1 --> C2 --> C3 --> C4
    C4 --> D1
    D1 --> D2 --> D3 --> D4 --> D5
    D5 -->|Success| E1
    D5 -.->|Failure| F1
    E1 --> E2 --> E4 --> E5 --> E6
    E6 -.->|Failure| E7
    E6 --> MONITOR
```

---

## 2. CI/CD Pipeline Stages

### Stage 1: Continuous Integration (`test.yml`)

| Job | Runner | Duration | Key Steps |
|-----|--------|----------|-----------|
| **Security Audit** | `ubuntu-latest` | ~30s | `composer audit`, `npm audit`, `trivy fs --severity CRITICAL,HIGH` |
| **Code Style (Pint)** | `ubuntu-latest` | ~15s | `vendor/bin/pint --test --parallel` (584 files) |
| **Frontend Build** | `ubuntu-latest` | ~45s | `npm ci`, `npm run build`, cache `public/build` |
| **PHP Tests** | `ubuntu-latest` | ~2min | MySQL 8 service, `pest --parallel --processes=4` (336 tests) |
| **Validation Smoke** | `ubuntu-latest` | ~30s | Full HTTP integration tests |

**Triggers:** Push to any branch, Pull Request  
**Parallelism:** 4 jobs run simultaneously  
**Caching:** Composer (`vendor`), NPM (`node_modules`), Build artifacts (`public/build`)

---

### Stage 2: Docker Build & Scan (`deploy.yml`)

```mermaid
sequenceDiagram
    participant GH as GitHub Actions
    participant DB as Docker Hub
    participant TV as Trivy Scanner
    participant GHCR as GH Container Registry

    GH->>GH: Checkout code
    GH->>GH: Setup Buildx
    GH->>DB: Login (secrets)
    GH->>DB: Build & Push App Image (multi-stage)
    GH->>DB: Build & Push Nginx Image
    GH->>TV: Scan Laravel Image (fs + vuln)
    GH->>TV: Scan Nginx Image
    TV-->>GH: Report (fail on CRITICAL)
    GH->>DB: Tag as sha-<commit>
```

**Images Produced:**
| Image | Base | Size | Tags |
|-------|------|------|------|
| `solarshare/app` | `php:8.3-fpm-alpine` | ~450MB | `sha-<commit>`, `latest` |
| `solarshare/nginx` | `nginx:1.29-alpine` | ~50MB | `sha-<commit>`, `latest` |

---

### Stage 3: Staging Smoke Test

```mermaid
flowchart TD
    A[Start Stack] --> B[Wait MySQL]
    B --> C[Clear Caches]
    C --> D[Restart App]
    D --> E[Migrate --force]
    E --> F[Seed --force]
    F --> G[Cache Config/Route/View]
    G --> H[Storage Link]
    H --> I[Health Check /up]
    I -->|200 OK| J[Success ✅]
    I -->|Fail| K[Diagnose + Logs]
    J --> L[Tear Down -v]
    K --> L
```

**Environment Variables (Testing):**
```env
APP_ENV=testing
APP_DEBUG=true
CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
SCOUT_DRIVER=null
BROADCAST_CONNECTION=log
```

---

### Stage 4: Production Deployment

```mermaid
flowchart TD
    A[Trigger: Push to main\nor Manual Dispatch] --> B[Self-hosted Runner]
    B --> C[Checkout Source]
    C --> D[Docker Login]
    D --> E[Pull Images from Hub]
    E --> F[Backup Database]
    F --> G[Deploy Stack]
    G --> H[Migrate + Seed]
    H --> I[Health Check /up]
    I -->|200| J[Success ✅]
    I -->|Fail| K[Rollback to Previous Image]
    K --> L[Alert]
```

**Deployment Script (`local-compose.sh`):**
- Blue/Green via image tags
- Automatic rollback on health check failure
- Database backup before migration
- Zero-downtime via `docker compose up -d`

---

## 3. Infrastructure as Code

### Docker Compose Services (Production)

| Service | Image | Ports | Health Check |
|---------|-------|-------|--------------|
| **app** | `solarshare/app:sha-<commit>` | 9000 (PHP-FPM) | `php-fpm -F` |
| **web** | `solarshare/nginx:sha-<commit>` | 80/443 → 8080 | `wget /up` |
| **queue** | `solarshare/app` | — | `queue:work` |
| **scheduler** | `solarshare/app` | — | `schedule:work` |
| **reverb** | `solarshare/app` | 8080 (WS) | `reverb:start` |
| **mysql** | `mysql:8.0` | 3306 | `mysqladmin ping` |
| **redis** | `redis:7-alpine` | 6379 | `redis-cli ping` |
| **meilisearch** | `getmeili/meilisearch:v1.13` | 7700 | `curl /health` |
| **mailpit** | `axllent/mailpit:v1.21` | 1025, 8025 | — |

### Networks & Volumes
```yaml
networks:
  solarshare:  # Single bridge network
    driver: bridge

volumes:
  solarshare-storage:        # Private uploads
  solarshare-public-storage: # Public storage link
  solarshare-mysql:          # Database persistence
  solarshare-redis:          # Cache/sessions
  solarshare-meilisearch:    # Search index
```

---

## 4. Security & Quality Gates

| Gate | Tool | Threshold | Blocking |
|------|------|-----------|----------|
| **Dependencies** | Composer Audit | No HIGH/CRITICAL | ⚠️ Warning |
| **NPM Audit** | npm audit | No HIGH/CRITICAL | ⚠️ Warning |
| **Container Scan** | Trivy | No CRITICAL | ✅ Blocking |
| **Code Style** | Laravel Pint | 0 errors | ✅ Blocking |
| **Tests** | Pest PHP | 100% pass | ✅ Blocking |
| **Secrets** | GitLeaks (built-in) | No leaks | ✅ Blocking |

---

## 5. Environments

| Environment | Branch | URL | Purpose |
|-------------|--------|-----|---------|
| **Local** | Any | `localhost:8000` | Development |
| **Staging** | `dev` (CI) | `localhost:18000` (ephemeral) | Smoke test per PR |
| **Production** | `main` | `solarshare.example.com` | Live traffic |

---

## 6. Rollback Strategy

```bash
# Automatic (on health check failure):
# 1. Previous image tag stored before deploy
# 2. docker compose up -d with previous image
# 3. Health check re-verified

# Manual (if needed):
docker compose -p solarshare-production pull
docker compose -p solarshare-production up -d --remove-orphans
```

---

## 7. Key Metrics & SLAs

| Metric | Target | Current |
|--------|--------|---------|
| **CI Duration** | < 5 min | ~3 min |
| **Build Time** | < 8 min | ~6 min |
| **Deploy Time** | < 3 min | ~2 min |
| **Test Coverage** | > 80% | ~85% |
| **Zero-downtime Deploy** | Yes | ✅ |
| **Rollback Time** | < 2 min | ✅ |

---

## 8. Secrets Management

| Secret | Scope | Rotation |
|--------|-------|----------|
| `DOCKERHUB_USERNAME` | Org | N/A |
| `DOCKERHUB_TOKEN` | Org | 90 days |
| `APP_KEY` | Per env | On deploy |
| `DB_PASSWORD` | Per env | 90 days |
| `STRIPE_SECRET_KEY` | Prod only | 90 days |
| `AI_*_KEY` | Per env | As needed |

---

## 9. Diagrams for Presentation

### 9.1 Simplified Pipeline (Slide-Ready)

```mermaid
flowchart LR
    DEV[💻 Dev\nPush] --> CI[⚙️ CI\nTest+Lint+Build]
    CI -->|Pass| DOCKER[🐳 Build Images\nScan]
    DOCKER --> STAGE[🧪 Staging\nSmoke Test]
    STAGE -->|Pass| PROD[🚀 Production\nDeploy]
    STAGE -.->|Fail| FIX[🔧 Fix & Retry]
    PROD --> MON[📊 Monitor\nHealth/Errors]
```

### 9.2 Deployment Flow Detail

```mermaid
sequenceDiagram
    actor Dev as Developer
    participant GH as GitHub Actions
    participant DH as Docker Hub
    participant SR as Self-hosted Runner
    participant PROD as Production Server

    Dev->>GH: git push main
    GH->>GH: Run test.yml (CI)
    GH->>DH: Build & Push Images
    GH->>GH: Trivy Scan
    GH->>GH: Staging Smoke Test
    GH->>SR: Trigger deploy-production
    SR->>DH: Pull Images
    SR->>PROD: Backup DB
    SR->>PROD: Deploy Stack
    SR->>PROD: Migrate + Seed
    SR->>PROD: Health Check
    alt Success
        SR->>MON: Monitor
    else Failure
        SR->>PROD: Rollback
        SR->>Dev: Alert
    end
```

---

## 10. Appendix: Commands Reference

### Local Development
```bash
# Start all services
docker compose up -d

# View logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test

# Rebuild after changes
docker compose up -d --build
```

### CI/CD Manual Triggers
```bash
# Run CI pipeline
gh workflow run test.yml --ref <branch>

# Run full deploy (staging + prod)
gh workflow run "Deploy Production" --ref main

# Check runner status
gh api repos/OWNER/REPO/actions/runners
```

### Production Server (Self-hosted Runner)
```bash
# Install runner (once)
mkdir actions-runner && cd actions-runner
curl -o runner.tar.gz -L https://github.com/actions/runner/releases/download/v2.329.0/actions-runner-linux-x64-2.329.0.tar.gz
tar xzf runner.tar.gz
./config.sh --url https://github.com/OWNER/REPO --token <TOKEN>
./run.sh
```

---

**Document Version:** 1.0  
**Prepared by:** SolarShare Team  
**Validation Date:** [To be filled]

---

*This document describes the complete DevOps pipeline implemented for SolarShare, from code commit to production deployment, including all quality gates, infrastructure definitions, and rollback procedures.*