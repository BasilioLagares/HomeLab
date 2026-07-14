# Roadmap de HomeLab

Este documento es la fuente canónica única de la planificación de HomeLab.
`README.md`, los manuales, War Room y los ficheros de estado pueden resumir o
representar esta información, pero no deben mantener roadmaps independientes.

## Reglas de sincronización

- Las decisiones de alcance y estado se realizan únicamente en este documento.
- `state/homelab_tasks.json` y `state/homelab_tasks.example.json` son copias
  equivalentes de la proyección operativa: solo contienen tareas activas e ideas
  futuras que pertenecen a HomeLab.
- War Room consume esa proyección en modo read-only; no define tareas, estados,
  prioridades ni contadores propios.
- El trabajo completado y los elementos de otros proyectos se documentan aquí,
  pero no se exportan al JSON operativo.

## Estado actual

- El trabajo de cierre del contenido público `v0.1` se considera cerrado a nivel
  de roadmap. El repositorio original y `origin/main` forman el historial
  canónico; las comprobaciones aplicables a commits, tags y pushes viven
  exclusivamente en `PRE_PUBLISH_CHECKLIST.md`.
- La allowlist pública está definida en `PUBLIC_V0.1_MANIFEST.txt` y separa el
  código publicable de la configuración, estado y operación privados.
- War Room funciona como panel PHP/JavaScript de solo lectura, sin acceso a
  `docker.sock` ni ejecución de comandos del host.
- El paquete público de War Room incluye Dockerfile, Compose de ejemplo,
  configuración de ejemplo, datos JSON saneados, manuales y exportador.
- Las validaciones realizadas cubren sintaxis, Compose, construcción de imagen,
  endpoints con datos de ejemplo, enlaces y detección de secretos.

## Tareas activas

No hay tareas activas. No está autorizada la implementación de nuevas
funcionalidades en esta fase.

## Ideas futuras

### Consola de operaciones catalogadas (implementación aplazada)

- ID operativo: `command-terminal`.
- Estado operativo: `later`.
- Prioridad: `high`.
- La posible implementación del MVP diagnóstico queda aplazada. El diseño de
  seguridad ya está completado en `docs/manuals/terminal-ordenes.md`.
- Debe seguir siendo un componente CLI separado de War Room, sin shell
  arbitraria, `docker.sock` ni operaciones que modifiquen estado.

### Chatbot o IA interna de HomeLab

- ID operativo: `homelab-internal-chatbot`.
- Estado operativo: `later`.
- Prioridad: `medium`.
- Idea futura para consultar documentación o información previamente saneada.
- No debe tener acceso a secretos, runtime sensible ni ejecución de órdenes.

## Otros proyectos

### Asistente de voz tipo Jarvis para Linux

Idea externa para Linux general. No pertenece al alcance de HomeLab y, si se
desarrolla, debe gestionarse en un repositorio y backlog separados. No se
exporta a la proyección operativa de HomeLab.

## Trabajo completado resumido

- Base Docker local con proxy, DNS, VPN, servicios y procedimientos operativos.
- War Room read-only con UI, API, manuales, tareas, recursos, servicios y estado
  de contenedores mediante JSON externo.
- Exportador host sin montar el socket Docker en la aplicación web.
- Plantillas saneadas de backup y actualización; scripts operativos reales
  conservados fuera del snapshot público.
- Manuales de Git seguro, backups, restauración, Caddy/DNS, WireGuard y
  recuperación de War Room.
- Política Git conservadora para secretos, certificados, backups, runtime y
  configuración específica del host.
- Snapshot público `v0.1` delimitado mediante allowlist y preparado para
  revisión final.
- Documentación pública alineada con el contenido reproducible y sin roadmaps
  secundarios.
- War Room empaquetado con Dockerfile, Compose, configuración y datos de
  ejemplo saneados.
- Exportación temporal de la allowlist validada sin dependencias de contenido
  privado o ignorado.
- Historial local y commit público previo integrados sin reescritura ni force
  push; `origin/main` queda como referencia canónica.
- Git y seguridad del repositorio cerrados para `v0.1.0`: rama `main`, tag
  anotado, política pública y publicación normal sin reescritura.
- SEO y exposición pública cerrados para `v0.1.0`: portada orientada a portfolio,
  alcance público y privado diferenciados, imagen conceptual relegada y War Room
  limitado a ejecución local sin demo online.
- Diseño de seguridad de la consola de operaciones catalogadas completado:
  alcance diagnóstico, catálogo cerrado, autorización, auditoría, límites y
  separación estricta de War Room documentados sin implementar la consola.

La validación desde una clonación Git real y el resto de controles previos a
cada push se mantienen en `PRE_PUBLISH_CHECKLIST.md`; no se replican como tareas
activas en este roadmap.
