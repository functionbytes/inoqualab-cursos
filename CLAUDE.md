# Proyecto Manager — Entorno Docker

## IMPORTANTE: Todo se ejecuta dentro de Docker

Este proyecto corre completamente en Docker. **Nunca ejecutes `php`, `artisan`, `composer` ni cualquier herramienta del stack directamente en el host Mac.** Todas las acciones deben hacerse dentro del contenedor `app`.

## Contenedores

| Contenedor        | Propósito                                              |
|-------------------|--------------------------------------------------------|
| `app`             | PHP 8.4-FPM + Laravel (principal)                      |
| `worker`          | Queue worker — `queue:work` (proceso de larga duración)|
| `nginx`           | Servidor web — puerto 8080                             |
| `redis`           | Redis 7 — puerto 6379                                  |
| `redis-commander` | UI Redis — puerto 8081                                 |
| `mailpit`         | Captura de emails — puerto 8025                        |

Docker Compose está en: `../docker/docker-compose.yml` (relativo a `src/`)  
Directorio de trabajo dentro del contenedor: `/var/www`

## CRÍTICO: Reiniciar SIEMPRE los 3 contenedores al editar PHP

El contenedor `worker` ejecuta `php artisan queue:work` como proceso de **larga duración**. No recarga código automáticamente. Si solo reinicias `app`, el worker sigue con código viejo.

**Después de cualquier edición de archivos `.php` hay que reiniciar los 3:**

```bash
# CORRECTO — reiniciar app + worker + nginx juntos
cd /Users/developerts/Herd/manager/docker && docker compose restart app worker nginx

# INCORRECTO — el worker mantiene el código viejo en memoria
cd /Users/developerts/Herd/manager/docker && docker compose restart app
```

> Si `nginx` da 502 después del restart es porque cambió la IP del contenedor `app`. Reinicia siempre los 3 juntos para evitarlo.

## IMPORTANTE: sintaxis obligatoria para docker exec

Docker Desktop en Mac bloquea `docker exec` cuando el CWD del shell es un directorio montado como volumen (`/var/www`). **Siempre** usa `-w /tmp` y rutas absolutas:

```bash
# CORRECTO — siempre con -w /tmp
docker exec -w /tmp app php /var/www/artisan <comando>
docker exec -w /tmp app composer --working-dir=/var/www <comando>
docker exec -w /tmp app php /var/www/<script.php>

# INCORRECTO — falla con "container breakout detected"
docker exec app php artisan <comando>
```

## Comandos esenciales

```bash
# Artisan
docker exec -w /tmp app php /var/www/artisan <comando>

# Composer
docker exec -w /tmp app composer --working-dir=/var/www <comando>

# PHP directo
docker exec -w /tmp app php /var/www/<archivo>

# Abrir shell en el contenedor (bash ya maneja el CWD internamente)
docker exec -it -w /var/www app bash

# Ver logs del contenedor
docker logs app -f

# Reiniciar contenedores (desde el directorio docker/)
cd /Users/developerts/Herd/manager/docker && docker compose restart

# Levantar / detener
cd /Users/developerts/Herd/manager/docker && docker compose up -d
cd /Users/developerts/Herd/manager/docker && docker compose down
```

## Comandos frecuentes de Laravel

```bash
# Limpiar caché
docker exec -w /tmp app php /var/www/artisan optimize:clear

# Migraciones
docker exec -w /tmp app php /var/www/artisan migrate
docker exec -w /tmp app php /var/www/artisan migrate:status

# Rutas
docker exec -w /tmp app php /var/www/artisan route:list

# Tests
docker exec -w /tmp app php /var/www/artisan test
docker exec -w /tmp app php /var/www/vendor/bin/phpunit

# Tinker
docker exec -it -w /var/www app php /var/www/artisan tinker
```

## Base de datos

- **MySQL**: host `host.docker.internal`, puerto `3306`, DB `managerchat`
- **Oracle**: host `192.168.253.8`, puerto `1521`, servicio `GESTCENT`

## Notas

- El código fuente en `src/` está montado como volumen en `/var/www` — los cambios de archivo son inmediatos, sin necesidad de reconstruir la imagen.
- Para reconstruir la imagen: `cd /Users/developerts/Herd/manager/docker && docker compose build`
- El usuario dentro del contenedor es `www` (uid 1000).
