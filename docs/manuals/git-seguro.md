# Git seguro en el HomeLab

Git en el HomeLab sirve para versionar documentación, código propio y ejemplos
de configuración saneados. No debe usarse como almacén de secretos, backups,
runtime ni configuración privada real.

## Qué se puede versionar

Contenido adecuado para Git:

- Documentación saneada.
- Código propio sin secretos.
- Assets públicos necesarios para la aplicación.
- Scripts revisados que no contengan credenciales ni datos privados.
- Ficheros `.example` con placeholders claros.
- Checklists genéricos sin información sensible.

## Qué no se debe versionar

Mantener fuera de Git:

- `.env` y variantes locales.
- Tokens, passwords, claves privadas y credenciales.
- Certificados reales, CA locales y material criptográfico.
- Configuración real de VPN o proxy.
- Backups, dumps, bases de datos y archivos comprimidos.
- Logs, cachés, temporales y datos runtime.
- Rutas personales absolutas, usuarios reales o topología privada detallada.
- Composes reales si contienen rutas del host o detalles privados.

## Flujo recomendado

1. Revisar estado:

   ```bash
   git status --short
   ```

2. Revisar candidatos no trackeados:

   ```bash
   git ls-files --others --exclude-standard
   ```

3. Añadir de forma selectiva:

   ```bash
   git add ruta/segura
   ```

4. Revisar lo preparado:

   ```bash
   git diff --cached --stat
   git diff --cached --name-only
   git diff --cached
   ```

5. Confirmar que no hay secretos ni ficheros sensibles.

6. Crear commit con mensaje claro.

## Prohibición práctica de `git add .`

No usar `git add .` salvo revisión extrema del árbol completo. En un HomeLab es
habitual tener datos generados, backups, configuraciones reales y ficheros de
runtime cerca del código versionable.

La práctica recomendada es añadir rutas concretas y revisar el índice antes del
commit.

## Política de `.example`

Cuando una configuración real no deba versionarse:

- Mantener el fichero real fuera de Git.
- Crear un equivalente `.example`.
- Usar variables o placeholders.
- Evitar rutas personales absolutas.
- No incluir secretos ni valores reales.
- Documentar qué debe ajustar cada instalación local.

Ejemplos:

- `docker-compose.example.yml`
- `.env.example`
- `config.example.yml`

## Revisión antes de un push futuro

Antes de publicar en un remoto:

- Ejecutar `git status --short`.
- Revisar todos los commits pendientes.
- Buscar patrones de secretos en el contenido versionado.
- Confirmar que no hay backups, dumps, certificados ni claves.
- Confirmar que los ficheros `.example` no contienen valores reales.
- Revisar ramas, remoto y alcance de publicación.

Si aparece cualquier duda, detener el push y sanear antes.

## Checklist rápida

- `git status` revisado.
- `git diff --cached` revisado.
- Solo rutas esperadas en el índice.
- Sin `.env`.
- Sin claves ni certificados.
- Sin backups ni dumps.
- Sin runtime ni logs.
- Sin rutas personales absolutas.
- Sin configuraciones privadas reales.
