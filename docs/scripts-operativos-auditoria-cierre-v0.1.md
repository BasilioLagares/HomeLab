# Cierre auditoría scripts operativos v0.1

Fecha aproximada: 19 de mayo de 2026.

## Resumen

Se cierra el microbloque de auditoría y saneamiento inicial de scripts
operativos. La revisión se centró en procedimientos de backup y actualización
sin incorporar secretos ni datos sensibles al repositorio.

Scripts revisados:

- `scripts/backup-mariadb.sh`
- `scripts/backup-uptime-kuma.sh`
- `scripts/update-stack.sh`

## Resultado por script

`backup-mariadb.sh`:

- Saneado.
- Probado de forma controlada.
- Versionado.
- Contraseña hardcodeada eliminada.
- Secreto local leído desde fichero no versionado.
- Backups generados fuera de Git.

`backup-uptime-kuma.sh`:

- Auditado.
- Pendiente de saneamiento o conversión definitiva a plantilla.
- Toca servicios, por lo que requiere revisión adicional antes de versionarse.

`update-stack.sh`:

- Auditado.
- Pendiente de saneamiento o conversión definitiva a plantilla.
- Ejecuta actualización real de stack, por lo que debe tratarse como operación
  con impacto.

## Plantillas saneadas creadas

Se crearon plantillas en `scripts/examples/`:

- `backup-mariadb.example.sh`
- `backup-uptime-kuma.example.sh`
- `update-stack.example.sh`
- `.env.example`
- `README.md`

Estas plantillas usan placeholders, `DRY_RUN` por defecto y comentarios de
seguridad. No contienen valores reales ni secretos.

## Decisiones de seguridad

- No versionar secretos.
- No versionar backups.
- No versionar dumps.
- No dejar contraseñas hardcodeadas.
- Usar `scripts/.secrets/` para secretos locales no versionados.
- Mantener `DRY_RUN` por defecto en ejemplos.
- No ejecutar operaciones de impacto sin validación previa.
- Mantener los scripts reales con impacto fuera de Git hasta sanearlos.

## Validaciones realizadas

- `bash -n` de los scripts de ejemplo.
- `bash -n` de `backup-mariadb.sh`.
- Prueba de conexión MariaDB mediante `docker exec -e MYSQL_PWD`.
- Ejecución real controlada del backup MariaDB.
- Confirmación de que backups y secretos locales no entran en Git.

## Pendientes

- Sanear o convertir a example `backup-uptime-kuma.sh`.
- Sanear o convertir a example `update-stack.sh`.
- Valorar rotación de credencial MariaDB si se considera necesario.
- Probar restauración real de backup.
- Documentar procedimiento final de restauración.

## Estado final

El microbloque queda cerrado con plantillas saneadas versionables y el backup de
MariaDB saneado sin contraseña hardcodeada. Los scripts con impacto operativo
pendientes permanecen fuera de Git hasta una decisión posterior.
