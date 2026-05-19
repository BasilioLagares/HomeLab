# Nota archivo local sensible v0.1

Fecha aproximada: 19 de mayo de 2026.

## Objetivo

Los documentos antiguos sensibles se han movido a un archivo local ignorado para
reducir el riesgo de añadirlos accidentalmente al repositorio.

La carpeta usada para ese archivo local es:

- `_local_sensitive_archive/`

Esta carpeta está ignorada por Git y no debe versionarse.

## Motivo

Los documentos antiguos pueden contener información operativa o privada que no
debe publicarse tal cual.

No deben entrar en Git como documentación versionada sin una revisión y
saneamiento explícitos.

## Documentación saneada equivalente

Ya existen documentos saneados y versionables que cubren las partes útiles:

- `docs/backups-configs-saneado-v1.md`
- `docs/caddy-dns-saneado-v1.md`
- `docs/mantenimiento-basico-saneado-v1.md`
- `docs/saneamiento-documental-cierre-v0.1.md`
- `docs/pre-push-auditoria-cierre-v0.1.md`

## Reglas de uso

- No usar `git add .`.
- No copiar contenido sensible desde el archivo local a documentación
  versionada.
- Consultar el archivo local solo si hace falta contexto histórico.
- Si se reutiliza información, crear una versión saneada nueva.
- No incluir secretos, datos privados, configuración real ni contenido de
  backups en documentación versionable.

## Estado

El archivo local sensible queda fuera del flujo normal de `docs/`.

La documentación versionable debe seguir usando equivalentes saneados.
