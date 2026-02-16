#!/usr/bin/env bash
set -euo pipefail

LANGUAGE="${1:-}"
WORKDIR="${2:-}"
TIMEOUT_SECONDS="${3:-5}"
MEMORY_LIMIT="${4:-128m}"
CPU_LIMIT="${5:-0.50}"
PIDS_LIMIT="${6:-64}"

if [[ -z "$LANGUAGE" || -z "$WORKDIR" || -z "$TIMEOUT_SECONDS" || -z "$MEMORY_LIMIT" || -z "$CPU_LIMIT" || -z "$PIDS_LIMIT" ]]; then
  echo "Usage: run_in_sandbox.sh <language> <workdir> <timeout_seconds> <memory_limit> <cpu_limit> <pids_limit>" >&2
  exit 2
fi

if [[ ! -d "$WORKDIR" ]]; then
  echo "Sandbox workdir not found." >&2
  exit 2
fi

case "$LANGUAGE" in
  python)
    IMAGE="codeoven-sandbox-python:latest"
    SRC_FILE="Main.py"
    RUN_CMD='python3 -I /sandbox/Main.py < /sandbox/stdin.txt'
    ;;
  php)
    IMAGE="codeoven-sandbox-php:latest"
    SRC_FILE="Main.php"
    RUN_CMD='php -d expose_php=0 -d display_errors=1 -d log_errors=0 /sandbox/Main.php < /sandbox/stdin.txt'
    ;;
  cpp)
    IMAGE="codeoven-sandbox-cpp:latest"
    SRC_FILE="Main.cpp"
    RUN_CMD='g++ /sandbox/Main.cpp -O2 -pipe -std=c++17 -o /sandbox/a.out && /sandbox/a.out < /sandbox/stdin.txt'
    ;;
  *)
    echo "Unsupported language: $LANGUAGE" >&2
    exit 2
    ;;
esac

if [[ ! -f "$WORKDIR/$SRC_FILE" ]]; then
  echo "Source file not found: $SRC_FILE" >&2
  exit 2
fi

if [[ ! -f "$WORKDIR/stdin.txt" ]]; then
  : > "$WORKDIR/stdin.txt"
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker CLI is required but not available." >&2
  exit 127
fi

if ! docker image inspect "$IMAGE" >/dev/null 2>&1; then
  echo "Sandbox image not found: $IMAGE. Prebuild images before serving requests." >&2
  exit 125
fi

chmod 700 "$WORKDIR"
chmod 600 "$WORKDIR/$SRC_FILE" "$WORKDIR/stdin.txt"

set +e
docker run --rm \
  --name "codeoven-run-$(date +%s)-$RANDOM" \
  --network none \
  --read-only \
  --pids-limit "$PIDS_LIMIT" \
  --memory "$MEMORY_LIMIT" \
  --cpus "$CPU_LIMIT" \
  --cap-drop ALL \
  --security-opt no-new-privileges \
  --security-opt seccomp=default \
  --ulimit nofile=64:64 \
  --ulimit nproc=64:64 \
  --ulimit cpu="${TIMEOUT_SECONDS}:${TIMEOUT_SECONDS}" \
  --user 10001:10001 \
  --tmpfs /tmp:rw,nosuid,nodev,noexec,size=64m \
  --tmpfs /run:rw,nosuid,nodev,noexec,size=16m \
  --mount "type=bind,src=$WORKDIR,dst=/sandbox,rw" \
  --workdir /sandbox \
  "$IMAGE" \
  sh -lc "$RUN_CMD"
STATUS=$?
set -e

if [[ -f "$WORKDIR/a.out" ]]; then
  rm -f "$WORKDIR/a.out" || true
fi

exit "$STATUS"
