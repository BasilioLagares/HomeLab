# Cierre saneamiento documental v0.1

Fecha aproximada: 19 de mayo de 2026.

## Objetivo

Sanear documentación antigua antes de versionarla y evitar introducir en Git
secretos, topología real, certificados, configuración privada de VPN, puertos o
rutas sensibles.

## Resultado

Ningún documento antiguo sensible se versionó tal cual.

Se crearon equivalentes saneados:

- `docs/backups-configs-saneado-v1.md`
- `docs/caddy-dns-saneado-v1.md`
- `docs/mantenimiento-basico-saneado-v1.md`

## Decisiones

- Backups configs: reemplazado por documento saneado.
- Caddy/DNS/`.home.arpa`: reemplazado por documento saneado.
- Mantenimiento básico: reemplazado por documento saneado.
- WireGuard real: queda fuera de Git.
- Estado global antiguo: queda fuera de Git.
- Mapa de puertos real: queda fuera de Git.
- Docs antiguos: quedan como histórico local no versionado o pendientes de
  descarte.

## Riesgos evitados

- Secretos.
- Certificados/CA.
- Hashes de autenticación.
- Configuración privada de VPN.
- Topología.
- IPs.
- Puertos.
- Rutas personales.
- Nombres reales de backups.
- Comandos peligrosos sin contexto.

## Documentos saneados resultantes

`docs/backups-configs-saneado-v1.md`:

- Política saneada de backups de configuración.
- Diferencia backups de configuración, datos persistentes y dumps.
- Define principios de seguridad, automatización, rotación y restauración.

`docs/caddy-dns-saneado-v1.md`:

- Guía saneada de Caddy como reverse proxy interno.
- Explica DNS interno y uso conceptual de `.home.arpa`.
- Incluye URL canónica de War Room sin exponer configuración privada.

`docs/mantenimiento-basico-saneado-v1.md`:

- Guía saneada de mantenimiento básico.
- Incluye checklists antes/después de tocar servicios.
- Resume buenas prácticas Git, Docker, backups y postmortem sin comandos
  destructivos listos para ejecutar.

## Estado final

El saneamiento documental v0.1 queda cerrado.

El repositorio mantiene solo documentación saneada. Los documentos antiguos
sensibles siguen fuera de Git.

## Pendientes

- Decidir si borrar o archivar localmente docs antiguos sensibles.
- Crear manual WireGuard saneado si hace falta.
- Mantener la política de no versionar documentación sensible tal cual.
- Revisar de nuevo antes de cualquier push remoto futuro.
