---
globs: "routes/**/*.php"
---

# Route Files Rules

## Archivos de rutas por dominio

```
routes/
├── web.php          # Frontend publico (pages, sitemap)
├── api.php          # API REST (Sanctum)
├── managers.php     # Panel manager (prefix: panel, middleware: auth+manager)
├── customers.php    # Portal cliente (middleware: auth+customer)
├── distributors.php # Portal distribuidor (middleware: auth+distributor)
├── enterprises.php  # Portal empresa (middleware: auth+enterprise)
├── supports.php     # Panel soporte (middleware: auth+support)
├── accountings.php  # Panel contabilidad (middleware: auth+accountings)
└── console.php      # Comandos de consola / scheduler
```

## Patron de grupo por dominio (estandar real del proyecto)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Managers\{Domain}\{Entity}Controller;

Route::group(['prefix' => 'panel', 'middleware' => ['auth', 'manager']], function () {

    Route::group(['prefix' => '{domain}'], function () {
        Route::get('/', [{Entity}Controller::class, 'index'])->name('manager.{domain}.index');
        Route::get('/create', [{Entity}Controller::class, 'create'])->name('manager.{domain}.create');
        Route::post('/', [{Entity}Controller::class, 'store'])->name('manager.{domain}.store');
        Route::get('/{id}/edit', [{Entity}Controller::class, 'edit'])->name('manager.{domain}.edit');
        Route::put('/{id}', [{Entity}Controller::class, 'update'])->name('manager.{domain}.update');
        Route::delete('/{id}', [{Entity}Controller::class, 'destroy'])->name('manager.{domain}.destroy');
        Route::post('/bulk-action', [{Entity}Controller::class, 'bulkAction'])->name('manager.{domain}.bulk-action');
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/{section}', [SettingsController::class, 'index'])->name('manager.settings.index');
        Route::patch('/{section}', [SettingsController::class, 'update'])->name('manager.settings.update');
    });
});
```

Middleware por rol:
| Dominio       | Middleware           | Prefijo   |
|---------------|----------------------|-----------|
| managers      | `auth`, `manager`    | `panel`   |
| customers     | `auth`, `customer`   | `customer`|
| distributors  | `auth`, `distributor`| `distributor`|
| enterprises   | `auth`, `enterprise` | `enterprise`|
| supports      | `auth`, `support`    | `support` |
| accountings   | `auth`, `accountings`| `panel`   |

## Patron `routes/api.php` (REST API)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{Entity}ApiController;

// Publicas con throttle
Route::middleware(['api', 'throttle:60,1'])
    ->prefix('{alias}/public')
    ->name('api.{alias}.public.')
    ->group(function () {
        Route::post('/submit', [PublicController::class, 'submit'])->name('submit');
    });

// Autenticadas con Sanctum
Route::middleware(['api', 'auth:sanctum'])
    ->prefix('{alias}')
    ->name('api.{alias}.')
    ->group(function () {
        Route::apiResource('{entity-kebab}', {Entity}ApiController::class);
    });
```

## Patron de rutas publicas (sin auth, con throttle)

```php
Route::middleware(['web', 'throttle:30,1'])
    ->prefix('ruta-publica')
    ->name('public.{alias}.')
    ->group(function () {
        Route::get('/', [PublicController::class, 'form'])->name('form');
        Route::post('/', [PublicController::class, 'submit'])->name('submit')->middleware('throttle:10,1');
    });
```

## Reglas criticas

- **Namespaces**: `App\Http\Controllers\{Domain}\` (NUNCA `Modules\`)
- **Prefix panel**: `panel` para managers y accountings
- **Prefix API**: `api/{alias}` con `auth:sanctum`
- **Name convention**: `{rol}.{domain}.{action}` (ej: `manager.users.index`, `customer.orders.show`)
- **Middleware roles**: `manager`, `customer`, `distributor`, `enterprise`, `support`, `accountings`
- **Middleware public**: `['web', 'throttle:30,1']` (rate limit obligatorio)
- **Middleware API publica**: `['api', 'throttle:60,1']`
- **Middleware API privada**: `['api', 'auth:sanctum']`

## HTTP methods correctos

| Accion                        | Method   | Sufijo ruta |
|-------------------------------|----------|-------------|
| Listar                        | `GET`    | `index`     |
| Formulario crear              | `GET`    | `create`    |
| Guardar nuevo                 | `POST`   | `store`     |
| Ver detalle                   | `GET`    | `show`      |
| Formulario editar             | `GET`    | `edit`      |
| Actualizar                    | `PUT`    | `update`    |
| Actualizar parcial (settings) | `PATCH`  | `update`    |
| Eliminar                      | `DELETE` | `destroy`   |
| Bulk action                   | `POST`   | `bulk-action`|
| Exportar                      | `GET`    | `export`    |

## Nested resources pattern

```php
Route::group(['prefix' => '{parent}/{parent_id}/{child}'], function () {
    Route::get('/', [ChildController::class, 'index'])->name('manager.{parent}.{child}.index');
    Route::post('/', [ChildController::class, 'store'])->name('manager.{parent}.{child}.store');
    Route::put('/{id}', [ChildController::class, 'update'])->name('manager.{parent}.{child}.update');
});
```

## Bulk action route

SIEMPRE incluir ruta `bulk-action` en listados:
```php
Route::post('/bulk-action', [Controller::class, 'bulkAction'])->name('manager.{domain}.bulk-action');
```

Espera JSON payload:
```json
{ "action": "delete|activate|...", "ids": [1, 2, 3] }
```

## NO usar

- `Route::resource()` para rutas web (usa rutas explicitas)
- `Route::apiResource()` solo en api.php
- Closures en routes (siempre usar controller)
- Rutas fuera de grupos (siempre con middleware explicito)
- Namespaces `Modules\` (este proyecto es monolito en `App\`)

## Ver tambien

- [rules/controllers.md] para patron de controller
- [rules/api-controllers.md] para API controllers
- [rules/laravel-cache-commands.md] `php artisan route:clear` despues de cambios
