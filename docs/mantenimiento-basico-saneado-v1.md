# Mantenimiento básico saneado v1

## Objetivo

Este documento recoge prácticas seguras de mantenimiento básico del HomeLab y
lecciones aprendidas para reducir errores operativos.

Sirve como guía general antes de tocar servicios, actualizar stacks o revisar
incidencias.

No contiene rutas reales, credenciales, datos sensibles ni configuración
privada.

## Principios generales

- No ejecutar comandos destructivos sin entender su impacto.
- No tocar volúmenes reales sin backup previo.
- No ejecutar operaciones que eliminen volúmenes salvo en un caso
  extremadamente controlado.
- Preferir `DRY_RUN` cuando exista.
- Confirmar backup antes de operaciones con impacto.
- Validar estado antes y después.
- Documentar cambios importantes.
- Separar documentación saneada de configuración privada.

## Checklist antes de tocar un servicio

- Identificar servicio y stack afectados.
- Confirmar URL, endpoint o healthcheck esperado.
- Confirmar si hay backup reciente.
- Confirmar si el cambio requiere parada.
- Confirmar si existe rollback razonable.
- Revisar logs sin pegar secretos.
- Preparar una ventana de mantenimiento si hay impacto.
- Confirmar qué archivos podrían cambiar.

## Checklist después de tocar un servicio

- Comprobar que el contenedor está arriba.
- Comprobar healthcheck o endpoint esperado.
- Comprobar URL canónica si aplica.
- Revisar logs sin publicar datos sensibles.
- Comprobar que no hay cambios no deseados en Git.
- Documentar resultado, incidencia y validaciones.

## Git y cambios locales

- Usar `git status` antes de preparar cambios.
- No usar `git add .` salvo revisión extrema.
- Usar `git add` selectivo.
- Revisar `git diff` y `git diff --cached`.
- No versionar secretos, backups, dumps, runtime, certificados, configuración
  privada de VPN ni compose real sensible.
- Crear archivos `.example` cuando proceda.
- Mantener documentación saneada separada de datos reales.

## Docker y servicios

Conceptos básicos:

- `pull`: descarga imágenes nuevas.
- `up -d`: crea o actualiza servicios definidos por el compose.
- `stop/start`: detiene o arranca servicios sin eliminar recursos.
- `down`: elimina contenedores y redes creadas por el compose.
- La variante que elimina volúmenes puede borrar datos persistentes.

Reglas prácticas:

- No eliminar volúmenes sin backup verificado.
- No ejecutar actualizaciones reales sin revisar el alcance.
- No parar servicios sin entender el impacto.
- Usar scripts saneados cuando existan.
- Validar después de cualquier operación con impacto.

Scripts relacionados:

- `scripts/backup-mariadb.sh`
- `scripts/backup-uptime-kuma.sh`
- `scripts/update-stack.sh`
- `scripts/examples/`

## Backups y restauración

Un backup no probado no debe considerarse fiable.

Antes de una operación con impacto:

- Confirmar que existe backup reciente.
- Confirmar permisos y tamaño razonable.
- Confirmar si la restauración se ha probado.
- Evitar restaurar sobre datos reales sin plan.

Documentos relacionados:

- `docs/backups-configs-saneado-v1.md`
- `docs/restauracion-backups-plan-v0.1.md`
- `docs/restauracion-backups-cierre-v0.1.md`

## Incidencias y postmortem

Una incidencia debe documentarse de forma breve y útil:

- Qué pasó.
- Impacto.
- Causa probable.
- Solución aplicada.
- Validaciones realizadas.
- Prevención futura.

No se deben pegar secretos, dumps, tokens ni logs sensibles.

## Qué no hacer

- No copiar comandos de internet sin adaptar.
- No restaurar sobre datos reales sin plan.
- No borrar volúmenes sin backup verificado.
- No exponer paneles internos.
- No meter contraseñas en scripts.
- No subir documentación sensible tal cual.
- No publicar logs completos si contienen datos privados.

## Estado final

Este documento es una versión saneada apta para versionar.

El documento antiguo queda como histórico local no versionado o pendiente de
descarte tras revisar si sigue aportando valor.
