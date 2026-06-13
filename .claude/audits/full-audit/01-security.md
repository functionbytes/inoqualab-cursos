# Auditoría de Seguridad — Training (Laravel 12, LMS + e-commerce)

> Fecha: 2026-06-13 · Alcance: monolito `app/` (NO modular) · Método: lectura/grep estático (sin ejecutar artisan, entorno Herd).

## Resumen de señales (conteos reales)

| Señal | Valor | Implicación |
|---|---|---|
| `$this->authorize()` / Gate / `->can()` en 213 controllers | **0** | Sin autorización granular |
| Policies definidas (`app/Policies`) | **0** | No hay capa de ownership |
| `spatie/laravel-permission` instalado | **NO** | Las reglas `.claude/rules/` lo asumen — ficción |
| Modelos con `$guarded = []` | 0 (✅ usan `$fillable`, 105) | Mass assignment controlado |
| `whereRaw` / `DB::raw` / `selectRaw` | 69 | Revisar interpolación |
| Validación inline (`$request->validate`) | 73 | Debe migrar a FormRequest (solo 20 existen) |
| `$request->all()` | 2 | Bajo riesgo |
| Secrets hardcodeados (sk_/pk_/bearer) | 0 detectados | ✅ usan `env()`/`setting()` |
| Usos de `auth()->id()` en controllers | 17 | Ownership casi inexistente → IDOR |

## CRÍTICO

### C1 — Cero autorización granular: solo control por rol, sin permisos ni ownership
La autenticación existe (rutas envueltas en `['auth','manager']`, `['auth','customer']`, etc. con middleware `IsManager/IsCustomer/IsDistributor/IsEnterprise` en `bootstrap/app.php:71`). **Pero dentro de cada rol no hay ninguna verificación de permiso ni de propiedad.** Cualquier `manager` puede ejecutar las 606 rutas de `routes/managers.php`; cualquier `customer` puede actuar sobre las 35 rutas de `routes/customers.php` sin que se valide que el recurso le pertenece.

- **0** llamadas a `$this->authorize()` en 213 controllers.
- **0** Policies.
- Solo **17** usos de `auth()->id()`/`auth()->user()` y **2** filtros `where('user_id', auth())` en todo el árbol de controllers.

**Impacto:** IDOR (Insecure Direct Object Reference). Un customer autenticado puede acceder/modificar órdenes, facturas, matrículas o certificados de otro customer cambiando el `id` en la URL. En Accountings (facturación) y Customers (datos personales/pagos) esto es exposición de datos sensibles.

**Fix:** instalar `spatie/laravel-permission`, crear Policies por entidad sensible (Order, Invoice, Inscription, Certificate, Course) con check de ownership (`$entity->user_id === $user->id`) y aplicar `$this->authorize()` en cada acción `show/edit/update/destroy`. Ver `.claude/rules/policies.md` (ya describe el patrón esperado).

### C2 — `spatie/laravel-permission` no instalado pero referenciado en código y reglas
`composer.json` incluye otros paquetes Spatie (activitylog, medialibrary, analytics, pdf, ignition) pero **NO** `laravel-permission`. Sin embargo:
- `.claude/rules/policies.md`, `form-requests.md`, `seeders.md` asumen `$user->can('{alias}.action')` y `Permission::firstOrCreate()` en todas partes.
- `app/Models/User.php:157` llama `$user->hasRole('student')` — sin el paquete, este método no existe y **lanza error en runtime** (o depende de una implementación custom no verificada).

**Fix:** decidir y unificar — o se instala `spatie/laravel-permission` y se implementa RBAC real, o se eliminan las referencias y se documenta el modelo de roles real (middleware custom). La divergencia actual genera código roto y reglas IA que producen namespaces/llamadas inválidas.

## ALTO

### A1 — 73 validaciones inline sin FormRequest
73 controllers validan con `$request->validate(...)` inline (solo existen 20 FormRequest). Riesgo: validación inconsistente, falta de `authorize()` en el punto de validación, mensajes duplicados. Concentrar en los flujos de dinero (CheckoutController, Orders, Invoices).

### A2 — 69 usos de SQL crudo (`whereRaw`/`DB::raw`/`selectRaw`)
No todos son vulnerables, pero cada uno que interpole `$request`/variables de usuario es inyección SQL potencial. **Acción:** auditar los 69 manualmente; priorizar los que reciban input de búsqueda/filtros (AnalyticsController 565L, listados con filtros).

### A3 — `laravel-ignition` activo: riesgo de stack traces en producción
`spatie/laravel-ignition` está instalado (páginas de error de debug). Si `APP_DEBUG=true` en producción, expone rutas, queries y entorno. **Fix:** garantizar `APP_DEBUG=false` en prod (y verlo en `.env.example`, que no existe — ver informe 06).

### A4 — `.env.testing` con credenciales reales versionable
(Cruce con informe 06-devops) `.env.testing` contiene contraseñas reales y no está garantizado en `.gitignore`. Riesgo de filtrado de secrets al repo.

## MEDIO

- **M1 — CheckoutController 726L mezcla validación, lógica de pago y webhook.** Difícil de auditar; superficie de ataque concentrada. Extraer a `WompiService`/`CheckoutService`.
- **M2 — File uploads:** verificar reglas `mimes`/`max` en subidas (medialibrary ayuda, pero los FormRequest deben restringir tipo/tamaño explícitamente). Revisar uploads de documentos en Supports/Documents.
- **M3 — Rate limiting parcial:** solo `payments.response` y webhook tienen throttle visible; endpoints de login/registro/reset deben tener throttle explícito contra fuerza bruta.

## POSITIVO (no romper)

- ✅ **Webhook Wompi verifica firma** correctamente: `WompiService::verifyWebhookSignature()` usa `hash_equals()` (constant-time) sobre `transactionId+status+amount+currency+checksum+eventsSecret` (SHA256). Responde 401 ante firma inválida (`CheckoutController.php:275`).
- ✅ **Mass assignment controlado:** 105 modelos con `$fillable` explícito, 0 con `$guarded=[]`.
- ✅ **Sin secrets hardcodeados** detectados; uso de `env()`/`setting()`.
- ✅ **Separación por rol** con middleware dedicado por dominio.

## Orden de corrección recomendado

1. **C1 + C2** — instalar permission, Policies + ownership en entidades sensibles (Order, Invoice, Inscription, Certificate). *Máxima prioridad: previene exposición de datos de clientes.*
2. **A2** — auditar los 69 SQL crudos en busca de inyección.
3. **A1** — migrar las 73 validaciones inline (empezando por CheckoutController/Orders/Invoices) a FormRequest.
4. **A3 + A4** — `.env.example`, garantizar `APP_DEBUG=false` prod, `.env.testing` fuera del repo.
