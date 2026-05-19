# Plan restauración backups v0.1

Fecha aproximada: 19 de mayo de 2026.

## Objetivo

Validar que los backups recientes son recuperables sin afectar a servicios
vivos.

Este plan no restaura encima de servicios reales, no toca la MariaDB real y no
toca el volumen real de Uptime Kuma. El orden correcto es primero inspección
segura, después restauración temporal controlada.

## Estado de partida

Backup MariaDB más reciente detectado:

- `backups/mariadb/homelab_demo_2026-05-19_19-43-14.sql`

Backup Uptime Kuma más reciente detectado:

- `backups/uptime-kuma/uptime-kuma-data_2026-05-19_20-00-33.tar.gz`

Los backups reales están fuera de Git. El directorio `scripts/.secrets/` también
está fuera de Git y no debe documentar valores reales.

## Principios de seguridad

- No restaurar sobre datos reales.
- No usar volúmenes reales para pruebas.
- No pegar dumps ni contenidos sensibles en documentación.
- No usar credenciales reales para contenedores temporales si no hace falta.
- Usar directorios temporales controlados.
- Limpiar temporales al terminar.
- Parar si hay duda sobre origen, destino o alcance del comando.

## Fase 1 - Inspección segura MariaDB

Seleccionar el último dump SQL:

```bash
LATEST_SQL="$(ls -t backups/mariadb/*.sql | head -n 1)"
```

Verificar existencia, tamaño y permisos:

```bash
ls -lh "$LATEST_SQL"
stat -c '%a %U %G %s %n' "$LATEST_SQL"
```

Mostrar solo una cabecera limitada y saneada, sin volcar datos completos:

```bash
sed -n '1,20p' "$LATEST_SQL" | sed -E 's/`[^`]+`/`<identificador>`/g; s/[0-9]+/<numero>/g'
```

## Fase 2 - Restauración temporal MariaDB

La prueba de restauración debe hacerse en un contenedor temporal y en una base
temporal, nunca sobre la base real.

Plan conceptual:

- Crear un contenedor MariaDB temporal.
- Usar una contraseña temporal local solo para la prueba.
- Crear una base temporal llamada `restore_test_db`.
- Importar el SQL al contenedor temporal.
- Validar con `SHOW TABLES`.
- No montar volúmenes reales.
- Borrar el contenedor temporal al terminar.

Comandos documentados para una fase posterior autorizada:

```bash
docker run --name mariadb-restore-test --rm -d \
  -e MARIADB_ROOT_PASSWORD='<password-temporal-local>' \
  mariadb:latest

docker exec mariadb-restore-test mariadb -uroot -p'<password-temporal-local>' \
  -e 'CREATE DATABASE restore_test_db;'

docker exec -i mariadb-restore-test mariadb -uroot -p'<password-temporal-local>' \
  restore_test_db < "$LATEST_SQL"

docker exec mariadb-restore-test mariadb -uroot -p'<password-temporal-local>' \
  -e 'SHOW TABLES FROM restore_test_db;'
```

## Fase 3 - Inspección segura Uptime Kuma

Seleccionar el último tarball:

```bash
LATEST_KUMA="$(ls -t backups/uptime-kuma/*.tar.gz | head -n 1)"
```

Verificar existencia, tamaño y permisos:

```bash
ls -lh "$LATEST_KUMA"
stat -c '%a %U %G %s %n' "$LATEST_KUMA"
```

Listar contenido de forma limitada, sin extraer todavía:

```bash
tar -tzf "$LATEST_KUMA" | sed -n '1,80p'
```

## Fase 4 - Extracción temporal Uptime Kuma

La extracción debe hacerse en un directorio temporal controlado, nunca sobre el
volumen real de Uptime Kuma.

Destinos válidos para prueba:

- `runtime/restore-test/uptime-kuma`
- `/tmp/homelab-restore-test/uptime-kuma`

Plan conceptual:

- Crear el directorio temporal con permisos restrictivos.
- Extraer el tarball en ese directorio.
- Listar estructura con profundidad limitada.
- Revisar permisos.
- Limpiar temporales al terminar.

Comandos documentados para una fase posterior autorizada:

```bash
RESTORE_DIR="runtime/restore-test/uptime-kuma"
mkdir -p "$RESTORE_DIR"
chmod 700 "$RESTORE_DIR"
tar -xzf "$LATEST_KUMA" -C "$RESTORE_DIR"
find "$RESTORE_DIR" -maxdepth 3 -type f | sed -n '1,120p'
```

## Criterios de éxito

- El SQL se importa en una base temporal.
- Existen tablas en `restore_test_db`.
- El tarball de Uptime Kuma lista y extrae sin error.
- La estructura de datos de Uptime Kuma es reconocible.
- No se modifica ningún servicio real.
- No aparece nada nuevo en Git salvo documentación.

## Criterios de parada

- El backup está vacío o corrupto.
- Los permisos son incorrectos.
- Un comando apunta a una ruta real por error.
- Hay duda sobre el destino.
- Aparece riesgo de exponer datos sensibles.

## Qué NO hacer

- No restaurar sobre `homelab_demo` real.
- No extraer sobre el volumen real de Uptime Kuma.
- No ejecutar `docker compose down`.
- No pegar contenido de dumps.
- No versionar backups ni dumps.
- No usar credenciales reales en documentación.

## Próximos pasos

- Ejecutar inspección segura de los backups más recientes.
- Si la inspección es correcta, hacer restauración temporal MariaDB.
- Después hacer extracción temporal Uptime Kuma.
- Documentar resultados en un cierre v0.1.
