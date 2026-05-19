#!/usr/bin/env bash

set -euo pipefail
umask 077

# Safe MariaDB dump template.
# Copy this file to a local, non-versioned script and adapt placeholders.
# DRY_RUN=1 is the default and must be tested before real execution.

DRY_RUN="${DRY_RUN:-1}"
DB_CONTAINER="${DB_CONTAINER:-}"
DB_NAME="${DB_NAME:-}"
DB_USER="${DB_USER:-}"
DB_PASSWORD_FILE="${DB_PASSWORD_FILE:-}"
DB_PASSWORD_ENV="${DB_PASSWORD_ENV:-}"
BACKUP_DIR="${BACKUP_DIR:-}"
BACKUP_PREFIX="${BACKUP_PREFIX:-mariadb-backup}"

die() {
  printf 'ERROR: %s\n' "$*" >&2
  exit 1
}

run() {
  printf '+ %q' "$1"
  shift || true
  printf ' %q' "$@"
  printf '\n'

  if [[ "$DRY_RUN" != "1" ]]; then
    "$@"
  fi
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

require_var DB_CONTAINER
require_var DB_NAME
require_var DB_USER
validate_backup_dir

if [[ -z "$DB_PASSWORD_FILE" && -z "$DB_PASSWORD_ENV" ]]; then
  die "Set DB_PASSWORD_FILE or DB_PASSWORD_ENV; do not hardcode passwords"
fi

timestamp="$(date +%Y%m%d-%H%M%S)"
output_file="$BACKUP_DIR/${BACKUP_PREFIX}_${timestamp}.sql"

printf 'MariaDB backup template\n'
printf 'Mode: %s\n' "$([[ "$DRY_RUN" == "1" ]] && echo DRY_RUN || echo EXECUTE)"
printf 'Destination file: %s\n' "$output_file"

if [[ "$DRY_RUN" == "1" ]]; then
  printf 'Would create backup directory with restrictive permissions.\n'
  printf 'Would run mariadb-dump inside the configured container.\n'
  printf 'Would verify the resulting dump is not empty.\n'
  exit 0
fi

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

if [[ -n "$DB_PASSWORD_FILE" ]]; then
  [[ -r "$DB_PASSWORD_FILE" ]] || die "DB_PASSWORD_FILE is not readable"
  db_password="$(<"$DB_PASSWORD_FILE")"
else
  db_password="$DB_PASSWORD_ENV"
fi

[[ -n "$db_password" ]] || die "Database password is empty"

docker exec \
  -e "MYSQL_PWD=$db_password" \
  "$DB_CONTAINER" \
  mariadb-dump -u "$DB_USER" "$DB_NAME" > "$output_file"

[[ -s "$output_file" ]] || die "Backup file is empty"
chmod 600 "$output_file"

printf 'Backup created safely.\n'
