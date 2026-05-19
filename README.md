# HomeLab

Repositorio de configuración, documentación y herramientas seguras del HomeLab personal.

## Objetivo

Mantener de forma versionada la documentación, scripts no sensibles y componentes desarrollados para operar el HomeLab con criterios de seguridad, robustez y reproducibilidad.

## Componentes actuales

- War Room como panel visual de estado.
- Documentación base de la fase inicial.
- Scripts auxiliares no sensibles.
- Checklist y roadmap operativo pendiente de saneamiento antes de versionarse.

## Política de seguridad

Este repositorio no debe incluir:

- secretos;
- credenciales;
- tokens;
- claves privadas;
- certificados locales;
- configuraciones WireGuard reales;
- backups;
- dumps de bases de datos;
- datos persistentes de contenedores;
- logs;
- archivos runtime.

Las configuraciones reales sensibles deben mantenerse fuera de Git. Cuando sea necesario documentarlas, se crearán versiones saneadas o archivos `.example`.

## Estado

Repositorio inicializado con versionado seguro mínimo. Los documentos y scripts sensibles se revisarán antes de ser añadidos.
