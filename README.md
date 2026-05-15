# Taller Cookies - Docker

Esta configuración prepara tu aplicación PHP con MySQL usando Docker.

## Archivos creados

- `Dockerfile`
- `docker-compose.yml`
- `.env.example`
- `.gitignore`
- `.dockerignore`
- `docker/mysql-init/init.sql`

## Uso

1. Copia el ejemplo de entorno:

   ```bash
   cp .env.example .env
   ```

2. Define tus valores reales en `.env`.

3. Arranca los servicios:

   ```bash
   docker compose up --build
   ```

4. Abre en el navegador:

   ```text
   http://localhost:8000
   ```

5. Para sembrar usuarios de prueba con contraseñas seguras:

   ```bash
   docker compose exec app php seed_usuarios.php
   ```

## Despliegue en GitHub

- No subas tu archivo `.env` a GitHub.
- El repositorio debe incluir `.env.example` y `.gitignore`.
- Antes de subir, verifica que `.env` esté en la lista de exclusión.
- Tu `Dockerfile` y `docker-compose.yml` permiten ejecutar el proyecto localmente y mantener la configuración separada.

## Despliegue en Dokku

Este proyecto está listo para usar con Dokku gracias al `Dockerfile`.

1. Crea la aplicación en Dokku:

   ```bash
   dokku apps:create taller_cookies
   ```

2. Instala y crea el servicio MySQL (si no lo tienes aún):

   ```bash
   sudo dokku plugin:install https://github.com/dokku/dokku-mysql.git mysql
   dokku mysql:create taller_cookies-db
   dokku mysql:link taller_cookies-db taller_cookies
   ```

3. Configura las variables de entorno en Dokku.
   Si la base de datos ya está ligada, revisa los valores con:

   ```bash
   dokku config taller_cookies
   ```

   Luego ajusta los valores `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` si es necesario:

   ```bash
   dokku config:set taller_cookies DB_HOST=<host> DB_NAME=<database> DB_USER=<user> DB_PASS=<password>
   ```

4. Empuja tu repositorio a Dokku:

   ```bash
   git remote add dokku dokku@<TU_SERVIDOR>:taller_cookies
   git push dokku main
   ```

> El archivo `Dockerfile` es suficiente para que Dokku construya la imagen.

## Seguridad

- No subas `.env` a GitHub.
- Usa `.gitignore` para excluir `.env`.
- `.dockerignore` evita incluir archivos innecesarios en la imagen.
