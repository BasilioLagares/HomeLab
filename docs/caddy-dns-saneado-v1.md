# Caddy, DNS interno y .home.arpa saneado v1

## Objetivo

Este documento describe de forma saneada el uso de Caddy como reverse proxy
interno, el uso de DNS interno para nombres del HomeLab y la migración
conceptual hacia `.home.arpa`.

No contiene Caddyfile real, IPs reales, certificados, claves, hashes,
credenciales ni topología privada completa.

## Arquitectura conceptual

Flujo genérico:

```text
Navegador o cliente
  -> DNS interno
  -> Caddy como reverse proxy
  -> Servicio Docker interno
```

Responsabilidades:

- El cliente solicita un nombre interno.
- El DNS interno resuelve ese nombre hacia el servidor del HomeLab.
- Caddy recibe la petición HTTPS.
- Caddy reenvía la petición al servicio interno correspondiente.
- El servicio responde sin quedar publicado directamente.

## Dominio canónico

La ruta canónica actual de War Room es:

- `https://warroom.homelab.home.arpa`

Si existió una ruta `.local`, queda como ruta antigua o no canónica.

El dominio `.home.arpa` es más adecuado para una red doméstica o laboratorio
interno porque está reservado para este tipo de uso y evita conflictos habituales
con mecanismos de descubrimiento local.

## Caddy como reverse proxy

Caddy actúa como punto de entrada HTTP/HTTPS interno para servicios del HomeLab.

Funciones principales:

- TLS interno.
- Reverse proxy hacia servicios internos.
- URLs limpias para servicios.
- Separación entre servicios privados y exposición pública.
- Posibilidad de aplicar controles de acceso en servicios sensibles.

Este documento no incluye:

- Caddyfile real.
- Hashes reales de autenticación.
- Rutas reales a certificados.
- Puertos privados reales.
- Configuración privada de servicios.

Ejemplo conceptual:

```text
<servicio>.homelab.home.arpa
  -> Caddy
  -> <servicio-interno>:<puerto-interno>
```

## DNS interno

El DNS interno permite resolver nombres del HomeLab sin depender de recordar IPs.

Funciones principales:

- Resolver nombres internos.
- Facilitar acceso desde LAN o VPN.
- Mantener URLs estables aunque cambie la organización interna.
- Separar nombres privados de dominios públicos.

Ejemplos con placeholders:

```text
<servicio>.homelab.home.arpa -> <ip-servidor-homelab>
<dominio-interno>            -> <ip-servidor-homelab>
```

No se documentan IPs reales, MACs, router real ni configuración real de dnsmasq.

## HTTPS interno / CA local

Caddy puede usar una CA interna para emitir certificados de uso local.

En ese modelo:

- Caddy sirve HTTPS dentro de la red privada.
- Los clientes pueden necesitar confiar en la CA local.
- Los certificados y claves son material sensible.
- La CA local no debe versionarse.

Este documento no incluye certificados reales, rutas reales ni procedimientos
basados en ficheros privados.

## Diagnóstico seguro

Checklist conceptual:

- Comprobar resolución DNS del nombre interno.
- Comprobar que Caddy responde.
- Diferenciar fallo DNS, fallo TLS y fallo del servicio.
- Comprobar el endpoint de War Room.
- Confirmar que se usa `.home.arpa` y no `.local`.
- Revisar logs sin pegar secretos ni configuración sensible.

Comandos genéricos:

```bash
curl -k https://warroom.homelab.home.arpa
curl -k https://warroom.homelab.home.arpa/api/v1/health.php
curl -k https://<servicio>.homelab.home.arpa
```

Interpretación básica:

- Si el nombre no resuelve, revisar DNS interno.
- Si resuelve pero TLS falla, revisar confianza de CA local o configuración TLS.
- Si HTTPS responde con error de aplicación, revisar el servicio interno.
- Si Caddy responde con una página inesperada, revisar la regla de proxy.

## Seguridad

- No exponer paneles privados a internet.
- Adminer y herramientas internas deben permanecer protegidas.
- `robots.txt` no es seguridad.
- Separar zona privada/VPN de demos públicas.
- No versionar el Caddyfile real si contiene secretos, rutas sensibles o
  autenticación real.
- No versionar certificados ni claves.
- No publicar topología interna completa.

## Relación con War Room

War Room debe mostrar URLs canónicas saneadas y evitar rutas privadas.

La URL canónica actual se mantiene como referencia operativa:

- `https://warroom.homelab.home.arpa`

Las secciones Manuales y Operaciones deben seguir documentando el sistema en
modo read-only, sin exponer configuración privada ni permitir acciones
administrativas.

## Estado final

Este documento es una versión saneada apta para versionar.

Los documentos antiguos quedan como histórico local no versionado o pendientes
de descarte tras revisar si siguen aportando valor.
