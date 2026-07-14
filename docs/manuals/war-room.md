# Manual de War Room

War Room es el panel local de observabilidad del HomeLab. Está pensado como una
vista de estado y consulta, no como un panel de control operativo.

## Estado actual

La War Room funciona en modo read-only:

- Muestra información del frontend servido desde `public/`.
- Consume endpoints API locales de solo lectura.
- Puede leer ficheros de estado o runtime montados en solo lectura.
- No ejecuta acciones sobre servicios.
- No reinicia contenedores.
- No despliega proyectos.
- No modifica configuración del host.

## Componentes generales

- Frontend: interfaz web estática/dinámica para consultar estado.
- API v1: endpoints JSON bajo `public/api/v1/`.
- Planificación: proyección operativa de `ROADMAP.md` sin secretos.
- Runtime externo: datos generados por exportadores fuera de la aplicación.
- Proxy local: capa de acceso gestionada fuera de la War Room.

El diseño separa la visualización de la operación. Cualquier acción con impacto
en infraestructura debe vivir en herramientas separadas, revisadas y con
permisos explícitos.

## Endpoints API

Endpoints disponibles a alto nivel:

- `GET /api/v1/health.php`: comprobación básica de disponibilidad.
- `GET /api/v1/status.php`: resumen general del estado conocido.
- `GET /api/v1/services.php`: listado de servicios definidos por la aplicación.
- `GET /api/v1/resources.php`: métricas visibles desde el contenedor.
- `GET /api/v1/containers.php`: estado leído desde runtime externo si existe.
- `GET /api/v1/tasks.php`: proyección del roadmap si está disponible.

La API debe devolver JSON, evitar caché para datos dinámicos y filtrar cualquier
dato externo antes de mostrarlo en el frontend.

## Validación segura

Antes de considerar la War Room lista para una fase, revisar:

- El contenedor o servicio web responde en el entorno local previsto.
- `health.php` devuelve una respuesta JSON válida.
- Los endpoints de estado responden sin errores fatales.
- Si no hay runtime externo, la API lo comunica como no disponible o stale.
- No hay secretos, tokens, certificados ni rutas privadas en respuestas públicas.
- La interfaz no ofrece botones de reinicio, despliegue o ejecución de comandos.

Usar comandos de consulta de solo lectura y evitar acciones que modifiquen
servicios durante una validación documental.

## Qué no debe hacer todavía

War Room no debe:

- Ejecutar comandos del sistema.
- Reiniciar servicios o contenedores.
- Tocar Docker directamente.
- Montar sockets de administración.
- Desplegar proyectos.
- Gestionar certificados, secretos o credenciales.
- Escribir en directorios de runtime o estado desde la aplicación web.

## Checklist operativa

Antes de cambiar War Room:

- Revisar `git status`.
- Revisar el diff completo.
- Confirmar que los cambios son de lectura o presentación.
- Confirmar que no se añaden secretos ni rutas privadas.
- Confirmar que no se introducen acciones operativas desde la API.
- Validar sintaxis de los ficheros modificados.
- Documentar limitaciones y próximos pasos sin prometer funciones pendientes.

## Planificación

La fuente canónica de tareas activas e ideas futuras es
[`ROADMAP.md`](../../ROADMAP.md). El JSON de estado es solo su proyección
operativa y este manual no mantiene una lista paralela de pendientes.

War Room continúa siendo un visor read-only. La consola de operaciones está
diseñada como componente separado, con autenticación, allowlist y auditoría
propias; su implementación permanece aplazada. Las ideas de chatbot o IA
interna permanecen fuera del trabajo activo y no se mezclan con la consola.
