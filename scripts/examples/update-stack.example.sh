#!/usr/bin/env bash

set -euo pipefail

# Safe stack update template.
# This is an operation with impact. DRY_RUN=1 is the default.
# Copy locally and adapt placeholders before use.

DRY_RUN="${DRY_RUN:-1}"
REQUIRE_CLEAN_GIT="${REQUIRE_CLEAN_GIT:-1}"
REQUIRE_BACKUP_CONFIRMATION="${REQUIRE_BACKUP_CONFIRMATION:-1}"
STACK_DIR="${STACK_DIR:-}"
STACK_NAME="${STACK_NAME:-example-stack}"

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

require_var() {
  local name="$1"
  local value="${!name:-}"
  [[ -n "$value" ]] || die "$name is required"
}

confirm() {
  local prompt="$1"
  local answer
  read -r -p "$prompt [type YES]: " answer
  [[ "$answer" == "YES" ]] || die "Confirmation not granted"
}

require_var STACK_DIR

[[ -d "$STACK_DIR" ]] || die "STACK_DIR does not exist"
[[ -f "$STACK_DIR/docker-compose.yml" || -f "$STACK_DIR/compose.yml" ]] || die "No compose file found"

printf 'Stack update template\n'
printf 'Stack: %s\n' "$STACK_NAME"
printf 'Mode: %s\n' "$([[ "$DRY_RUN" == "1" ]] && echo DRY_RUN || echo EXECUTE)"

if [[ "$REQUIRE_CLEAN_GIT" == "1" ]]; then
  run git -C "$STACK_DIR" status --short
  if [[ "$DRY_RUN" != "1" && -n "$(git -C "$STACK_DIR" status --short)" ]]; then
    die "Working tree is not clean"
  fi
fi

if [[ "$REQUIRE_BACKUP_CONFIRMATION" == "1" && "$DRY_RUN" != "1" ]]; then
  confirm "Confirm that required backups have been completed"
fi

if [[ "$DRY_RUN" == "1" ]]; then
  printf 'Would pull updated images for the configured stack.\n'
  printf 'Would recreate services with docker compose up -d.\n'
  printf 'Would run read-only post-checks.\n'
  exit 0
fi

cd "$STACK_DIR"

confirm "Proceed with docker compose pull and up -d for this stack"

run docker compose pull
run docker compose up -d

printf 'Post-checks to run manually:\n'
printf '- docker compose ps\n'
printf '- docker compose logs --tail=100\n'
printf '- service healthcheck URLs\n'

# This template intentionally does not use docker compose down.
