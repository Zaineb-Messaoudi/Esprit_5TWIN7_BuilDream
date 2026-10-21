#!/usr/bin/env bash
set -Eeuo pipefail

# One-time setup for a fresh Ubuntu Docker host.
# Run as root, or with sudo available. This script never creates a production
# .env and never receives GitHub secrets.

DEPLOY_PATH="${DEPLOY_PATH:-/opt/solarshare}"
DEPLOY_USER="${DEPLOY_USER:-solarshare}"
REPOSITORY_RAW_URL="${REPOSITORY_RAW_URL:-https://raw.githubusercontent.com/Zaineb-Messaoudi/Esprit_5TWIN7_BuilDream/dev}"

if [[ "$(id -u)" -ne 0 ]]; then
    echo "Run this script as root: sudo bash bootstrap-ubuntu.sh" >&2
    exit 1
fi

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y ca-certificates curl git

if ! command -v docker >/dev/null 2>&1; then
    curl -fsSL https://get.docker.com | sh
fi

systemctl enable --now docker

if ! id "$DEPLOY_USER" >/dev/null 2>&1; then
    useradd --create-home --shell /bin/bash "$DEPLOY_USER"
fi

usermod -aG docker "$DEPLOY_USER"
install -d -o "$DEPLOY_USER" -g docker -m 0750 "$DEPLOY_PATH"

curl --fail --location "$REPOSITORY_RAW_URL/docker-compose.yml" \
    --output "$DEPLOY_PATH/docker-compose.yml"
curl --fail --location "$REPOSITORY_RAW_URL/.env.example" \
    --output "$DEPLOY_PATH/.env.example"

if [[ ! -f "$DEPLOY_PATH/.env" ]]; then
    install -o "$DEPLOY_USER" -g docker -m 0600 /dev/null "$DEPLOY_PATH/.env"
    echo "Created an empty $DEPLOY_PATH/.env. Fill it before deployment."
fi

chown "$DEPLOY_USER:$DEPLOY_USER" "$DEPLOY_PATH/.env.example"
echo "Docker host prepared at $DEPLOY_PATH for user $DEPLOY_USER."
echo "Log in to GHCR, populate the private .env, then configure GitHub Environment secrets."
