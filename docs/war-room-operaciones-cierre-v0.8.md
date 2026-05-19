# Cierre War Room v0.8 — Operaciones read-only

Fecha aproximada: 19 de mayo de 2026.

## Resumen

Se cierra War Room v0.8 con una nueva sección de diagnóstico operativo en modo
solo lectura. Esta fase añade visibilidad sobre el estado de Git, documentación,
seguridad y próximos pasos sin introducir acciones administrativas ni ejecución
de comandos.

La sección no duplica el panel de Servicios críticos. Servicios críticos sigue
centrado en healthchecks; Operaciones read-only muestra estado de fase,
decisiones de seguridad, situación documental y recomendaciones operativas.

## Endpoint creado

- `/api/v1/operations.php`

El endpoint devuelve:

- `operations_mode: read_only`
- `status: available`
- secciones de diagnóstico saneadas
- flags de seguridad que confirman ausencia de shell, docker socket, acciones
  administrativas y comandos libres

## URL canónica

- `https://warroom.homelab.home.arpa`

## Secciones de diagnóstico

Repositorio/versionado:

- Git seguro inicial cerrado.
- `.gitignore` seguro aplicado.
- Compose real tratado como local/no versionado.
- Compose de ejemplo actualizado.

Documentación/manuales:

- `docs/manuals` montado en modo read-only.
- Lector online disponible.
- Docs antiguos sensibles pendientes de saneamiento.
- Checklist HomeLab actualizada.

Seguridad:

- War Room en modo read-only.
- Sin comandos shell desde API.
- Sin `docker.sock`.
- Sin operaciones administrativas.
- Secretos fuera de Git.

Pendientes recomendados:

- Revisar scripts backup/update.
- Sanear docs antiguos.
- Probar restauración de backups.
- Diseñar v0.9 operaciones controladas con lista blanca.
- Mantener separación entre zona privada y demos públicas.

## Decisiones de seguridad

- Sin shell.
- Sin `docker.sock`.
- Sin comandos libres.
- Sin cambios administrativos.
- Sin formularios de ejecución.
- Sin tocar Docker, Caddy ni WireGuard.
- Sin rutas arbitrarias.
- Sin secretos expuestos.
- Sin reinicios ni operaciones sobre servicios.

## Validaciones realizadas

- `php -l platform/war-room/public/api/v1/operations.php`
- `php -l platform/war-room/public/index.php`
- `node --check platform/war-room/public/assets/app.js`
- Consulta HTTPS al endpoint `/api/v1/operations.php`
- Validación visual en la UI de War Room

## Diferencia frente a Servicios críticos

Servicios críticos monitoriza healthchecks de servicios definidos por War Room.
Su función es indicar disponibilidad básica y respuesta HTTP.

Operaciones read-only no monitoriza servicios duplicados. Su función es mostrar
diagnóstico de fase, seguridad, Git, documentación y próximos pasos operativos
sin permitir ejecución ni cambios.

## Estado final

War Room v0.8 queda funcional y read-only. La UI muestra Estado Operativo con
diagnóstico seguro y el endpoint `/api/v1/operations.php` responde correctamente
sin exponer secretos ni habilitar acciones.

## Próximos pasos recomendados

- v0.7.1 ampliar manuales operativos saneados.
- Revisar scripts backup/update antes de versionarlos.
- Sanear docs antiguos sensibles.
- Probar restauración de backups.
- Diseñar v0.9 Operaciones controladas con lista blanca.
- Mantener separación entre zona privada y demos públicas.
