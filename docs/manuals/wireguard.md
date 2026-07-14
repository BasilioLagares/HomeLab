# WireGuard

Este manual resume el uso conceptual de WireGuard para administración remota
privada del HomeLab. No incluye configuraciones reales, claves, peers reales ni
datos privados.

## Propósito

WireGuard permite crear una VPN privada para acceder al HomeLab desde fuera de la
red local. Su uso recomendado es administrar servicios internos a través de un
canal privado en lugar de exponer paneles administrativos a internet.

VPN privada no es lo mismo que exposición pública:

- VPN privada: acceso restringido a dispositivos autorizados.
- Exposición pública: servicio accesible desde internet y diseñado para ello.

La administración debe ir preferentemente por VPN. Las demos públicas deben
tratarse como un caso separado, con su propia seguridad y superficie mínima.

## Checklist conceptual

- Servidor activo.
- Puerto UDP publicado correctamente.
- Peer configurado.
- Handshake reciente.
- DNS interno accesible desde la VPN.
- Rutas internas accesibles.
- Servicios administrativos no expuestos públicamente.

## Diagnóstico seguro

Comprobaciones aceptables:

- Comprobar estado del contenedor o servicio sin mostrar claves.
- Comprobar que existe handshake reciente sin publicar claves ni configuración.
- Comprobar resolución DNS interna desde el cliente VPN.
- Comprobar acceso a la URL canónica interna de War Room.

Ejemplos genéricos:

```bash
docker ps --filter name=<servicio-vpn>
wg show
getent hosts warroom.example.home.arpa
curl -k -I https://warroom.example.home.arpa
```

Si se comparte salida para diagnóstico, eliminar cualquier clave, endpoint
privado, identificador sensible o dato que permita reconstruir la configuración.

## Advertencias

- No guardar claves en Git.
- No compartir configuraciones reales de peers.
- No publicar claves privadas, claves precompartidas ni códigos QR reales.
- No mezclar WireGuard con demos públicas.
- No exponer paneles administrativos por internet.
- No documentar rutas internas reales si revelan topología sensible.

## Pendiente

- Definir checklist de revisión final de acceso remoto.
- Documentar restauración conceptual de peers sin incluir datos reales.
- Separar administración privada, demos públicas y monitorización.
