# Scripts operativos de ejemplo

Esta carpeta contiene plantillas saneadas para documentar procedimientos
operativos del HomeLab. No son los scripts reales de producción.

## Política

- Los scripts reales quedan fuera de Git si contienen configuración local,
  credenciales o rutas privadas.
- `.env` real no se versiona.
- Los backups reales nunca se versionan.
- Cualquier script con impacto debe revisarse antes de usarse.
- Probar siempre con `DRY_RUN=1` antes de ejecutar cambios reales.

## Plantillas incluidas

- `backup-mariadb.example.sh`: ejemplo de dump de MariaDB sin password en claro.
- `backup-uptime-kuma.example.sh`: ejemplo de backup de datos persistentes con
  parada opcional controlada.
- `update-stack.example.sh`: ejemplo de actualización de stack con confirmación
  explícita y sin `docker compose down`.
- `.env.example`: nombres de variables y placeholders seguros.

## Uso recomendado

1. Copiar la plantilla necesaria a una ubicación local no versionada.
2. Copiar `.env.example` a `.env` local no versionado.
3. Rellenar valores reales solo en el entorno local.
4. Ejecutar primero con `DRY_RUN=1`.
5. Revisar salida, destino y alcance.
6. Ejecutar con `DRY_RUN=0` solo si el procedimiento está claro.

## Validaciones seguras

Estas comprobaciones no ejecutan la lógica operativa:

```bash
bash -n scripts/examples/backup-mariadb.example.sh
bash -n scripts/examples/backup-uptime-kuma.example.sh
bash -n scripts/examples/update-stack.example.sh
```

También se recomienda revisar con `shellcheck` si está disponible.

## Advertencias

- No copiar secretos a estas plantillas.
- No pegar salidas de logs con datos sensibles.
- No usar `rm -rf` para rotación de backups.
- No ejecutar scripts de actualización sin backups validados.
- No detener servicios sin entender el impacto.
