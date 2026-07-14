# Roadmap de HomeLab

Este documento es la fuente canónica única de la planificación de HomeLab.
`README.md`, los manuales, War Room y los ficheros de estado pueden resumir o
representar esta información, pero no deben mantener roadmaps independientes.

## Estado actual

- El trabajo de cierre del snapshot público `v0.1` se considera cerrado a nivel
  de roadmap. El snapshot está preparado localmente; las comprobaciones
  residuales de exportación, commit público y tag viven exclusivamente en
  `PRE_PUBLISH_CHECKLIST.md`.
- La allowlist pública está definida en `PUBLIC_V0.1_MANIFEST.txt` y separa el
  código publicable de la configuración, estado y operación privados.
- War Room funciona como panel PHP/JavaScript de solo lectura, sin acceso a
  `docker.sock` ni ejecución de comandos del host.
- El paquete público de War Room incluye Dockerfile, Compose de ejemplo,
  configuración de ejemplo, datos JSON saneados, manuales y exportador.
- Las validaciones realizadas cubren sintaxis, Compose, construcción de imagen,
  endpoints con datos de ejemplo, enlaces y detección de secretos.

## Tareas activas

### 5. Revisar Git y la seguridad del repositorio

- Conservar el historial operativo en local y crear el repositorio público desde
  un snapshot limpio separado, sin publicar las rutas personales antiguas.
- Usar `main` como rama por defecto y reservar `v0.1.0` para el commit inicial
  limpio del repositorio público.
- Confirmar el remoto y el repositorio de destino únicamente en la copia
  pública separada.
- Repetir sobre el commit definitivo las comprobaciones de secretos, rutas
  privadas, binarios, PHP, Bash, JSON y Compose.
- Mantener la allowlist y `.gitignore` como frontera explícita entre contenido
  público y entorno operativo.
- Mantener `SECURITY.md` como política pública mínima para comunicar problemas
  de seguridad.

### 6. SEO y exposición pública controlada

- Decidir si se expone únicamente el repositorio o también una demo.
- Preparar descripción, topics, portada y capturas saneadas para portfolio.
- Separar de forma visible la demostración pública de la infraestructura
  privada.
- No exponer War Room a Internet sin autenticación, restricción de red y
  cabeceras revisadas.
- Definir qué información puede indexarse y qué debe permanecer local.

### 7. Terminal de órdenes

- Diseñarla como componente separado de la War Room read-only.
- Definir una allowlist cerrada de órdenes; no admitir shell arbitraria.
- Diseñar autenticación, autorización, confirmaciones y registro de auditoría
  antes de implementar ejecución.
- Mantener secretos y acceso directo a `docker.sock` fuera del componente web.
- Separar claramente órdenes diagnósticas de acciones que modifican estado.

La terminal de órdenes no es un chatbot, una función de IA ni un cambio del
modelo de seguridad actual de War Room. Cualquier implementación requiere una
decisión arquitectónica explícita.

## Ideas futuras

### 8. Chatbot o IA interna de HomeLab

Idea futura para consultar documentación o información previamente saneada. No
es una tarea activa y no debe tener acceso a secretos, runtime sensible ni
ejecución de órdenes.

### 9. Asistente de voz tipo Jarvis para Linux

Idea externa para Linux general. No pertenece al alcance de HomeLab y, si se
desarrolla, debe gestionarse como un proyecto separado.

### 10. IA local integrada en HomeLab

Idea aparcada y no viable por ahora. No es una tarea, una dependencia ni un
compromiso de versión; solo puede reevaluarse si cambian los requisitos y los
recursos disponibles.

## Trabajo completado resumido

- Base Docker local con proxy, DNS, VPN, servicios y procedimientos operativos.
- War Room read-only con UI, API, manuales, tareas, recursos, servicios y estado
  de contenedores mediante JSON externo.
- Exportador host sin montar el socket Docker en la aplicación web.
- Plantillas saneadas de backup y actualización; scripts operativos reales
  conservados fuera del snapshot público.
- Manuales de Git seguro, backups, restauración, Caddy/DNS, WireGuard y
  recuperación de War Room.
- Política Git conservadora para secretos, certificados, backups, runtime y
  configuración específica del host.
- Snapshot público `v0.1` delimitado mediante allowlist y preparado para
  revisión final.
- Documentación pública alineada con el contenido reproducible y sin roadmaps
  secundarios.
- War Room empaquetado con Dockerfile, Compose, configuración y datos de
  ejemplo saneados.
- Exportación limpia del índice validada sin dependencias del árbol operativo.

La validación post-commit desde una clonación Git real y el resto de controles
de publicación se mantienen en `PRE_PUBLISH_CHECKLIST.md`; no se replican como
tareas activas en este roadmap.
