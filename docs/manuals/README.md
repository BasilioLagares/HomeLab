# Manuales del HomeLab

Esta carpeta contiene la base documental saneada para la futura sección
Manuales de la War Room. Su objetivo es reunir procedimientos operativos
seguros, revisables y versionables sin incluir datos privados del entorno real.

## Manuales disponibles

- [War Room](war-room.md): descripción del panel, estado actual, validaciones y
  límites de seguridad.
- [Git seguro](git-seguro.md): política de versionado, flujo recomendado y
  revisión antes de publicar cambios.
- [Backups](backups.md): política general de copias, restauración conceptual y
  comprobaciones de integridad.
- [Caddy y DNS](caddy-dns.md): reverse proxy interno, DNS interno y diagnóstico
  seguro de acceso.
- [WireGuard](wireguard.md): administración remota privada, VPN y advertencias
  sobre claves y exposición pública.
- [Backups y restauración](backups-restauracion.md): procedimientos saneados
  para crear y restaurar copias.
- [Recuperación de War Room](recuperacion-war-room.md): diagnóstico seguro si la
  War Room no carga o sus APIs no responden.
- [Consola de operaciones catalogadas](terminal-ordenes.md): diseño de seguridad
  y contrato del futuro MVP diagnóstico, todavía sin implementación.

Todos los manuales de esta carpeta están pensados para consulta read-only desde
la War Room. Deben mantenerse saneados y libres de configuración privada.
El lector de War Room mantiene su propia allowlist; añadir un manual al
repositorio no lo habilita automáticamente en la interfaz.

## Política de seguridad documental

La documentación versionada debe explicar procesos, criterios y decisiones sin
exponer detalles sensibles. Antes de añadir o modificar un manual hay que
revisar que no incluya:

- Secretos, tokens, passwords, hashes de autenticación o claves privadas.
- Certificados reales, CA locales o material criptográfico.
- Direcciones IP reales, dominios públicos reales o topología privada detallada.
- Usuarios reales, rutas personales absolutas o nombres de host privados.
- Configuraciones reales de VPN, proxy, bases de datos o backups.
- Nombres de ficheros de backup reales, dumps o datos persistentes.

## Reglas prácticas

- Usar ejemplos genéricos y placeholders claros.
- Preferir ficheros `.example` para configuraciones versionables.
- Mantener fuera de Git los datos runtime, logs, backups y secretos.
- Revisar siempre con `git status`, `git diff` y `git diff --cached` antes de
  preparar un commit.
- No copiar literalmente documentos antiguos si contienen datos del entorno
  real; sanearlos o reescribirlos.

## Aviso

No guardar secretos ni configuraciones reales sensibles en esta carpeta. Si un
manual necesita mencionar una integración privada, debe hacerlo de forma
conceptual y remitir a configuración local no versionada.
