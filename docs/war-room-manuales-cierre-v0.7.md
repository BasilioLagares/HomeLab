# Cierre War Room v0.7 — Manuales read-only

Fecha aproximada: 19 de mayo de 2026.

## Resumen

Se cierra la fase de Git seguro y War Room Manuales con una base documental
saneada y consultable desde la War Room en modo solo lectura.

Logros principales:

- Repositorio Git inicializado en rama `main`.
- `.gitignore` seguro para excluir secretos, backups, certificados, WireGuard,
  runtime, datos persistentes, dumps, tarballs, logs y configuraciones sensibles.
- Código público de War Room versionado de forma controlada.
- README raíz seguro.
- Documentación saneada en `docs/manuals/`.
- Panel Manuales integrado en la War Room.
- Menú lateral Manuales funcional.
- Lector online de manuales dentro de la War Room.
- Endpoint `/api/v1/manuals.php` en modo read-only.
- Compose de ejemplo actualizado con mount saneado para manuales.

## URL canónica

URL canónica actual de War Room:

- `https://warroom.homelab.home.arpa`

La ruta `.local` antigua no es la ruta canónica actual y no debe usarse como
referencia principal en documentación nueva.

## Decisiones de seguridad

- Mantener War Room como panel de observabilidad y lectura.
- No convertir Manuales en un punto de descarga directa de ficheros.
- Servir manuales mediante API read-only y lista blanca de slugs.
- Montar solo documentación saneada, no `docs/` completo.
- Mantener el compose real local fuera del versionado tal cual.
- Versionar únicamente ejemplos saneados cuando haga falta documentar mounts.
- No exponer rutas absolutas del host ni del contenedor en respuestas públicas.
- No introducir acciones operativas en la UI de Manuales.

## Endpoint implicado

- `/api/v1/manuals.php`

El endpoint expone el catálogo de manuales permitidos y, cuando se solicita un
slug permitido, devuelve el contenido Markdown de forma controlada. Los slugs
admitidos son:

- `README`
- `war-room`
- `git-seguro`
- `backups`

## Archivos principales

Archivos y carpetas creados o modificados durante la fase:

- `docs/manuals/`
- `platform/war-room/public/api/v1/manuals.php`
- `platform/war-room/public/index.php`
- `platform/war-room/public/assets/app.js`
- `platform/war-room/public/assets/style.css`
- `platform/war-room/docker-compose.example.yml`

El fichero `platform/war-room/docker-compose.yml` real se modificó localmente
para montar `docs/manuals/` en modo solo lectura, pero no debe versionarse tal
cual porque puede contener detalles propios del despliegue local.

## Validaciones realizadas

- Consulta al endpoint de manuales.
- Confirmación de `manuals_available: true`.
- Lectura por slug de `war-room`.
- Lectura por slug de `git-seguro`.
- Validación visual en la UI de War Room.
- Comprobación de que el lector funciona sin descargas directas de ficheros.

## Riesgos evitados

- Sin shell.
- Sin `docker.sock`.
- Sin comandos libres.
- Sin path traversal.
- Sin rutas absolutas expuestas.
- Sin descarga directa de ficheros.
- Sin exposición de secretos.
- Sin botones de reinicio, despliegue o ejecución operativa.

## Estado final

War Room v0.7 queda cerrada como visor read-only con sección Manuales funcional.
La documentación saneada ya puede consultarse desde la interfaz usando la URL
canónica actual.

La base queda preparada para futuras fases de Operaciones y Deploy/Orchestrator,
manteniendo separación entre lectura, automatización y acciones con impacto
operativo.

## Próximos pasos recomendados

- Revisar y sanear documentos antiguos antes de versionarlos.
- Decidir si el compose real de War Room debe mantenerse solo local o derivar en
  una plantilla adicional.
- Crear ejemplos saneados para otros servicios si procede.
- Revisar scripts de backup y actualización antes de versionarlos.
- Decidir si el checklist de estado se versiona tal cual o como `.example`.
- Diseñar War Room v0.8 como Operaciones read-only.
- Diseñar War Room v0.9 como Operaciones controladas con permisos y auditoría.
- Definir el alcance de Deploy / HomeLab Orchestrator.
- Evaluar IA interna como copiloto documental sin acceso a secretos.
- Completar revisión final de acceso remoto robusto.
- Consolidar backups automáticos y restauración probada.
- Definir seguridad global: autenticación, permisos, auditoría y gestión de
  secretos.
- Planificar exposición pública controlada solo cuando toque portfolio o demos.
