#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/docker-compose.sandbox.yml"

if ! command -v docker >/dev/null 2>&1; then
  echo "docker is required." >&2
  exit 1
fi

if docker compose version >/dev/null 2>&1; then
  docker compose -f "$COMPOSE_FILE" --profile sandbox-build build --pull
else
  docker-compose -f "$COMPOSE_FILE" --profile sandbox-build build --pull
fi

echo "Sandbox images built:"
docker image ls codeoven-sandbox-python:latest codeoven-sandbox-php:latest codeoven-sandbox-cpp:latest
