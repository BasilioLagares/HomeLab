# Checklist de publicación HomeLab v0.1

Fecha de auditoría final: **13 de julio de 2026**.

Un elemento marcado indica que se comprobó en el repositorio público local.

## Estado de release

- [x] Construir el repositorio desde un snapshot limpio, fuera del árbol
  operativo.
- [x] Decidir si los nombres de aplicaciones/personas y subdominios internos se
  eliminan, parametrizan o se consideran deliberadamente públicos.
- [x] Sanear las definiciones de servicios codificadas en War Room.
- [x] Crear y auditar un historial público limpio con un único commit raíz.
- [x] Ejecutar Gitleaks sobre el snapshot y todo el historial público.
- [x] Integrar PHP cURL en la imagen de War Room y validarlo dentro del
  contenedor.
- [x] Añadir la licencia MIT.

Resultado: repositorio apto para publicación, sin incidencias técnicas o de
seguridad conocidas. Solo queda crear el repositorio en GitHub, añadir el remoto
y hacer push.

Las casillas abiertas en las secciones siguientes son tareas operativas,
opcionales o posteriores a v0.1; no impiden esta publicación.

## Secretos y datos sensibles

- [x] `.env` y variantes están ignorados.
- [x] `scripts/.secrets/` está ignorado.
- [x] Certificados, claves y CA están ignorados.
- [x] WireGuard real y material de peers están ignorados.
- [x] Backups, dumps SQL, bases de datos y archivos comprimidos están ignorados.
- [x] Runtime, logs, uploads y datos persistentes están ignorados.
- [x] El conjunto actual de archivos rastreados no contiene bloques de clave
  privada ni valores de credenciales detectables mediante búsqueda textual.
- [x] El historial conocido no contiene claves privadas ni passwords en claro
  detectados mediante búsqueda textual básica.
- [ ] Rotar la credencial MariaDB local si ha podido copiarse fuera de su
  almacenamiento previsto.
- [ ] Confirmar que ningún paquete de recuperación se sincroniza con un destino
  público. El script local de disaster recovery archiva el árbol completo.
- [ ] Revisar los tarballs antiguos: incluyen certificados, Caddyfile, DNS,
  runtime y documentación de topología.
- [ ] Comprobar metadatos de todas las imágenes y binarios del snapshot final.

Material sensible excluido del repositorio público:

- claves privadas, clave precompartida y configuración WireGuard;
- secreto de backup MariaDB;
- certificados/CA locales;
- `.env` de WireGuard y del stack MVP;
- Caddyfile, dnsmasq y Compose reales;
- IP LAN, rutas personales y nombres internos;
- dumps MariaDB, backups Uptime Kuma y paquetes de configuración;
- logs, runtime JSON, configuración de aplicaciones y uploads.

## Contenido Git

- [x] Rama actual identificada: `main`.
- [x] No hay remoto configurado.
- [x] La identidad Git usa una dirección `users.noreply.github.com`.
- [x] Los archivos rastreados se han inventariado.
- [x] Los archivos ignorados críticos se han inventariado.
- [x] Revisar `git status` y confirmar que el working tree está limpio.
- [x] Revisar `git diff --check` y `git diff --cached --check`.
- [x] Revisar `git ls-files` y confirmar los 41 archivos del manifest.
- [ ] Crear el tag `v0.1.0` solo después de validar el snapshot público.
- [ ] Crear el repositorio en GitHub, configurar el remoto y hacer push.

No usar `git add .` en el árbol operativo.

## Configuración y reproducibilidad

- [x] `platform/war-room/docker-compose.example.yml` usa rutas relativas y
  variables con valores por defecto.
- [x] El Compose de ejemplo pasa `docker compose config --quiet`.
- [x] Todos los PHP pasan `php -l`.
- [x] Todos los scripts Bash pasan `bash -n`.
- [x] `state/homelab_tasks.example.json` es JSON válido.
- [x] Añadir un `.env.example` específico de War Room.
- [ ] Documentar cómo crear la red Docker externa.
- [ ] Evitar `container_name` o hacerlo configurable para permitir varias
  instancias.
- [ ] Sustituir etiquetas `latest` por versiones o digest en ejemplos públicos.
- [x] Decidir si `state/homelab_tasks.json` se publica como ejemplo, no como
  estado operativo.
- [ ] Crear Compose saneados para proxy, DNS, VPN y stack MVP si se presentan
  como parte reproducible.
- [x] Validar desde un repositorio nuevo e independiente, sin rutas personales.

## Seguridad de War Room

- [x] El Compose no monta `/var/run/docker.sock`.
- [x] Estado, runtime y manuales se montan read-only.
- [x] Las APIs no ejecutan shell ni acciones administrativas.
- [x] Manuales usan allowlist y comprobación de ruta real.
- [x] Tareas y contenedores filtran los campos expuestos.
- [ ] Añadir autenticación o documentar y aplicar una restricción de red
  verificable delante del panel.
- [ ] Revisar cabeceras HTTP, CSP y comportamiento detrás de proxy.
- [ ] No desactivar validación TLS si las sondas pasan a usar HTTPS.
- [ ] Filtrar nombres, imágenes y puertos en el exportador según una allowlist.
- [ ] Evitar que estados estáticos iniciales parezcan telemetría real.
- [ ] Limitar la latencia total de las sondas mediante concurrencia, caché o un
  presupuesto global de timeout.
- [ ] Verificar estados `unavailable`, `stale` y errores de JSON mediante tests.

## Documentación de portfolio

- [x] README diferencia implementación, limitaciones y Future Work.
- [x] Arquitectura separa contenido versionado y configuración local.
- [x] Roadmap distingue estado actual, Planned y Future Work.
- [x] La imagen conceptual está identificada como no real.
- [ ] Añadir capturas del estado real solo después de sanear nombres, URLs,
  horas, topología y contenido de los paneles.
- [x] Confirmar que todos los enlaces relativos tienen destinos presentes.
- [x] Actualizar la fecha y el estado real antes de publicar.

## Auditoría final ejecutada

Comandos reproducibles ejecutados sobre el repositorio público local:

```bash
git status --short --ignored
git ls-files
git diff --check
git grep -nEI 'BEGIN .*PRIVATE KEY|password|secret|token|api[_-]?key|credential'
git grep -nEI '/home/[^/ ]+|([0-9]{1,3}\.){3}[0-9]{1,3}'
find . -type f \( -name '.env' -o -name '*.key' -o -name '*.pem' -o -name '*.p12' -o -name '*.sql' -o -name '*.tar.gz' \) -print
gitleaks git --redact
docker compose -f platform/war-room/docker-compose.example.yml config --quiet
find platform/war-room/public -type f -name '*.php' -print0 | xargs -0 -n1 php -l
find scripts tools -type f -name '*.sh' -print0 | xargs -0 -n1 bash -n
jq empty state/homelab_tasks.json
```

Revisar manualmente todos los resultados: las búsquedas producen falsos
positivos en documentación defensiva, y una salida vacía no demuestra por sí
sola que no existan secretos.
