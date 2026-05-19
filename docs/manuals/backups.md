# Backups del HomeLab

Este manual define una política general de backups para el HomeLab. No contiene
rutas reales, nombres de ficheros privados, dumps ni configuraciones sensibles.

## Objetivo

Los backups deben permitir recuperar servicios, configuraciones y datos
importantes ante errores humanos, fallos de almacenamiento o migraciones. La
estrategia debe ser simple, verificable y separada del repositorio Git.

Git no sustituye a un sistema de backups. Git versiona documentación, código y
ejemplos saneados; los backups guardan datos operativos y estado real.

## Qué respaldar

Definir por servicio:

- Configuración necesaria para reconstruir el servicio.
- Datos persistentes que no puedan regenerarse.
- Metadatos de despliegue que no contengan secretos.
- Documentación operativa saneada.

Separar datos sensibles de ejemplos versionables. Si un servicio requiere
secretos, esos secretos deben almacenarse en un mecanismo local seguro fuera de
Git.

## Qué no versionar

No añadir a Git:

- Archivos de backup.
- Dumps de bases de datos.
- Bases de datos completas.
- Archivos comprimidos.
- Logs o capturas de runtime.
- Certificados, claves, tokens o credenciales.
- Inventarios privados con datos sensibles.

## Procedimiento general de backup

1. Identificar el servicio y su estado persistente.
2. Parar o congelar escrituras solo si el procedimiento lo requiere y está
   documentado.
3. Generar la copia en una ubicación local no versionada.
4. Aplicar permisos restrictivos al resultado.
5. Calcular una suma de verificación.
6. Registrar fecha, alcance y resultado en una nota saneada si procede.
7. Probar restauración en entorno controlado antes de confiar en la copia.

No ejecutar procedimientos de backup desde la War Room mientras siga en modo
read-only.

## Restauración conceptual

Una restauración debe tratarse como operación controlada:

- Confirmar qué servicio se va a restaurar.
- Identificar el backup correcto sin exponer su contenido.
- Verificar integridad antes de usarlo.
- Revisar permisos y propietario esperados.
- Restaurar primero en entorno de prueba cuando sea posible.
- Validar el servicio con comprobaciones de solo lectura.
- Documentar incidencias sin incluir datos sensibles.

## Integridad y permisos

Cada política de backup debe definir:

- Frecuencia.
- Retención.
- Ubicación segura.
- Cifrado si aplica.
- Verificación de integridad.
- Prueba periódica de restauración.
- Propietario responsable.

Los backups deben tener permisos mínimos y no deben quedar accesibles desde
servicios web o directorios públicos.

## Checklist operativa

- Alcance del backup definido.
- Secretos fuera de Git.
- Ubicación no versionada.
- Permisos revisados.
- Integridad verificada.
- Restauración probada o planificada.
- Retención documentada.
- Riesgos pendientes anotados.

## Pendiente para futuras fases

- Definir inventario saneado de servicios respaldados.
- Crear plantillas de procedimientos por tipo de servicio.
- Añadir validaciones automáticas de integridad.
- Documentar restauraciones de prueba sin datos reales.
- Integrar estado de backups en War Room solo como lectura y sin exponer rutas o
  nombres privados.
