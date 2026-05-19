#!/usr/bin/env bash

set -euo pipefail
umask 077

BACKUP_DIR="$HOME/HomeLab/backups/mariadb"
DB_CONTAINER="${DB_CONTAINER:-homelab_mariadb}"
DB_NAME="${DB_NAME:-homelab_demo}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD_FILE="${DB_PASSWORD_FILE:-scripts/.secrets/mariadb-backup-password}"
BACKUP_PREFIX="${BACKUP_PREFIX:-homelab_demo}"

die() {
  printf 'ERROR: %s\n' "$*" >&2
  exit 1
}

validate_backup_dir() {
  [[ -n "$BACKUP_DIR" ]] || die "BACKUP_DIR no puede estar vacío"
  [[ "$BACKUP_DIR" != "/" ]] || die "BACKUP_DIR no puede ser /"
  [[ "$BACKUP_DIR" != "." ]] || die "BACKUP_DIR debe ser explícito"
  [[ "$BACKUP_DIR" != ".." ]] || die "BACKUP_DIR debe ser explícito"
}

validate_backup_dir

[[ -f "$DB_PASSWORD_FILE" ]] || die "No existe el fichero local de secreto DB_PASSWORD_FILE"
[[ -r "$DB_PASSWORD_FILE" ]] || die "El fichero local de secreto no es legible"

db_password="$(<"$DB_PASSWORD_FILE")"
[[ -n "$db_password" ]] || die "El fichero local de secreto está vacío"

DATE="$(date +%Y-%m-%d_%H-%M-%S)"
FILE="$BACKUP_DIR/${BACKUP_PREFIX}_$DATE.sql"
TMP_FILE="$FILE.tmp"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

echo "Creando backup de MariaDB..."

if docker exec \
  -e "MYSQL_PWD=$db_password" \
  "$DB_CONTAINER" \
  mariadb-dump -u "$DB_USER" "$DB_NAME" > "$TMP_FILE"; then
  [[ -s "$TMP_FILE" ]] || die "El dump generado está vacío"
  mv "$TMP_FILE" "$FILE"
  chmod 600 "$FILE"
else
  rm -f "$TMP_FILE"
  die "Falló el dump de MariaDB"
fi

echo "Backup creado:"
echo "$FILE"

ls -lh "$FILE"
