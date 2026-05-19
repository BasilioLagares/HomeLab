# Backups y restauración

Este manual define criterios operativos saneados para copias y restauraciones.
No contiene rutas reales, nombres de backup, dumps ni datos privados.

## Política general

Un backup debe permitir volver a un estado conocido después de un fallo,
migración o error humano. Un backup que no se ha probado no debe considerarse
fiable.

Git no sustituye a los backups. Git versiona documentación, código y ejemplos
saneados; los backups conservan datos reales y estado persistente fuera del
repositorio.

## Tipos de backup

Backup de configuración:

- Guarda ficheros necesarios para reconstruir servicios.
- Debe excluir secretos si se va a documentar o versionar como ejemplo.

Backup de datos persistentes:

- Guarda volúmenes, datos de aplicaciones o estado que no se puede regenerar.
- Requiere revisar consistencia antes de copiar.

Dumps de base de datos:

- Exportan contenido de una base de datos.
- Pueden contener datos sensibles y nunca deben subirse a Git.

Snapshots/exportaciones:

- Capturan un estado completo o parcial.
- Deben almacenarse fuera del repositorio y con permisos adecuados.

## Checklist para crear backup

- Identificar qué se va a respaldar.
- Decidir si hay que parar el servicio según el tipo de dato.
- Generar la copia.
- Verificar que existe.
- Verificar que tiene tamaño razonable.
- Registrar fecha y alcance de forma saneada.
- Proteger permisos.
- Guardar fuera de Git.

## Checklist para restaurar

- No restaurar encima sin copia previa del estado actual.
- Verificar integridad del backup.
- Confirmar que el backup corresponde al servicio correcto.
- Parar el servicio si procede.
- Restaurar en la ubicación prevista.
- Levantar el servicio.
- Comprobar healthcheck.
- Revisar logs sin publicar secretos.
- Documentar resultado sin incluir datos sensibles.

## Advertencias

- No versionar backups.
- No subir dumps a Git.
- No confiar en un backup no probado.
- Probar restauración antes de necesitarla.
- No guardar backups en directorios servidos por aplicaciones web.
- No pegar logs completos si contienen rutas privadas, tokens o datos reales.

## Pendiente

- Definir pruebas periódicas de restauración.
- Crear plantillas saneadas por tipo de servicio.
- Registrar estado de backups en War Room solo como lectura y sin exponer rutas
  ni nombres privados.
