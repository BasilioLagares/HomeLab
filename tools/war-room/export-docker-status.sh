#!/usr/bin/env bash
set -euo pipefail

OUT_DIR="/home/basilio/HomeLab/runtime/war-room"
OUT_FILE="${OUT_DIR}/docker-status.json"
TMP_FILE="${OUT_FILE}.tmp"

mkdir -p "$OUT_DIR"

if ! command -v docker >/dev/null 2>&1; then
  jq -n \
    --arg last_update "$(date --iso-8601=seconds)" \
    '{
      last_update: $last_update,
      source: "host_exporter",
      state: "unavailable",
      containers_total: null,
      containers_running: null,
      containers_exited: null,
      items: [],
      error: "docker_command_not_found"
    }' > "$TMP_FILE"

  mv "$TMP_FILE" "$OUT_FILE"
  exit 0
fi

if ! docker info >/dev/null 2>&1; then
  jq -n \
    --arg last_update "$(date --iso-8601=seconds)" \
    '{
      last_update: $last_update,
      source: "host_exporter",
      state: "unavailable",
      containers_total: null,
      containers_running: null,
      containers_exited: null,
      items: [],
      error: "docker_daemon_unavailable"
    }' > "$TMP_FILE"

  mv "$TMP_FILE" "$OUT_FILE"
  exit 0
fi

docker ps -a --format '{{json .}}' \
  | jq -s \
      --arg last_update "$(date --iso-8601=seconds)" \
      '{
        last_update: $last_update,
        source: "host_exporter",
        state: "operational",
        containers_total: length,
        containers_running: map(select(.State == "running")) | length,
        containers_exited: map(select(.State == "exited")) | length,
        items: map({
          id: .ID,
          name: .Names,
          image: .Image,
          state: .State,
          status: .Status,
          ports: .Ports,
        })
      }' > "$TMP_FILE"

mv "$TMP_FILE" "$OUT_FILE"
