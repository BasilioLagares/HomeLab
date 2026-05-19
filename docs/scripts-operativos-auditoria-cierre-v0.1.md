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

- Saneado.
- Probado en `DRY_RUN`.
- Probado con ejecución real controlada.
- Versionado.
- Mantiene `DRY_RUN=1` por defecto.
- Exige `CONFIRM_STOP` para parar temporalmente el servicio.
- Incluye `trap` de recuperación para intentar arrancar el servicio si falla
  después de haberlo parado.
- Usa ejecución con usuario del host en el contenedor temporal para evitar
  backups creados como root.
- Valida tarball final y aplica permisos restrictivos.
- Se corrigió el problema inicial de fichero temporal creado como root.
- Uptime Kuma volvió a arrancar tras la prueba.

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
- `bash -n` de `backup-uptime-kuma.sh`.
- Prueba de conexión MariaDB mediante `docker exec -e MYSQL_PWD`.
- Ejecución real controlada del backup MariaDB.
- Prueba `DRY_RUN` de backup Uptime Kuma.
- Ejecución real controlada del backup Uptime Kuma.
- Confirmación de que Uptime Kuma volvió a arrancar.
- Confirmación de que el backup Uptime Kuma quedó con usuario correcto y
  permisos restrictivos.
- Confirmación de que backups y secretos locales no entran en Git.

## Pendientes

- Sanear o convertir a example `update-stack.sh`.
- Valorar rotación de credencial MariaDB si se considera necesario.
- Probar restauración real de backup.
- Documentar procedimiento final de restauración.

## Estado final

El microbloque queda cerrado con plantillas saneadas versionables, backup de
MariaDB saneado sin contraseña hardcodeada y backup de Uptime Kuma saneado con
dry-run, confirmación y recuperación básica. El script de actualización de stack
permanece pendiente.
