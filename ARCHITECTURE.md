# Arquitectura de HomeLab

## 1. Alcance de este documento

Este documento describe lo observable en el código, la configuración local y
los artefactos existentes a 12 de julio de 2026. Distingue entre arquitectura
operativa local y contenido reproducible desde Git. No presupone que un servicio
esté activo por el hecho de tener un Compose o un contenedor detenido.

## 2. Vista del sistema

HomeLab es un conjunto de despliegues Docker Compose independientes conectados
por una red proxy externa. No existe un Compose raíz que orqueste el sistema
completo.

```mermaid
flowchart TB
    Client[Cliente LAN o VPN] --> DNS[dnsmasq\nDNS interno]
    Client --> Caddy[Caddy\nHTTP/HTTPS interno]
    Caddy --> WarRoom[War Room]
    Caddy --> Homepage[Homepage]
    Caddy --> Kuma[Uptime Kuma]
    Caddy --> Adminer[Adminer]
    Caddy --> PhpApps[Aplicaciones PHP]
    Adminer --> MariaDB[(MariaDB)]
    PhpApps --> MariaDB
    VPN[WireGuard] --> Client

    HostExporter[export-docker-status.sh] --> Runtime[docker-status.json]
    Runtime -->|solo lectura| WarRoom
    Tasks[state/homelab_tasks.json] -->|solo lectura| WarRoom
    Manuals[docs/manuals] -->|solo lectura| WarRoom
```

La configuración real de Caddy, dnsmasq, WireGuard, el stack MVP y las
aplicaciones está presente solo en el entorno local e ignorada por Git. Por
tanto, esta vista describe el laboratorio inspeccionado, pero una clonación del
repositorio actual solo reproduce War Room y las herramientas versionadas.

## 3. Componentes demostrados

### 3.1 War Room

Aplicación server-rendered y sin framework, servida por PHP/Apache:

- `public/index.php` genera la estructura inicial y estados de respaldo.
- `public/assets/app.js` consulta periódicamente los endpoints y actualiza la UI.
- `public/assets/style.css` implementa la presentación responsive.
- `public/api/v1/` expone JSON de solo lectura.

Endpoints implementados:

| Endpoint | Fuente | Comportamiento real |
| --- | --- | --- |
| `health.php` | Proceso PHP | Salud básica y hora del servidor |
| `status.php` | Sondas de servicios | Resumen agregado; depende de PHP cURL |
| `services.php` | Definiciones internas y HTTP | Estado y latencia; depende de PHP cURL |
| `resources.php` | `/proc` y filesystem del contenedor | CPU estimada, memoria y disco visibles desde el contenedor, no necesariamente del host |
| `containers.php` | `/var/warroom-runtime/docker-status.json` | Inventario filtrado y detección de datos stale a los 45 segundos |
| `tasks.php` | Mount de estado o copia del repositorio | Valida esquema, estados y prioridades antes de responder |
| `manuals.php` | `docs/manuals` montado | Allowlist fija y lectura Markdown sin traversal |
| `operations.php` | Datos estáticos revisados | Diagnóstico informativo; no ejecuta operaciones |

Todos aplican cabeceras sin caché. El rate limit guarda contadores por IP y
endpoint en el directorio temporal del contenedor; es una protección básica por
instancia, no un control distribuido ni autenticación.

### 3.2 Exportador Docker

`tools/war-room/export-docker-status.sh` se ejecuta en el host, consulta Docker y
genera el fichero runtime mediante escritura temporal y `mv`. War Room recibe
solo JSON, nunca el socket de Docker. El exportador requiere Bash, Docker y
`jq`; su planificación periódica no está versionada ni demostrada.

El JSON incluye identificador corto, nombre, imagen, estado, texto de estado y
puertos de todos los contenedores. Aunque no contiene secretos por diseño, puede
revelar topología y no debe publicarse sin filtrado adicional.

### 3.3 Scripts operativos

- `backup-mariadb.sh`: dump mediante `mariadb-dump`, secreto leído desde fichero
  local y salida con permisos restrictivos.
- `backup-uptime-kuma.sh`: parada confirmada, archivo temporal del volumen,
  validación y recuperación básica mediante `trap`; `DRY_RUN=1` por defecto.
- `update-stack.sh`: allowlist de stacks, confirmación de backup y doble
  confirmación antes de `pull` y `up -d`; solo se ha demostrado en dry-run.
- `status-homelab.sh`: diagnóstico directo del daemon y del espacio Docker.
- `scripts/examples/`: plantillas parametrizadas para publicación.

La documentación existente registra ejecuciones reales de los backups y una
restauración temporal de MariaDB, además de la extracción controlada de un
backup de Uptime Kuma. No demuestra una restauración completa del servicio
Uptime Kuma ni una actualización real con `update-stack.sh`.

### 3.4 Stacks locales no versionados

Se inspeccionaron los siguientes Compose válidos sintácticamente:

| Stack | Imágenes declaradas | Estado de publicación |
| --- | --- | --- |
| MVP | `mariadb:11`, `adminer:latest`, `louislam/uptime-kuma:1`, `php:8.3-apache`, `ghcr.io/gethomepage/homepage:latest` | Local, ignorado |
| Proxy | `caddy:2-alpine` | Local, ignorado |
| DNS | `jpillora/dnsmasq:latest` | Local, ignorado; contiene bind a IP privada |
| VPN | `lscr.io/linuxserver/wireguard:latest` | Local, ignorado; contiene claves y configuración real |
| War Room | `php:8.3-apache` | Ejemplo saneado rastreado; Compose real local no rastreado |

Las etiquetas `latest` reducen reproducibilidad. No hay hashes de imagen,
healthchecks uniformes, CI ni pruebas automáticas de integración.

## 4. Redes y resolución

- `homelab_proxy` es una red Docker externa compartida por proxy y servicios.
- MariaDB se publica solo en loopback en el Compose local inspeccionado.
- Los servicios web internos se conectan al proxy por nombre de contenedor.
- dnsmasq sirve nombres bajo el dominio reservado `.home.arpa` en una IP LAN.
- Caddy termina HTTPS interno con una CA local.
- WireGuard proporciona acceso remoto privado según la documentación y la
  configuración local, pero su robustez actual no ha sido revalidada.

Los nombres `.home.arpa` no son dominios públicos. Aun así, los subdominios,
nombres de contenedor, puertos e IP LAN forman parte de la topología privada y
deben parametrizarse en los ejemplos públicos.

## 5. Datos y límites de confianza

```text
Zona versionada y revisable
  War Room, scripts saneados, plantillas, manuales, checklist

Zona local de configuración
  Compose reales, Caddyfile, dnsmasq, .env, configuración WireGuard

Zona sensible
  secretos, claves, CA, dumps, backups, bases de datos, uploads

Zona generada
  logs, runtime JSON, temporales y estado de contenedores
```

Controles existentes:

- `.gitignore` de exclusión conservadora.
- Mounts read-only en el Compose de ejemplo de War Room.
- Ausencia deliberada de `docker.sock` en el contenedor web.
- Allowlist de manuales y filtrado de campos de tareas/contenedores.
- Secretos de scripts leídos desde ficheros ignorados.
- Backups creados con `umask 077` y comprobaciones de salida.

Limitaciones:

- No hay autenticación en War Room; la seguridad depende del perímetro local.
- El rate limit usa ficheros temporales y confía en `REMOTE_ADDR`.
- Las sondas desactivan la verificación TLS, aunque actualmente consultan URLs
  internas HTTP.
- La imagen de War Room compila y valida PHP cURL durante el build.
- Las sondas se ejecutan secuencialmente, sin caché. Con varios servicios caídos,
  `status.php` y `services.php` pueden acumular varios timeouts y responder con
  latencia elevada.
- Los datos iniciales de la UI contienen estados estáticos que pueden mostrarse
  antes de que responda la API y no son una fuente de verdad.
- No existe automatización versionada para ejecutar periódicamente el exportador.

## 6. Dependencias

Dependencias directas versionadas:

- PHP 8.3, Apache y APIs estándar de PHP.
- JavaScript y CSS nativos, sin gestor de paquetes frontend.
- Bash, Docker CLI, Docker Compose v2 y `jq` para herramientas.
- Red Docker externa creada fuera del Compose de War Room.

Dependencias locales observadas: Caddy, MariaDB, Adminer, Uptime Kuma, Homepage,
WireGuard y dnsmasq. No existe un fichero único que fije o compruebe todas sus
versiones.

## 7. Estructura pública propuesta

```text
.
├── platform/
│   └── war-room/
│       ├── compose.example.yml
│       ├── Dockerfile
│       ├── config/services.example.json
│       └── public/
├── stacks/
│   ├── core/compose.example.yml
│   ├── proxy/compose.example.yml
│   ├── dns/compose.example.yml
│   └── vpn/compose.example.yml
├── scripts/
│   ├── backup/
│   ├── maintenance/
│   └── examples/
├── docs/
│   ├── manuals/
│   ├── decisions/
│   └── evidence/
├── state/homelab_tasks.example.json
├── .env.example
├── README.md
├── ARCHITECTURE.md
├── ROADMAP.md
└── PRE_PUBLISH_CHECKLIST.md
```

Esta es una estructura objetivo, no el estado actual.

## 8. Reorganización sin ruptura

1. Crear ejemplos saneados de cada stack junto a los Compose reales, sin mover
   todavía los despliegues locales.
2. Mantener validada la imagen de War Room y sus dependencias PHP al actualizar
   la imagen base.
3. Mantener las definiciones públicas parametrizadas y los valores operativos
   exclusivamente en el `.env` local ignorado.
4. Completar la parametrización de red, puertos y rutas mediante `.env.example`.
5. La v0.1 pública se construyó exclusivamente desde el manifest en un
   repositorio limpio, sin reutilizar el historial ni el árbol operativo.
6. Tras validar equivalencia, migrar scripts a subcarpetas y conservar wrappers
   en sus rutas antiguas durante una versión.
7. Archivar los documentos de cierre en `docs/evidence/` solo después de revisar
   nombres de backups, fechas y topología; no moverlos en la primera publicación.

No se recomienda reorganizar físicamente el árbol actual antes de disponer de
pruebas de integración o, al menos, una validación manual reproducible.
