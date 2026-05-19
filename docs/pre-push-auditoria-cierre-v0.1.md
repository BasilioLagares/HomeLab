# Cierre auditoría pre-push v0.1

Fecha aproximada: 19 de mayo de 2026.

## Objetivo

Validar el estado del repositorio local antes de cualquier remoto o push futuro.

La auditoría confirma que no hay secretos ni artefactos peligrosos trackeados y
documenta las advertencias pendientes antes de una posible publicación remota.

## Resultado general

- Rama actual: `main`.
- Remote configurado: ninguno.
- Último commit revisado: `45d47ae docs: cierra saneamiento documental`.
- Archivos trackeados: `48`.
- Estado del repositorio local: apto con advertencias.
- No se hizo push.
- No se creó remote.

## Archivos peligrosos no trackeados

Siguen presentes localmente y no deben añadirse tal cual:

- Docs antiguos sensibles.
- Compose real de War Room.
- WireGuard real.
- Proxy real.
- Backups.
- Runtime.
- Certs.
- `scripts/.secrets/`.

## Archivos peligrosos trackeados

No se detectaron archivos peligrosos trackeados.

La revisión no encontró:

- `.env` trackeado.
- Backups trackeados.
- Dumps trackeados.
- Archivos comprimidos de backup trackeados.
- Certs trackeado.
- WireGuard real trackeado.
- Runtime trackeado.
- Proxy real trackeado.
- Stacks trackeado.
- Apps trackeado.
- Compose real de War Room trackeado.
- Caddyfile real trackeado.
- Bloques de clave privada o certificado trackeados.

## Ignorados confirmados

La política de `.gitignore` mantiene fuera:

- `apps/`
- `backups/`
- `certs/`
- `platform/wireguard/`
- `proxy/`
- `runtime/`
- `scripts/.secrets/`
- `stacks/`
- `tools/backups/`
- Dumps.
- Tarballs.
- Backups temporales.
- Logs, caches y runtime generado.

## Falsos positivos

Se detectaron términos sensibles solo en contexto defensivo o saneado:

- `password` y `secret` aparecen en documentación de política.
- `WireGuard` aparece en manuales saneados.
- `basic_auth` aparece como referencia defensiva.
- `MYSQL_PWD` se usa sin valor hardcodeado y leyendo el secreto desde fichero
  local no versionado.
- `127.0.0.1` no se considera IP sensible de red.
- Referencias a dumps y tarballs aparecen como documentación, no como archivos
  trackeados.

## Veredicto

Categoría: apto con advertencias.

Motivo: lo trackeado está saneado y no se detectaron secretos reales ni
artefactos peligrosos. La advertencia principal es que siguen existiendo
archivos locales sensibles sin trackear; un `git add .` o una selección
descuidada podría introducirlos por error.

## Reglas antes de cualquier push

- No usar `git add .`.
- Revisar `git status --short`.
- Revisar `git diff --cached --name-only`.
- Revisar `git ls-files`.
- Preferir repositorio privado si se crea remoto.
- Repetir auditoría antes de un push real.
- No subir docs antiguos sensibles.
- No subir compose real.
- No subir secretos, backups, certificados, WireGuard real, runtime ni datos
  persistentes.

## Pendientes

- Decidir si borrar o archivar localmente docs antiguos sensibles.
- Valorar crear un script de auditoría pre-push.
- Valorar rotación de credencial MariaDB si procede.
- Mantener seguridad global: auth, permisos, auditoría y secretos.
