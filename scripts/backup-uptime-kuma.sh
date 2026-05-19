#!/usr/bin/env bash

set -euo pipefail
umask 077

DRY_RUN="${DRY_RUN:-1}"
CONFIRM_STOP="${CONFIRM_STOP:-no}"
BACKUP_DIR="${BACKUP_DIR:-$HOME/HomeLab/backups/uptime-kuma}"
BACKUP_PREFIX="${BACKUP_PREFIX:-uptime-kuma-data}"
STACK_DIR="${STACK_DIR:-$HOME/HomeLab/stacks/fase-1-mvp}"
SERVICE_NAME="${SERVICE_NAME:-uptime-kuma}"
DATA_VOLUME="${DATA_VOLUME:-fase-1-mvp_uptime_kuma_data}"

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
    printf 'Intentando arrancar %s tras un fallo.\n' "$SERVICE_NAME" >&2
    docker compose start "$SERVICE_NAME" || true
  fi
  exit "$exit_code"
}

validate_backup_dir() {
  [[ -n "$BACKUP_DIR" ]] || die "BACKUP_DIR no puede estar vacío"
  [[ "$BACKUP_DIR" != "/" ]] || die "BACKUP_DIR no puede ser /"
  [[ "$BACKUP_DIR" != "." ]] || die "BACKUP_DIR debe ser explícito"
  [[ "$BACKUP_DIR" != ".." ]] || die "BACKUP_DIR debe ser explícito"
}

validate_stack_dir() {
  [[ -n "$STACK_DIR" ]] || die "STACK_DIR no puede estar vacío"
  [[ -d "$STACK_DIR" ]] || die "STACK_DIR no existe"
  [[ -f "$STACK_DIR/docker-compose.yml" || -f "$STACK_DIR/compose.yml" ]] || die "No se encontró docker-compose.yml ni compose.yml"
}

validate_backup_dir
validate_stack_dir
[[ -n "$SERVICE_NAME" ]] || die "SERVICE_NAME no puede estar vacío"
[[ -n "$DATA_VOLUME" ]] || die "DATA_VOLUME no puede estar vacío"

DATE="$(date +%Y-%m-%d_%H-%M-%S)"
FILE="$BACKUP_DIR/${BACKUP_PREFIX}_$DATE.tar.gz"
TMP_NAME="${BACKUP_PREFIX}_$DATE.tar.gz.tmp"
TMP_FILE="$BACKUP_DIR/$TMP_NAME"
FINAL_NAME="$(basename "$FILE")"

echo "=== Backup Uptime Kuma ==="
echo "Modo: $([[ "$DRY_RUN" == "1" ]] && echo DRY_RUN || echo EJECUCIÓN)"
echo "Stack: $STACK_DIR"
echo "Servicio: $SERVICE_NAME"
echo "Destino: $FILE"
echo

if [[ "$DRY_RUN" == "1" ]]; then
  echo "DRY_RUN activo: no se parará el servicio, no se ejecutará Docker y no se creará backup."
  echo "Se ejecutaría:"
  show_cmd mkdir -p "$BACKUP_DIR"
  show_cmd chmod 700 "$BACKUP_DIR"
  show_cmd cd "$STACK_DIR"
  show_cmd docker compose stop "$SERVICE_NAME"
  show_cmd docker run --rm --user "$(id -u):$(id -g)" -v "$DATA_VOLUME":/data:ro -v "$BACKUP_DIR":/backup alpine sh -c "tar czf /backup/$TMP_NAME -C /data ."
  show_cmd docker compose start "$SERVICE_NAME"
  echo "Después se validaría el tarball y se renombraría el fichero temporal."
  exit 0
fi

[[ "$CONFIRM_STOP" == "yes" ]] || die "Define CONFIRM_STOP=yes para permitir la parada temporal del servicio"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"
cd "$STACK_DIR"

trap restore_service_on_error ERR

echo "Parando $SERVICE_NAME para backup consistente..."
run docker compose stop "$SERVICE_NAME"
SERVICE_STOPPED=1

echo "Creando backup del volumen persistente..."
run docker run --rm \
  --user "$(id -u):$(id -g)" \
  -v "$DATA_VOLUME":/data:ro \
  -v "$BACKUP_DIR":/backup \
  alpine \
  sh -c "tar czf /backup/$TMP_NAME -C /data ."

[[ -s "$TMP_FILE" ]] || die "El tarball temporal está vacío"
mv "$TMP_FILE" "$FILE"
chmod 600 "$FILE"

echo "Arrancando $SERVICE_NAME..."
run docker compose start "$SERVICE_NAME"
SERVICE_STOPPED=0
trap - ERR

[[ -s "$FILE" ]] || die "El backup final está vacío"

echo "Backup creado:"
echo "$FILE"
ls -lh "$FILE"
