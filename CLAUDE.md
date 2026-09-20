# Proyecto Manager — Entorno Herd (macOS nativo)

## Entorno de desarrollo

Este proyecto corre con **Laravel Herd nativo en macOS**. No hay Docker. Los comandos PHP, Artisan, Composer y Pint se ejecutan directamente en el host.

## Comandos esenciales

```bash
# Artisan
php artisan <comando>

# Composer
composer <comando>

# Formatear codigo (PHP CS Fixer via Pint)
vendor/bin/pint
vendor/bin/pint --dirty   # Solo archivos con cambios

# Tests
php artisan test
vendor/bin/phpunit
```

## Comandos frecuentes de Laravel

```bash
# Limpiar cache
php artisan optimize:clear

# Migraciones
php artisan migrate
php artisan migrate:status

# Rutas
php artisan route:list

# Tinker
php artisan tinker
```

## Arquitectura: monolito por dominios

No existe `modules/`. El codigo vive en `app/` organizado por dominios:

```
app/Http/Controllers/
├── Accountings/    # Contabilidad e invoices
├── Auth/           # Autenticacion y validacion
├── Customers/      # Portal del cliente
├── Distributors/   # Portal del distribuidor
├── Enterprises/    # Portal de empresas
├── Managers/       # Panel de administracion
├── Pages/          # Frontend publico (carrito, etc.)
└── Supports/       # Soporte y atencion
```

Namespaces reales:
- Controladores: `App\Http\Controllers\{Domain}\`
- Modelos: `App\Models\`
- Form Requests: `App\Http\Requests\{Domain}\`
- Services: `App\Services\`
- Jobs: `App\Jobs\`
- Notifications: `App\Notifications\`
- Events/Listeners: `App\Events\` / `App\Listeners\`
- Policies: `App\Policies\`

## Archivos de rutas por dominio

```
routes/
├── web.php          # Frontend publico (pages)
├── api.php          # API REST (Sanctum)
├── managers.php     # Panel manager
├── customers.php    # Portal cliente
├── distributors.php # Portal distribuidor
├── enterprises.php  # Portal empresa
├── supports.php     # Panel soporte
├── accountings.php  # Panel contabilidad
└── console.php      # Comandos de consola / scheduler
```

El grupo base de cada dominio protegido:
```php
Route::group(['prefix' => 'panel', 'middleware' => ['auth', 'manager']], function () {
    // rutas del dominio managers
});
```

Middleware disponibles por rol: `manager`, `customer`, `distributor`, `enterprise`, `support`, `accountings`.

## Sistema de roles y permisos

### Rol actual (columna `role` en `users`)

Valores: `manager`, `customer`, `support`, `distributor`, `enterprise`, `accounting`.

Middleware de verificacion directa: `IsManager`, `IsCustomer`, `IsDistributor`, `IsEnterprise`, `IsAccountings`, `IsSupport` — verifican `Auth::user()->role === '{rol}'`.

### Adopcion gradual de Spatie Permission

`spatie/laravel-permission` se instala en coexistencia con la columna `role`. Estrategia:
- Los permisos Spatie (`{alias}.action`) se crean y sincronizan con el observer de rol.
- La columna `role` se mantiene durante la transicion.
- La autorizacion se migra dominio a dominio hacia Policies + `$user->can('{alias}.action')`.
- Mientras no se migre un dominio, el middleware de rol sigue siendo el guardián.

Convencion de permisos: `{alias}.action` — por ejemplo `orders.view`, `users.create`, `invoices.delete`.

## Base de datos

- **MySQL/MariaDB**: host `127.0.0.1`, puerto `3306`, DB `managerchat` (gestionado por Herd)
- **Oracle**: host `192.168.253.8`, puerto `1521`, servicio `GESTCENT` (segunda conexion de solo lectura)

## Notas

- Los cambios de archivo son inmediatos para requests HTTP normales — **pero SÍ
  hay un worker de colas de larga duración** (`com.training.queue-worker`, un
  LaunchAgent de macOS con `KeepAlive`, ver
  `~/Library/LaunchAgents/com.training.queue-worker.plist`). Ese proceso carga
  todo el código PHP (Mailables, Listeners, `app/helpers.php`, etc.) **una sola
  vez al arrancar** y lo mantiene en memoria: si editas algo que corre dentro de
  un job encolado (cualquier `Mailable implements ShouldQueue`, un Listener, una
  función global que ellos llamen) y lo pruebas con `Mail::to(...)->send(...)`
  o disparando el flujo real, verás el resultado **viejo** hasta reiniciar ese
  proceso — no es cache de Redis ni de la app, es memoria del propio worker.
  Detectado dos veces en la práctica: un cambio a `humanize_date()` y otro al
  layout de `MailerLayout` (este último además tenía su propio cache de 1h sin
  invalidar, ver `MailerLayout::booted()`). Reiniciar con:
  ```bash
  launchctl unload ~/Library/LaunchAgents/com.training.queue-worker.plist
  launchctl load ~/Library/LaunchAgents/com.training.queue-worker.plist
  ```
  Nota: `Mail::to($x)->send($mailable)` con un Mailable `ShouldQueue` en
  realidad SÍ lo encola (Laravel enruta `send()` a `queue()` para esas clases) —
  aunque el nombre del método sugiera lo contrario, pasa por este mismo worker.
- Para el queue worker en desarrollo, **siempre con la lista de colas**:
  ```bash
  php artisan queue:work redis --queue=$(php artisan queue:app-queues)
  ```
  Un `queue:work` sin `--queue` solo consume la cola `default`, y todo lo que va a
  `emails`, `mails`, `newsletter` o `seo` se queda pendiente para siempre **sin
  fallar ni aparecer en `failed_jobs`**. Las colas se declaran en
  `config/queue.php` → `app_queues`; `QueueNamesAreCoveredTest` rompe el CI si se
  añade una cola al código y no a esa lista. El LaunchAgent de arriba ya trae
  `--queue=default,emails,mails,newsletter,seo` (corregido en sep-2026; antes
  corría sin `--queue`, así que nunca procesaba `emails` — el correo de "olvidé
  mi contraseña" nunca salía y no daba ningún error visible).
- Variables de entorno en `.env` (raiz del proyecto).
- NUNCA ejecutar `migrate:fresh` — destruye todos los datos.
- NUNCA usar `config:cache`, `route:cache` ni `view:cache` en desarrollo (dificulta debug).
