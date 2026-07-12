# Caddy y DNS interno

Este manual explica el papel de Caddy y del DNS interno en el HomeLab de forma
saneada. No contiene configuraciones reales, credenciales ni detalles privados
del despliegue.

## Propósito

Caddy actúa como reverse proxy interno: recibe peticiones HTTPS y las dirige al
servicio Docker correspondiente. Permite usar nombres claros para acceder a
servicios internos sin exponer detalles de contenedores, puertos o redes.

El DNS interno resuelve nombres del HomeLab hacia el punto de entrada correcto.
La ruta canónica actual de War Room es:

```text
https://warroom.example.home.arpa
```

La nomenclatura `.local` antigua no es la ruta canónica actual. La documentación
nueva debe preferir `.home.arpa` para nombres internos.

## Flujo conceptual

```text
navegador -> DNS interno -> Caddy -> servicio Docker
```

Pasos:

- El navegador solicita un nombre interno.
- El DNS interno resuelve ese nombre hacia el proxy.
- Caddy recibe la petición HTTPS.
- Caddy aplica la regla correspondiente.
- Caddy reenvía la petición al servicio interno.
- El servicio responde a través del proxy.

## Checklist de diagnóstico

- Comprobar que el dominio interno resuelve.
- Comprobar que Caddy responde.
- Comprobar que HTTPS interno funciona.
- Comprobar el endpoint de War Room.
- Separar el tipo de fallo:
  - fallo DNS: el nombre no resuelve;
  - fallo TLS: hay respuesta, pero el certificado no se acepta;
  - fallo de servicio: Caddy responde, pero el backend no;
  - fallo de ruta: Caddy llega a otro destino o a una página de fallback.

## Diagnóstico seguro

Usar comprobaciones de solo lectura:

```bash
getent hosts warroom.example.home.arpa
curl -k -I https://warroom.example.home.arpa
curl -k https://warroom.example.home.arpa/api/v1/health.php
```

No pegar en documentación salidas que contengan cabeceras privadas, cookies,
tokens, hashes o detalles reales del proxy.

## Advertencias

- No exponer paneles privados por internet sin diseño de seguridad.
- `robots.txt` no es una medida de seguridad.
- Las herramientas internas deben seguir protegidas.
- No subir un Caddyfile real si contiene credenciales, hashes o detalles
  sensibles.
- No mezclar reglas de demos públicas con administración privada sin revisión.

## Pendiente

- Crear ejemplos saneados de reglas Caddy si hacen falta.
- Documentar el flujo de diagnóstico sin depender de datos reales.
- Separar claramente nombres internos, demos públicas y servicios de
  administración.
