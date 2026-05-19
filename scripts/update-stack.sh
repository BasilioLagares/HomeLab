#!/usr/bin/env bash

set -euo pipefail

DRY_RUN="${DRY_RUN:-1}"
CONFIRM_UPDATE="${CONFIRM_UPDATE:-no}"
REQUIRE_BACKUP_CONFIRMATION="${REQUIRE_BACKUP_CONFIRMATION:-yes}"
STACK_DIR="${STACK_DIR:-$HOME/HomeLab/stacks/fase-1-mvp}"
STACK_NAME="${STACK_NAME:-fase-1-mvp}"
ALLOWED_STACKS="${ALLOWED_STACKS:-fase-1-mvp}"

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

confirm_yes() {
  local prompt="$1"
  local answer
  read -r -p "$prompt [escribe YES]: " answer
  [[ "$answer" == "YES" ]] || die "Confirmación no concedida"
}

stack_allowed() {
  local allowed
  IFS=',' read -ra allowed <<< "$ALLOWED_STACKS"
  for allowed in "${allowed[@]}"; do
    [[ "$STACK_NAME" == "$allowed" ]] && return 0
  done
  return 1
}

validate_config() {
  [[ -n "$STACK_DIR" ]] || die "STACK_DIR no puede estar vacío"
  [[ -n "$STACK_NAME" ]] || die "STACK_NAME no puede estar vacío"
  [[ -d "$STACK_DIR" ]] || die "STACK_DIR no existe"
  [[ -f "$STACK_DIR/docker-compose.yml" || -f "$STACK_DIR/compose.yml" ]] || die "No se encontró docker-compose.yml ni compose.yml"
  stack_allowed || die "STACK_NAME no está en ALLOWED_STACKS"
}

validate_config

echo "=== Proyecto HomeLab - Actualización controlada ==="
echo "Stack: $STACK_NAME"
echo "Directorio: $STACK_DIR"
echo "Modo: $([[ "$DRY_RUN" == "1" ]] && echo DRY_RUN || echo EJECUCIÓN)"
echo
echo "Este script puede descargar imágenes y recrear servicios con docker compose up -d."
echo "No usa docker compose down."
echo
echo "Recomendación: confirma backups recientes antes de ejecutar en modo real."
echo

cd "$STACK_DIR"

echo "=== Estado previo de contenedores ==="
run docker compose ps
echo

if [[ "$DRY_RUN" == "1" ]]; then
  echo "DRY_RUN activo: no se ejecutará pull ni up -d."
  echo "Se ejecutaría:"
  show_cmd docker compose pull
  show_cmd docker compose up -d
  echo
  echo "Post-check conceptual:"
  show_cmd docker compose ps
  show_cmd docker compose logs --tail=100
  exit 0
fi

[[ "$CONFIRM_UPDATE" == "yes" ]] || die "Define CONFIRM_UPDATE=yes para permitir la actualización real"

if [[ "$REQUIRE_BACKUP_CONFIRMATION" == "yes" ]]; then
  confirm_yes "Confirma que los backups necesarios están hechos"
fi

confirm_yes "Confirma actualización real del stack $STACK_NAME"

echo "=== Descargando imágenes nuevas ==="
run docker compose pull

echo
echo "=== Recreando servicios ==="
run docker compose up -d

echo
echo "=== Estado posterior de contenedores ==="
run docker compose ps

echo
echo "Actualización finalizada. Revisa healthchecks y logs de forma local."
