# Checklist previo a publicar HomeLab v0.1

Fecha de auditoría inicial: **12 de julio de 2026**.
Preparación del snapshot público: **14 de julio de 2026**.

Un elemento marcado indica que se comprobó en el árbol actual. No sustituye la
revisión final del snapshot exacto que se vaya a publicar.

## Bloqueos actuales

- [x] Mantener `PUBLIC_V0.1_MANIFEST.txt` como allowlist exacta de la rama
  pública canónica; cada tag conserva su copia histórica.
- [x] Validar el contenido preparado desde un directorio limpio, no desde el
  árbol operativo que contiene secretos y backups ignorados.
- [x] Decidir si los nombres de aplicaciones/personas y subdominios internos se
  eliminan, parametrizan o se consideran deliberadamente públicos.
- [x] Sanear las definiciones de servicios codificadas en War Room.
- [x] Mantener el historial real del proyecto en el repositorio canónico y
  publicar mediante pushes normales, sin reescritura ni force push.
- [x] Integrar el commit público previo con el historial local conservando el
  árbol actualizado y ambas líneas históricas.
- [x] Ejecutar `gitleaks` sobre cambios preparados, snapshot limpio e historial
  completo; no se detectaron filtraciones el 14 de julio de 2026.
- [x] Integrar PHP cURL en la imagen de War Room y validarlo dentro del
  contenedor.
- [x] Añadir la licencia MIT.

Mientras quede abierto cualquiera de estos puntos, el repositorio no debe
considerarse listo para publicación.

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
- [x] Comprobar formato, metadatos y contenido visual de las imágenes del
  snapshot final. Se excluyó un PNG no utilizado que no podía decodificarse.

Material sensible localizado y no publicable:

- claves privadas, clave precompartida y configuración WireGuard;
- secreto de backup MariaDB;
- certificados/CA locales;
- `.env` de WireGuard y del stack MVP;
- Caddyfile, dnsmasq y Compose reales;
- IP LAN, rutas personales y nombres internos;
- dumps MariaDB, backups Uptime Kuma y paquetes de configuración;
- logs, runtime JSON, configuración de aplicaciones y uploads.

## Contenido Git

- [x] Rama del repositorio canónico identificada: `main`.
- [x] Rama por defecto del repositorio público: `main`.
- [x] Remoto público confirmado: `origin` apunta a
  `https://github.com/BasilioLagares/HomeLab.git`.
- [x] La identidad Git usa una dirección `users.noreply.github.com`.
- [x] Los archivos rastreados se han inventariado.
- [x] Los archivos ignorados críticos se han inventariado.
- [x] Revisar `git status --short --ignored` inmediatamente antes de exportar.
- [x] Revisar `git diff --check` y `git diff --cached`.
- [x] Revisar `git ls-files` y confirmar cada fichero binario.
- [ ] Crear o mover tags únicamente sobre commits revisados del historial
  canónico.
- [x] Publicar únicamente desde el repositorio canónico mediante push normal.

No usar `git add .` en el árbol operativo.

## Configuración y reproducibilidad

- [x] `platform/war-room/docker-compose.example.yml` usa rutas relativas y
  variables con valores por defecto.
- [x] El Compose de ejemplo pasa `docker compose config --quiet`.
- [x] Todos los PHP pasan `php -l`.
- [x] Todos los scripts Bash pasan `bash -n`.
- [x] `state/homelab_tasks.example.json` es JSON válido.
- [x] Añadir un `.env.example` específico de War Room.
- [x] Documentar cómo crear la red Docker externa.
- [x] Evitar `container_name` en el Compose público para permitir varias
  instancias.
- [x] Evitar etiquetas `latest` en los artefactos ejecutables públicos; la
  imagen base de War Room está fijada por digest.
- [x] Publicar `state/homelab_tasks.example.json`; mantener
  `state/homelab_tasks.json` como estado operativo privado.
- [ ] Crear Compose saneados para proxy, DNS, VPN y stack MVP si se presentan
  como parte reproducible.
- [ ] Probar desde clonación nueva, sin depender de `/home/<usuario>/HomeLab`.

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

- [x] README diferencia implementación, limitaciones, trabajo activo e ideas
  futuras.
- [x] Arquitectura separa contenido versionado y configuración local.
- [x] Roadmap separa estado actual, tareas activas, ideas futuras y trabajo
  completado.
- [x] La imagen conceptual está identificada como no real.
- [ ] Añadir capturas del estado real solo después de sanear nombres, URLs,
  horas, topología y contenido de los paneles.
- [x] Confirmar que todos los enlaces relativos del snapshot resuelven
  localmente. La renderización final en GitHub se revisa tras el commit.
- [x] Actualizar la fecha y el estado real del snapshot preparado.

## Auditoría final sugerida

Ejecutar en el repositorio canónico antes de cada push:

```bash
git status --short
git ls-files
git diff --cached --check
comm -3 <(git ls-files | sort) <(awk '!/^($|#)/ {print}' PUBLIC_V0.1_MANIFEST.txt | sort)
git grep -nEI 'BEGIN .*PRIVATE KEY|password|secret|token|api[_-]?key|credential'
git grep -nEI '/home/[^/ ]+|([0-9]{1,3}\.){3}[0-9]{1,3}'
git ls-files | grep -E '(^|/)(\.env|.*\.(key|pem|p12|sql|tar\.gz))$'
gitleaks git --redact
docker compose -f platform/war-room/docker-compose.example.yml config --quiet
find platform/war-room/public -type f -name '*.php' -print0 | xargs -0 -n1 php -l
find scripts tools -type f -name '*.sh' -print0 | xargs -0 -n1 bash -n
jq empty state/homelab_tasks.example.json platform/war-room/examples/docker-status.example.json
```

Después del commit definitivo, repetir `gitleaks git --redact` antes de crear o
mover un tag y antes del push.

Revisar manualmente todos los resultados: las búsquedas producen falsos
positivos en documentación defensiva, y una salida vacía no demuestra por sí
sola que no existan secretos.
