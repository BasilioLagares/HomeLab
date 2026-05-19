# Recuperación de War Room

Este manual describe qué revisar si War Room no carga. Está escrito como guía
saneada y no incluye rutas privadas, IPs ni configuraciones reales.

URL canónica actual:

```text
https://warroom.homelab.home.arpa
```

La ruta `.local` antigua no debe usarse como referencia principal.

## War Room no responde por localhost

Revisar:

- El contenedor o servicio de War Room está activo.
- El puerto local esperado responde.
- El frontend existe en el contenedor.
- La API `health.php` responde.

Diagnóstico de solo lectura:

```bash
docker ps --filter name=<war-room>
curl -I http://localhost:<puerto-local>
curl http://localhost:<puerto-local>/api/v1/health.php
```

No reiniciar como primer paso. Primero identificar si el problema es de
contenedor, puerto, aplicación o API.

## Responde por localhost pero no por dominio

Separar:

- fallo DNS;
- fallo de Caddy;
- regla de proxy incorrecta;
- backend no alcanzable desde el proxy.

Comprobaciones:

```bash
getent hosts warroom.homelab.home.arpa
curl -k -I https://warroom.homelab.home.arpa
curl -k https://warroom.homelab.home.arpa/api/v1/health.php
```

## Falla HTTPS/TLS

Si el navegador avisa de certificado:

- Confirmar que se usa la URL canónica.
- Confirmar que el certificado interno esperado está instalado o confiado en el
  cliente.
- Confirmar que Caddy está sirviendo el sitio correcto.

No copiar certificados reales ni cadenas completas en documentación.

## Falla DNS

Indicios:

- El nombre no resuelve.
- Resuelve a un destino inesperado.
- Otros servicios internos tampoco resuelven.

Revisar DNS interno antes de tocar la aplicación.

## Carga la UI pero no las APIs

Revisar:

- `health.php`.
- `status.php`.
- `manuals.php`.
- `operations.php`.
- Errores de navegador en la pestaña Network.

Usar solo consultas GET y no pegar respuestas que contengan datos sensibles.

## Manuales aparecen como no montados

Si el lector online muestra manuales no disponibles:

- Confirmar que la lista blanca contiene el slug.
- Confirmar que el fichero existe en la carpeta de manuales saneados.
- Confirmar que el mount read-only de manuales está aplicado al contenedor.
- Confirmar que el contenedor fue recreado después de cambiar mounts.

No montar `docs/` completo si solo hace falta `docs/manuals/`.

## Caché del navegador

Si la UI parece antigua:

- Usar Ctrl+F5.
- Probar ventana privada.
- Revisar que el JS cargado corresponde a la versión actual.

## Caddy responde con fallback

Si el dominio muestra una página distinta:

- Confirmar la regla del host.
- Confirmar que la petición llega al servicio correcto.
- Confirmar que no se está usando una URL antigua.

## Último recurso

Recrear o reiniciar servicios solo debe hacerse después de identificar el tipo
de fallo y revisar el impacto. Antes de hacerlo:

- Guardar estado relevante.
- Confirmar que no se perderán datos.
- Revisar logs de forma local sin publicarlos.
- Documentar el motivo de la acción.
