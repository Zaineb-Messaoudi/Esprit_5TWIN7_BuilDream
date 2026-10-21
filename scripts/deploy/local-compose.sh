#!/usr/bin/env bash
set -Eeuo pipefail

environment="${1:?environment is required}"
image_tag="${2:?image tag is required}"

case "$environment" in
  staging|production) ;;
  *) echo "Unsupported environment: $environment" >&2; exit 1 ;;
esac

if [[ "$image_tag" == sha-* ]]; then
  image_tag="sha-${image_tag#sha-}"
  image_tag="${image_tag:0:11}"
fi

username="$(printf '%s' "${DOCKERHUB_USERNAME:-}" | tr -d '[:space:]' | tr '[:upper:]' '[:lower:]')"
test -n "$username"
test -n "${DOCKERHUB_TOKEN:-}"

deploy_dir="$HOME/solarshare-$environment"
test -f "$deploy_dir/.env" || {
  echo "Missing $deploy_dir/.env. Create it once before deploying." >&2
  exit 1
}

app_port="$(sed -n 's/^APP_PORT=//p' "$deploy_dir/.env" | tail -n 1 | tr -d '\r"' | tr -d "'")"
app_port="${app_port:-8000}"

cp docker-compose.yml "$deploy_dir/docker-compose.yml"
printf '%s' "$DOCKERHUB_TOKEN" | docker login --username "$username" --password-stdin

image="docker.io/$username/solarshare:$image_tag"
docker pull "$image"
export APP_IMAGE="$image"
docker compose --project-name "solarshare-$environment" --project-directory "$deploy_dir" up -d --remove-orphans
docker compose --project-name "solarshare-$environment" --project-directory "$deploy_dir" exec -T app php artisan migrate --force
docker compose --project-name "solarshare-$environment" --project-directory "$deploy_dir" ps
curl --fail --retry 10 --retry-delay 3 "http://127.0.0.1:$app_port/up"
