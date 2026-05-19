# Cierre restauración backups v0.1

Fecha aproximada: 19 de mayo de 2026.

## Objetivo

Validar que los backups recientes son recuperables mediante pruebas
controladas, sin afectar a servicios vivos y sin restaurar encima de datos
reales.

## Alcance

- MariaDB.
- Uptime Kuma.
- Sin tocar servicios reales.
- Sin restaurar sobre volúmenes reales.
- Sin versionar backups, dumps ni secretos.

## Resumen de pruebas realizadas

- Inspección segura del backup MariaDB más reciente.
- Inspección segura del backup Uptime Kuma más reciente.
- Restauración temporal MariaDB en contenedor aislado.
- Extracción temporal Uptime Kuma en `runtime/restore-test/uptime-kuma`.
- Limpieza del temporal de Uptime Kuma.

## Resultado MariaDB

Backup usado:

- `backups/mariadb/homelab_demo_2026-05-19_19-43-14.sql`

Resultado:

- Permisos correctos: `600`, usuario/grupo local.
- Dump SQL legible y no vacío.
- Importación realizada en un contenedor MariaDB temporal.
- Base temporal usada: `restore_test_db`.
- Importación correcta.
- Tabla detectada: `prueba_homelab`.
- Recuento de tablas: `1`.
- El contenedor temporal MariaDB quedó eliminado al terminar o ya no estaba
  presente.

## Resultado Uptime Kuma

Backup usado:

- `backups/uptime-kuma/uptime-kuma-data_2026-05-19_20-00-33.tar.gz`

Resultado:

- Permisos correctos: `600`, usuario/grupo local.
- Tarball legible y no vacío.
- Extracción realizada solo en `runtime/restore-test/uptime-kuma`.
- Estructura esperada detectada:
  - `docker-tls/`
  - `screenshots/`
  - `upload/`
  - `kuma.db`
- `kuma.db` detectado con tamaño razonable.
- Temporal limpiado después de la prueba.

## Validaciones de seguridad

- No se tocaron servicios vivos.
- No se usaron volúmenes reales.
- No se versionaron backups.
- No se versionaron dumps.
- No se publicaron secretos.
- `runtime/restore-test` no entró en Git.

## Incidencias

- Al intentar parar/eliminar el contenedor temporal MariaDB, este ya no estaba
  presente. No tuvo impacto porque se trataba de un contenedor temporal y no
  persistente.

## Estado final

La restauración/validación v0.1 queda correcta. Los backups probados son
legibles, tienen permisos restrictivos y permitieron validar recuperación en
entornos temporales sin afectar a servicios reales.

## Pendientes

- Probar restauración completa de servicio Uptime Kuma solo si algún día hace
  falta.
- Automatizar scripts de restauración de prueba si compensa.
- Mantener rotación y retención de backups.
- Valorar rotación de credencial MariaDB si procede.
- Revisar docs antiguos sensibles.
