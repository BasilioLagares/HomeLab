# War Room HomeLab

War Room es el panel local de observabilidad del HomeLab. Su objetivo es mostrar
estado, salud básica y tareas de operación de forma clara, sin convertirse en un
panel de control con acciones destructivas o cambios sobre la infraestructura.

La aplicación está diseñada como una interfaz de solo lectura. No debe montar el
socket de Docker, no debe ejecutar comandos del host y no debe exponer secretos.

Este repositorio no publica una instancia online de War Room. El Compose de
ejemplo enlaza el servicio a `127.0.0.1` por defecto y usa nombres, URLs y datos
saneados. La topología, las credenciales y la configuración del despliegue real
permanecen fuera de Git.

## Estado actual

Estado funcional: panel web local con API v1 de solo lectura.

Capacidades actuales:

- Frontend servido desde `public/`.
- API PHP en `public/api/v1/`.
- Lectura básica de estado de servicios definidos por la aplicación.
- Lectura de métricas visibles desde el contenedor.
- Lectura opcional de estado de contenedores desde un fichero runtime generado
  fuera de la aplicación.
- Lectura opcional de la proyección operativa de `ROADMAP.md` desde un fichero
  de estado montado en solo lectura.

Limitaciones actuales:

- No hay acciones operativas desde la interfaz.
- No hay orquestación ni despliegues desde War Room.
- No se debe considerar fuente única de verdad para seguridad, backups o alertas.
- Los datos de runtime pueden no existir o estar desactualizados; la API debe
  tratar esos casos como estado no disponible o stale.

## Arquitectura básica

Componentes:

- `public/index.php`: entrada principal del panel.
- `public/assets/`: JavaScript, CSS e imágenes del frontend.
- `public/api/v1/`: endpoints JSON de solo lectura.
- `state` montado en solo lectura: proyección operativa del roadmap.
- `runtime` montado en solo lectura: datos generados por exportadores externos.

Principios:

- El contenedor sirve la aplicación web y la API.
- Los datos externos se consumen como ficheros de solo lectura.
- El exportador de estado de contenedores vive fuera de War Room.
- La API filtra la información que devuelve al frontend.
- Las operaciones de administración quedan fuera de la aplicación.

## API v1

Endpoints disponibles:

- `GET /api/v1/health.php`
- `GET /api/v1/status.php`
- `GET /api/v1/services.php`
- `GET /api/v1/resources.php`
- `GET /api/v1/containers.php`
- `GET /api/v1/tasks.php`
- `GET /api/v1/manuals.php`
- `GET /api/v1/operations.php`

Propiedades esperadas:

- Respuestas JSON.
- Cabeceras sin caché para datos dinámicos.
- Rate limiting básico por endpoint.
- Solo lectura.
- Sin comandos shell desde PHP.
- Sin acceso directo al socket de Docker.

## Configuración de ejemplo

El fichero `docker-compose.example.yml` es una plantilla saneada para despliegue.
No sustituye automáticamente a la configuración real del host.

La imagen se construye desde `Dockerfile`, instala PHP cURL explícitamente y
verifica durante el build que la extensión puede cargarse.

Mounts esperados:

- `./public:/var/www/html:ro`: código público de War Room.
- `../../state:/var/warroom-state:ro`: proyección operativa del roadmap,
  opcional.
- `../../runtime/war-room:/var/warroom-runtime:ro`: datos runtime generados por
  exportadores externos, opcional.

Si se usan rutas distintas en producción, deben configurarse fuera de Git con
variables de entorno locales o con un compose real no versionado.

Los nombres, URLs, sondas y zona horaria de la aplicación también se leen desde
variables `WARROOM_*`. El fichero raíz `.env.example` contiene valores públicos
no enrutables. Los valores operativos deben permanecer en un `.env` local
ignorado e inyectarse desde el Compose privado.

Para probar el ejemplo desde una clonación limpia:

```bash
cp .env.example .env
cp state/homelab_tasks.example.json state/homelab_tasks.json
docker network create homelab_proxy
docker compose -f platform/war-room/docker-compose.example.yml up --build
```

Los dos ficheros copiados quedan ignorados por Git. La red externa se crea una
sola vez. El puerto se publica únicamente en `127.0.0.1` por defecto; cualquier
exposición a la LAN o a un proxy requiere una decisión explícita del operador.

## Política de seguridad

War Room debe mantenerse como superficie de lectura:

- No montar `/var/run/docker.sock`.
- No ejecutar `docker`, `systemctl`, scripts de backup ni comandos del host desde
  la API.
- No escribir sobre volúmenes de estado o runtime desde el contenedor web.
- No exponer servicios sensibles sin autenticación fuera de War Room.
- No guardar secretos, tokens ni certificados dentro de `public/`.
- No publicar datos de runtime que revelen credenciales, claves o detalles
  privados de infraestructura.

## Qué no debe versionarse

No deben añadirse a Git:

- `.env` y variantes locales.
- Certificados reales, CA locales, claves privadas o ficheros PEM/P12/PFX.
- Configuración real de WireGuard o material de claves.
- Backups, dumps SQL, bases de datos y archivos comprimidos.
- Logs, cachés, ficheros temporales y datos runtime.
- Composes reales con rutas absolutas del host o datos sensibles.
- Configuraciones de proxy con credenciales, hashes de `basic_auth` o detalles
  privados de despliegue.

Usar ficheros `.example` para documentar configuración versionable.

## Planificación

El roadmap canónico del proyecto está en [`ROADMAP.md`](../../ROADMAP.md). El
JSON consumido por `tasks.php` es únicamente su proyección de tareas activas e
ideas futuras; este README y War Room no mantienen listas independientes.

War Room permanece como consumidor read-only. El diseño de la consola de
operaciones ya está documentado como componente separado; su posible
implementación futura no convierte esta API en un ejecutor de shell, Docker o
tareas administrativas.
