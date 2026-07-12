# Roadmap de HomeLab

Este roadmap usa evidencia del repositorio y del entorno inspeccionado. Las
ideas sin código o validación se marcan como **Planned** o **Future Work**.

## Estado actual: v0.1 preparada para publicación

### Implemented

- Repositorio Git con política de exclusión para secretos y artefactos locales.
- War Room con UI PHP/JavaScript y API JSON de solo lectura.
- Endpoints de salud, servicios, recursos, contenedores, tareas, manuales y
  diagnóstico operativo.
- Consumo read-only de manuales, checklist y JSON runtime.
- Exportador host de estado Docker con escritura atómica.
- Scripts de backup MariaDB y Uptime Kuma.
- Script de actualización de stack seguro por defecto y validado en dry-run.
- Plantillas saneadas de scripts operativos.
- Manuales de Git seguro, backups, Caddy/DNS, WireGuard y recuperación de War
  Room.
- Prueba documentada de restauración temporal MariaDB y extracción controlada
  del backup Uptime Kuma.

### Implemented locally, not reproducible from the public tree

- Stack Docker con MariaDB, Adminer, Uptime Kuma, Homepage y aplicaciones PHP.
- Reverse proxy Caddy y HTTPS con CA local.
- DNS interno dnsmasq bajo `.home.arpa`.
- WireGuard con configuración de servidor y peer.
- Configuración de Homepage y aplicaciones PHP locales.

Estos componentes existen en archivos ignorados y tuvieron contenedores locales,
pero no forman parte del contenido Git publicable. En la inspección del 12 de
julio de 2026 todos los contenedores HomeLab estaban detenidos.

### Known limitations

- No hay autenticación ni autorización dentro de War Room.
- Las definiciones públicas usan variables de entorno y fallbacks genéricos; la
  topología operativa permanece en un `.env` local ignorado.
- No hay ejecución periódica versionada del exportador Docker.
- Las métricas representan el contenedor, no necesariamente el host.
- Las sondas HTTP son secuenciales y pueden acumular timeouts cuando los
  servicios están caídos.
- La UI incluye contenido inicial/conceptual que puede confundirse con datos
  reales antes de cargar la API.
- No hay CI ni suite de tests; la auditoría manual de v0.1 con Gitleaks está
  completada.
- Las imágenes con etiqueta `latest` no son reproducibles.
- No hay Compose raíz ni procedimiento público completo de instalación.

## v0.1: preparada para publicación

### Completado

- [x] Completar las validaciones técnicas y de seguridad del checklist.
- [x] Crear la publicación desde un snapshot limpio sin material ignorado.
- [x] Parametrizar o sanear rutas personales, nombres internos y definiciones de
  servicios.
- [x] Añadir los ejemplos saneados incluidos en el alcance público.
- [x] Construir una imagen War Room con cURL y demostrar las sondas.
- [x] Añadir la licencia MIT explícita.
- [x] Ejecutar Gitleaks sobre contenido e historial.
- [x] Documentar un arranque mínimo reproducible de War Room.

### Acceptance criteria

- [x] Un repositorio limpio valida el Compose y War Room sin rutas del autor.
- [x] Los endpoints responden y degradan correctamente cuando faltan datos runtime.
- [x] Ningún secreto, backup, certificado, clave, IP LAN o dominio personal aparece
  en el snapshot ni en su historial.
- [x] README, arquitectura y comandos coinciden con el contenido preparado.

La única acción restante es crear el repositorio público en GitHub, añadir el
remoto y hacer push.

## v0.2: reproducibilidad y calidad

### Planned

- Actualización controlada de la imagen propia de War Room y sus dependencias.
- Configuración externa saneada para catálogo de servicios.
- Esquemas JSON para tareas y estado Docker.
- Tests de contrato para los endpoints PHP.
- Lint de PHP, ShellCheck, validación Compose y JSON en CI.
- Versiones o digest de imágenes en lugar de `latest`.
- Healthchecks para los servicios publicados como ejemplos.
- Ejecución documentada del exportador mediante systemd timer o cron.

## v0.3: operación local robusta

### Planned

- Autenticación delante de War Room y cabeceras de seguridad revisadas.
- Filtrado configurable de contenedores antes de generar el runtime JSON.
- Retención automatizada y verificación periódica de backups.
- Prueba completa de restauración de Uptime Kuma.
- Validación controlada del flujo real de actualización de stack.
- Separación formal entre configuración pública de ejemplo y overrides privados.
- Revisión final del acceso WireGuard y del modelo de exposición LAN/VPN.

## Future Work

No existe implementación actual para estas líneas:

- Operaciones controladas desde War Room v0.9.
- Orquestador o despliegue de HomeLab desde la interfaz.
- IA interna o copiloto documental.
- Alertas de seguridad y auditoría centralizadas.
- Inventario de múltiples hosts.
- Exposición pública de demos y SEO.
- Migración a mini PC.

Estas ideas requieren diseño de autenticación, autorización, auditoría y límites
de privilegios antes de convertirse en compromisos de versión.
