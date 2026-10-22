#!/usr/bin/env bash
set -Eeuo pipefail

environment="${1:?environment is required}"
image_tag="${2:?image tag is required}"

case "$environment" in
  production) ;;
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
web_image="docker.io/$username/solarshare-nginx:$image_tag"
compose_args=(--project-name "solarshare-$environment" --project-directory "$deploy_dir")
previous_image=""
previous_web_image=""
app_container="$(docker compose "${compose_args[@]}" ps -q app 2>/dev/null | head -n 1 || true)"
if [[ -n "$app_container" ]]; then
  previous_image="$(docker inspect --format '{{.Config.Image}}' "$app_container" 2>/dev/null || true)"
fi
web_container="$(docker compose "${compose_args[@]}" ps -q web 2>/dev/null | head -n 1 || true)"
if [[ -n "$web_container" ]]; then
  previous_web_image="$(docker inspect --format '{{.Config.Image}}' "$web_container" 2>/dev/null || true)"
fi

rollback() {
  exit_code=$?
  if [[ "$exit_code" -ne 0 && -n "$previous_image" && "$previous_image" != "$image" ]]; then
    echo "Deployment failed; rolling back to $previous_image" >&2
    export APP_IMAGE="$previous_image"
    if [[ -n "$previous_web_image" ]]; then
      export WEB_IMAGE="$previous_web_image"
    fi
    docker compose "${compose_args[@]}" up -d --remove-orphans || true
  fi
  exit "$exit_code"
}
trap rollback EXIT

docker pull "$image"
docker pull "$web_image"
export APP_IMAGE="$image"
export WEB_IMAGE="$web_image"
docker compose "${compose_args[@]}" up -d --remove-orphans
docker compose "${compose_args[@]}" exec -T app php artisan backup:database --disk=local --path=backups/database
docker compose "${compose_args[@]}" exec -T app php artisan migrate --force
docker compose "${compose_args[@]}" exec -T app php artisan db:seed --force
docker compose "${compose_args[@]}" ps
curl --fail --retry 10 --retry-delay 3 "http://127.0.0.1:$app_port/up"
trap - EXIT
