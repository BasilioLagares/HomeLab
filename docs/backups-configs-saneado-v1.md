# Backups de configuración saneados v1

## Objetivo

Este documento define una política saneada para backups de configuración del
HomeLab.

Un backup de configuración guarda ficheros necesarios para reconstruir o revisar
la configuración de un servicio. No equivale a un backup de datos persistentes y
no sustituye a los dumps de bases de datos.

Este documento no contiene rutas reales, nombres reales de backups, secretos ni
configuración privada.

## Alcance

Incluye:

- Configuraciones versionables o exportables tras revisión.
- Documentación operativa saneada.
- Scripts saneados y revisados.
- Plantillas de configuración sin valores privados.

Excluye:

- Secretos.
- Certificados.
- Claves.
- Configuración privada de VPN.
- Dumps.
- Bases de datos.
- Backups reales.
- Runtime.
- Logs.
- Datos persistentes de servicios.

## Principios de seguridad

- No versionar backups reales.
- No versionar dumps.
- No versionar certificados ni claves.
- No guardar contraseñas en documentación.
- No copiar rutas absolutas personales.
- Usar placeholders claros para valores locales.
- Mantener permisos restrictivos en ficheros generados.
- Validar el destino antes de escribir.
- Separar documentación saneada de configuración privada.

## Procedimiento conceptual

1. Preparar un destino local seguro para el backup.
2. Comprobar que el destino no está vacío, no es raíz y no apunta a una ruta
   inesperada.
3. Generar el backup con permisos restrictivos.
4. Validar que el fichero existe.
5. Validar que tiene un tamaño razonable.
6. Validar permisos y propietario local.
7. Registrar fecha, alcance y resultado de forma saneada.
8. Mantener el backup fuera de Git.

## Automatización

La automatización debe usar scripts revisados. Cualquier script real que genere
backups o toque servicios debe cumplir como mínimo:

- Usar `set -euo pipefail`.
- Usar `DRY_RUN` cuando exista impacto operativo.
- Validar rutas de entrada y destino.
- Usar `umask 077` si genera backups.
- Mostrar logs claros sin datos sensibles.
- No contener secretos hardcodeados.
- Fallar de forma explícita si falta configuración obligatoria.

Los ejemplos deben usar placeholders y no deben incluir rutas reales ni nombres
reales.

## Rotación

La retención debe definirse como política conceptual antes de automatizarla.

Antes de eliminar backups antiguos hay que validar:

- Directorio objetivo.
- Prefijo o patrón permitido.
- Profundidad máxima.
- Antigüedad mínima.
- Modo de simulación previo.

No se deben usar borrados amplios ni comandos destructivos sin validación
explícita. La rotación debe revisarse primero en modo simulación.

## Restauración

Un backup que no se ha probado no debe considerarse confiable.

La prueba de restauración debe hacerse en entorno temporal, sin tocar servicios
vivos y sin restaurar sobre datos reales.

Documentos relacionados:

- `docs/restauracion-backups-plan-v0.1.md`
- `docs/restauracion-backups-cierre-v0.1.md`

## Criterios de éxito

- El backup existe.
- El tamaño es razonable.
- Los permisos son adecuados.
- La inspección o restauración temporal se valida correctamente.
- El backup no entra en Git.
- El backup no contiene secretos que deban separarse.

## Qué no hacer

- No subir backups a Git.
- No documentar secretos.
- No copiar comandos destructivos sin contexto.
- No borrar backups sin validación previa.
- No mezclar backups de configuración con dumps o datos persistentes.
- No publicar configuraciones privadas como documentación versionable.

## Estado final

Este documento es una versión saneada apta para versionar.

Los documentos antiguos quedan como histórico local no versionado o pendientes
de descarte tras revisar si siguen aportando valor.
