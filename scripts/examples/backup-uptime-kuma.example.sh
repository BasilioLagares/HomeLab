#!/usr/bin/env bash

set -euo pipefail
umask 077

# Safe persistent-data backup template.
# This may temporarily stop a service when CONFIRM_STOP=yes and DRY_RUN=0.
# Copy locally and adapt placeholders before use.

DRY_RUN="${DRY_RUN:-1}"
CONFIRM_STOP="${CONFIRM_STOP:-no}"
STACK_DIR="${STACK_DIR:-}"
SERVICE_NAME="${SERVICE_NAME:-}"
DATA_VOLUME="${DATA_VOLUME:-}"
BACKUP_DIR="${BACKUP_DIR:-}"
BACKUP_PREFIX="${BACKUP_PREFIX:-persistent-data-backup}"

SERVICE_STOPPED=0

die() {
  printf 'ERROR: %s\n' "$*" >&2
  exit 1
}

show_cmd() {
  printf '+'
  printf ' %q' "$@"
  printf '\n'
}

run() {
  show_cmd "$@"
  if [[ "$DRY_RUN" != "1" ]]; then
    "$@"
  fi
}

restore_service_on_error() {
  local exit_code=$?
  if [[ "$SERVICE_STOPPED" == "1" ]]; then
    printf 'Attempting to start service after failure.\n' >&2
    docker compose start "$SERVICE_NAME" || true
  fi
  exit "$exit_code"
}

require_var() {
  local name="$1"
  local value="${!name:-}"
  [[ -n "$value" ]] || die "$name is required"
}

validate_backup_dir() {
  [[ -n "$BACKUP_DIR" ]] || die "BACKUP_DIR is required"
  [[ "$BACKUP_DIR" != "/" ]] || die "BACKUP_DIR must not be root"
  [[ "$BACKUP_DIR" != "." ]] || die "BACKUP_DIR must be explicit"
  [[ "$BACKUP_DIR" != ".." ]] || die "BACKUP_DIR must be explicit"
}

require_var STACK_DIR
require_var SERVICE_NAME
require_var DATA_VOLUME
validate_backup_dir

timestamp="$(date +%Y%m%d-%H%M%S)"
output_file="$BACKUP_DIR/${BACKUP_PREFIX}_${timestamp}.tar.gz"

printf 'Persistent-data backup template\n'
printf 'Mode: %s\n' "$([[ "$DRY_RUN" == "1" ]] && echo DRY_RUN || echo EXECUTE)"
printf 'Destination file: %s\n' "$output_file"

if [[ "$DRY_RUN" == "1" ]]; then
  printf 'Would validate stack directory and backup destination.\n'
  printf 'Would optionally stop the configured service if CONFIRM_STOP=yes.\n'
  printf 'Would archive the configured data volume into a tarball.\n'
  printf 'Would verify the resulting tarball is not empty.\n'
  exit 0
fi

[[ "$CONFIRM_STOP" == "yes" ]] || die "Set CONFIRM_STOP=yes to allow temporary service stop"
[[ -d "$STACK_DIR" ]] || die "STACK_DIR does not exist"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"
cd "$STACK_DIR"

trap restore_service_on_error ERR

run docker compose stop "$SERVICE_NAME"
SERVICE_STOPPED=1

run docker run --rm \
  -v "$DATA_VOLUME":/data:ro \
  -v "$BACKUP_DIR":/backup \
  alpine \
  sh -c "tar czf /backup/${BACKUP_PREFIX}_${timestamp}.tar.gz -C /data ."

run docker compose start "$SERVICE_NAME"
SERVICE_STOPPED=0
trap - ERR

[[ -s "$output_file" ]] || die "Backup tarball is empty"
chmod 600 "$output_file"

printf 'Backup created safely.\n'

# Optional rotation should be implemented only after validating BACKUP_DIR.
# Never use rm -rf. Prefer find with maxdepth, exact prefix and explicit review.
