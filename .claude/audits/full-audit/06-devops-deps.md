# Auditoría DevOps & Dependencias — training (LMS + E-commerce)

**Fecha**: 2026-06-13  
**Entorno**: Herd nativo (macOS), Laravel 12.61.1, PHP ^8.3

---

## 1. DEPENDENCIAS PHP

### 1.1 Paquetes instalados (composer.lock)

| Paquete | Versión lock | Constraint | Estado |
|---|---|---|---|
| laravel/framework | 12.61.1 | ^12.0 | OK |
| laravel/reverb | 1.10.2 | ^1.0 | OK |
| laravel/sanctum | 4.3.2 | ^4.0 | OK |
| laravel/ui | 4.6.3 | ^4.5 | OK |
| spatie/laravel-medialibrary | 11.23.0 | ^11.0 | OK |
| spatie/laravel-activitylog | 4.12.3 | ^4.8 | OK |
| spatie/laravel-analytics | 5.7.1 | ^5.2 | OK |
| spatie/laravel-pdf | 1.9.0 | ^1.2 | OK |
| spatie/laravel-image-optimizer | 1.7.x | ^1.7 | OK |
| maatwebsite/excel | 3.1.69 | ^3.1 | RIESGO — ver abajo |
| barryvdh/laravel-dompdf | 3.1.2 | ^3.1 | OK |
| aws/aws-sdk-php | 3.384.5 | ^3.342 | OK |
| guzzlehttp/guzzle | 7.x | ^7.2 | OK |
| nesbot/carbon | 3.11.4 | ^3.0 | OK |
| twig/twig | 3.27.1 | ^3.0 | OK |
| webklex/laravel-imap | 6.2.0 | ^6.2 | OK |
| artesaos/seotools | 1.4.1 | * | RIESGO — constraint sin fijar |

### 1.2 Problemas críticos de dependencias

#### CRITICO: spatie/laravel-permission NO está en composer.json ni en vendor
- El proyecto usa `$user->can()` con permisos Spatie en toda la codebase (controllers, form requests, policies).
- El paquete **no aparece en composer.lock ni en vendor/spatie/**.
- Consecuencia: toda la autorización RBAC falla en producción si el vendor fue regenerado limpio.
- **Fix**: `composer require spatie/laravel-permission:^6.0`

#### CRITICO: nwidart/laravel-modules NO está instalado
- CLAUDE.md, reglas y estructura del proyecto asumen módulos en `modules/` con nwidart.
- El directorio `modules/` no existe. La gestión de módulos referenciada en las reglas no tiene base instalada.
- **Fix**: `composer require nwidart/laravel-modules` o confirmar si el proyecto abandonó módulos.

#### ADVERTENCIA: artesaos/seotools con constraint `"*"`
- Sin versión fijada; una actualización mayor podría romper la API silenciosamente.
- **Fix**: Cambiar a `"^1.4"` en composer.json.

#### ADVERTENCIA: maatwebsite/excel 3.1.69
- Excel 3.1 usa PhpSpreadsheet ~1.x. La versión 4.x de maatwebsite (con PhpSpreadsheet 2.x) ya fue lanzada y la rama 3.x recibe solo correcciones críticas.
- Compatibilidad con PHP 8.4 puede tener fricciones en 3.1.x.
- **Fix**: Evaluar migración a `maatwebsite/excel:^4.0` en próximo ciclo.

#### ADVERTENCIA: spatie/laravel-ignition solo en require-dev
- Ignition está correctamente en dev, pero no hay stack de errores para producción (Flare/Sentry).
- `spatie/flare-client-php` está en el lock como dependencia transitiva pero `FLARE_KEY` no aparece en .env ni .env.example.
- Ver sección 6.

### 1.3 Paquetes ausentes recomendados

| Paquete | Razón |
|---|---|
| `spatie/laravel-permission` | CRITICO — RBAC usado en todo el código |
| `laravel/horizon` | Cola Redis activa; Horizon da visibilidad y control |
| `laravel/telescope` | Debugging de producción/staging |
| `laravel/pulse` | Métricas de app (mencionado en reglas del proyecto) |

---

## 2. DEPENDENCIAS JS (package.json)

### 2.1 Estado actual

```json
devDependencies:
  axios: ^1.6.4
  laravel-vite-plugin: ^1.0.0
  vite: ^5.0.0

dependencies:
  herd: ^1.0.0   ← ANOMALIA
```

### 2.2 Problemas

#### ADVERTENCIA: `herd: ^1.0.0` en dependencies
- Un paquete llamado `herd` en npm no corresponde a Laravel Herd. Es una dependencia fantasma o error tipográfico.
- **Fix**: Eliminar de `dependencies`.

#### ADVERTENCIA: Vite 5.x — versión disponible es Vite 6.x
- `laravel-vite-plugin ^1.0` es compatible con Vite 5. La v2.x del plugin requiere Vite 6.
- No es urgente pero el ecosistema se mueve a Vite 6.
- **Fix**: Actualizar en próximo ciclo de mantenimiento.

#### INFO: Bootstrap 4.5.3 en frontend público + Bootstrap 5.3 en panel admin
- Coexistencia documentada y esperada (dos layouts distintos).
- Bootstrap 4.5.3 se sirve desde `public/pages/css/` como archivo estático; no gestionado por npm.
- Bootstrap 5 se sirve desde `public/managers/libs/bootstrap/`.
- **No hay conflicto real** siempre que no se mezclen clases en el mismo DOM.
- Riesgo: `fw-bold`, `gap-*`, `me-*` (clases Bootstrap 5) no existen en Bootstrap 4 — las reglas de Blade ya lo documentan.

#### INFO: jQuery no está en package.json
- jQuery se carga desde `public/` como asset estático (no via npm/vite).
- Esto impide tree-shaking y actualización controlada.
- **Recomendación futura**: mover jQuery a npm y bundlear con Vite si se refactoriza el front.

#### INFO: Sin axios en uso real
- `axios` está en devDependencies pero el stack JS usa jQuery+AJAX (regla del proyecto).
- Se puede eliminar si no hay uso directo.

---

## 3. CONFIGURACIÓN

### 3.1 Secrets en config/ — Estado

| Archivo | Estado |
|---|---|
| `config/services.php` | OK — todo via `env()` |
| `config/reverb.php` | OK — todo via `env()` |
| `config/analytics.php` | ADVERTENCIA — credencial via path de archivo |
| `config/database.php` | OK — todo via `env()` |
| `config/queue.php` | OK — default `sync`, correcto para dev |

#### ADVERTENCIA: config/analytics.php — service account JSON
```php
'service_account_credentials_json' => storage_path('app/analytics/service-account-credentials.json'),
```
- El archivo de credenciales de Google Analytics vive en `storage/app/analytics/`.
- Ese path no está en `.gitignore`. Si el archivo existe, puede ser commiteado accidentalmente.
- **Fix**: Agregar `storage/app/analytics/` o `*.json` a `.gitignore`, o documentar que el archivo se provee via deploy secrets.

### 3.2 Wompi (pasarela de pagos)
```php
'wompi' => [
    'sandbox' => env('WOMPI_SANDBOX', true),  // default true = seguro
    ...
]
```
- El default `sandbox: true` es correcto como protección. OK.
- Las keys no tienen valores hardcodeados. OK.

### 3.3 .env.example — FALTANTE
- **El archivo `.env.example` NO EXISTE** en el repositorio.
- Esto es crítico para onboarding y para documentar variables requeridas.
- Variables conocidas que necesitan estar documentadas:

```
# App
APP_KEY=
APP_ENV=production
APP_DEBUG=false
APP_URL=

# DB
DB_CONNECTION=mysql
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_PASSWORD_SECOND=   # Segunda conexión Oracle/MySQL

# Cache/Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAILJET_APIKEY=
MAILJET_APISECRET=

# AWS
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=

# Wompi (pagos)
WOMPI_PUBLIC_KEY=
WOMPI_INTEGRITY_SECRET=
WOMPI_EVENTS_SECRET=
WOMPI_SANDBOX=true

# Reverb (WebSockets)
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=
REVERB_PORT=443
REVERB_SCHEME=https

# IMAP (incoming mail)
IMAP_HOST=
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_USERNAME=
IMAP_PASSWORD=

# Google Analytics
# Archivo: storage/app/analytics/service-account-credentials.json

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error

# Flare (errores producción — opcional)
FLARE_KEY=

# Telescope/Pulse
TELESCOPE_ENABLED=false
PULSE_ENABLED=false
```

**Fix**: Crear `/Users/developerts/Herd/training/.env.example` con el contenido anterior.

---

## 4. ENTORNO Y TESTS

### 4.1 .env.testing — EXISTE
- Ubicación correcta: `/Users/developerts/Herd/training/.env.testing`
- Configuración:
  - `CACHE_DRIVER=array` — correcto
  - `QUEUE_CONNECTION=sync` — correcto
  - `SESSION_DRIVER=array` — correcto
  - `MAIL_MAILER=array` — correcto
  - `DB_DATABASE=training_test` — base de datos separada
  - `PULSE_ENABLED=false`, `TELESCOPE_ENABLED=false` — correcto
- **Estado: OK** — el riesgo de `migrate:fresh` destruyendo la BD real está mitigado.

#### ADVERTENCIA: .env.testing tiene contraseña real
```
DB_PASSWORD=6cWRY1PUmiwYciQxJXkg
```
- Si `.env.testing` es commiteado (no aparece en .gitignore), la contraseña queda expuesta.
- **Fix**: Agregar `.env.testing` a `.gitignore` o usar contraseña distinta en BD de test.

### 4.2 phpunit.xml
- Archivo presente. No leído en detalle pero el stack test (PHPUnit 11) está en composer.json.

### 4.3 Colas en producción
- `QUEUE_CONNECTION` en `.env.testing` es `sync`.
- El `.env` real tiene `QUEUE_CONNECTION` (no verificado en esta auditoría).
- **Recomendación**: Documentar que producción debe usar `redis` y gestionar workers via Supervisor.
- Sin Horizon instalado, la monitorización de colas en producción es ciega.

---

## 5. CI/CD Y HERRAMIENTAS DE DESARROLLO

### 5.1 GitHub Actions — FALTANTE
- **No existe directorio `.github/workflows/`**.
- No hay pipeline de CI/CD automatizado.
- Consecuencias: sin tests automáticos en PR, sin lint, sin deploy automatizado.

### 5.2 Pre-commit hooks — FALTANTE
- No existe `.husky/` ni configuración de pre-commit.
- Sin hooks, nada impide commitear código con errores de estilo o tests fallando.

### 5.3 Laravel Pint — FALTANTE CONFIGURACIÓN
- `laravel/pint ^1.0` está en require-dev (correcto).
- **No existe `pint.json` ni `.pint.json`** en la raíz.
- Sin configuración explícita, Pint usa los defaults de Laravel (PSR-12 + Laravel ruleset).
- **Recomendación**: Crear `pint.json` mínimo para hacer el ruleset explícito y estable entre versiones:

```json
{
    "preset": "laravel"
}
```

### 5.4 Git hygiene — CRÍTICO
- Solo **2 commits** en el historial con decenas de archivos modificados.
- El `git status` muestra ~100+ archivos sin commitear (`.claude/`, `app/`, `modules/` etc.).
- Riesgo: un solo `git reset --hard` o conflicto de merge puede borrar semanas de trabajo.
- **Fix urgente**: Hacer commits frecuentes por feature/módulo. Considerar conventional commits.

### 5.5 .gitignore — evaluación
- `.env` está en `.gitignore` — OK.
- `vendor/` está en `.gitignore` — OK.
- `node_modules/` está en `.gitignore` — OK.
- `/public/build` está en `.gitignore` — OK.
- `.env.testing` **NO** está en `.gitignore` — RIESGO (ver sección 4.1).
- `storage/app/analytics/` **NO** está explícitamente en `.gitignore` — RIESGO (ver sección 3.1).

---

## 6. LOGGING Y MONITORIZACIÓN

### 6.1 Logging actual
- `config/logging.php`: driver `daily`, retención 14 días.
- Sin canal de alertas (Slack, email) configurado.
- `LOG_LEVEL` por defecto `debug` — en producción debería ser `error` o `warning`.

### 6.2 Error tracking en producción — FALTANTE
- `spatie/flare-client-php` está como dependencia transitiva (via ignition) pero no se usa directamente.
- **No hay Sentry, Bugsnag ni Flare configurado** para alertas de errores en producción.
- Ignition (`spatie/laravel-ignition`) es solo para desarrollo; no debe estar activo en producción.
- **Recomendación**: Agregar `FLARE_KEY` o integrar Sentry para producción:
  - Opción A: `composer require spatie/laravel-ignition` ya está — solo configurar `FLARE_KEY` en .env de producción.
  - Opción B: `composer require sentry/sentry-laravel` para stack más robusto.

### 6.3 Telescope/Pulse/Horizon
- Ninguno de los tres está instalado en composer.json.
- Las reglas del proyecto mencionan rutas `/horizon`, `/pulse`, `/telescope` — estas no existen.
- **Para producción mínima**: instalar al menos Horizon para monitorizar la cola Redis.

---

## 7. RESUMEN DE HALLAZGOS POR PRIORIDAD

### PRIORIDAD 1 — Bloquean funcionamiento correcto

1. **`spatie/laravel-permission` faltante** — toda la autorización RBAC está rota si vendor regenerado limpio.
2. **`.env.example` no existe** — imposible onboarding ni documentación de variables requeridas.
3. **Solo 2 commits con ~100 archivos sin versionar** — riesgo crítico de pérdida de trabajo.

### PRIORIDAD 2 — Riesgos de seguridad y producción

4. **`.env.testing` no está en `.gitignore`** — contraseña de BD expuesta si se commitea.
5. **`storage/app/analytics/` sin protección en .gitignore** — credenciales Google Analytics pueden filtrarse.
6. **`MAILJET_APIKEY` + `IMAP_PASSWORD` en `.env` real** — verificar que .env nunca sea commiteado (actualmente protegido por .gitignore, pero sin .env.example el riesgo es mayor).
7. **Sin error tracking en producción** — errores silenciosos, sin alertas.

### PRIORIDAD 3 — DevOps y mantenibilidad

8. **Sin CI/CD** — cero automatización de tests, lint ni deploy.
9. **Sin pre-commit hooks** — calidad de código depende de disciplina manual.
10. **Horizon no instalado** — colas Redis sin monitorización.
11. **`pint.json` faltante** — ruleset de linting no explícito.
12. **`herd: ^1.0.0` en package.json dependencies** — dependencia npm fantasma, eliminar.
13. **`nwidart/laravel-modules` faltante** — las reglas del proyecto lo asumen pero no está instalado (proyecto puede haber abandonado módulos — verificar).

### PRIORIDAD 4 — Deuda técnica menor

14. **`artesaos/seotools: "*"`** — constraint sin fijar, cambiar a `"^1.4"`.
15. **`maatwebsite/excel 3.1.x`** — evaluar migración a v4 por PHP 8.4 y soporte.
16. **`LOG_LEVEL=debug`** — cambiar a `error` en producción.
17. **jQuery no gestionado por npm** — assets estáticos, difícil actualizar.
18. **Vite 5.x** — Vite 6 disponible; actualizar en próximo ciclo.

---

## 8. ACCIONES CONCRETAS RECOMENDADAS

```bash
# 1. Instalar paquete crítico faltante
composer require spatie/laravel-permission:^6.0

# 2. Crear .env.example (ver sección 3.3 para contenido completo)

# 3. Actualizar .gitignore
echo ".env.testing" >> .gitignore
echo "storage/app/analytics/" >> .gitignore

# 4. Fijar constraint seotools
# En composer.json: "artesaos/seotools": "^1.4"
composer update artesaos/seotools

# 5. Crear pint.json
echo '{"preset":"laravel"}' > pint.json

# 6. Eliminar dependencia fantasma npm
# En package.json: eliminar "herd": "^1.0.0" de dependencies

# 7. Instalar Horizon para monitorización de colas
composer require laravel/horizon
php artisan horizon:install

# 8. Configurar error tracking producción
# Opción rápida: agregar FLARE_KEY al .env de producción
# Opción robusta: composer require sentry/sentry-laravel

# 9. Hacer commits por feature — urgente
git add -p  # staged interactivo, no git add -A

# 10. Crear GitHub Actions CI mínimo (.github/workflows/ci.yml)
```

---

*Auditoría generada automáticamente — verificar manualmente antes de ejecutar cambios en producción.*
