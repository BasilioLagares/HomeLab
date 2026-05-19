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
- Estado revisado: ficheros de checklist o tareas sin secretos.
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
- `GET /api/v1/tasks.php`: checklist o tareas saneadas si están disponibles.

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

## Roadmap

Manuales:

- Integrar esta carpeta como fuente de la sección Manuales.
- Añadir navegación y lectura segura desde la interfaz.
- Definir formato estable para manuales versionados.

Operaciones:

- Exponer únicamente estados y comprobaciones de solo lectura.
- Añadir validaciones sobre datos runtime antes de mostrarlos.
- Separar alertas, auditoría y acciones en componentes independientes.

Deploy/Orchestrator:

- Diseñar un componente separado para despliegues.
- Exigir autenticación, permisos explícitos y registro de auditoría.
- Mantener War Room como visor hasta que exista un diseño revisado.

IA interna:

- Evaluar asistencia contextual sobre documentación versionada.
- Evitar que la IA acceda a secretos o datos runtime sensibles.
- Mantener acciones automatizadas fuera de alcance hasta una fase específica de
  seguridad y auditoría.
