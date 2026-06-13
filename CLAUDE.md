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

- Los cambios de archivo son inmediatos; no hay proceso worker de larga duracion que reiniciar.
- Para el queue worker en desarrollo: `php artisan queue:work` en una terminal separada.
- Variables de entorno en `.env` (raiz del proyecto).
- NUNCA ejecutar `migrate:fresh` — destruye todos los datos.
- NUNCA usar `config:cache`, `route:cache` ni `view:cache` en desarrollo (dificulta debug).
