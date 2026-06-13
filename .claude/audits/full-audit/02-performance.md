# Auditoría de Rendimiento — Training (Laravel 12, LMS + e-commerce)

> Fecha: 2026-06-13 · Alcance: `app/`, `database/migrations`, `resources/views` · Método: estático (sin ejecutar artisan/profiler, entorno Herd).

## Resumen de señales (conteos reales)

| Señal | Valor | Lectura |
|---|---|---|
| Métodos `index`/`list` en controllers | 168 | Superficie de listados |
| `->with(` (eager loading) en controllers | 530 | Buen uso general de eager loading |
| `->get()` en controllers | 261 | Muchos sin paginar |
| `->paginate(` en controllers | 119 | ~119 de ~168 listados paginan |
| `->all()` (instancia) | 15 | Cargas completas de tabla |
| `Cache::remember` / `rememberForever` | 23 | Caching presente pero limitado |
| Blades con `@foreach` | 276 | Superficie alta de N+1 en vistas |
| `Mail::to`/`->send()` síncrono en controllers | 6 | Envío bloqueante |
| Jobs definidos (`app/Jobs`) | 6 | Cola infrautilizada |
| Clases con `ShouldQueue` | 18 | — |
| Migraciones | 188 | — |
| Migraciones con índice/unique/foreign | 125 | 63 sin ningún índice |
| `foreignId` (con constrained) | 17 | FKs explícitas escasas |

## CRÍTICO / ALTO

### A1 — N+1 en vistas: 276 blades con `@foreach`, listados sin garantía de eager loading
El proyecto usa `->with()` 530 veces (bien), pero hay **261 `->get()`** y **168 listados**; no todos los listados que alimentan los 276 blades con bucles cargan sus relaciones. Cada `@foreach` que accede a `$model->relacion` sin `with()` previo dispara una query por fila.

**Puntos calientes a revisar primero** (alto tráfico / muchas filas):
- `Customers/CoursesController.php` (357L) y `Managers/Courses/CoursesController.php` (459L) — listados de cursos con thumbnail (medialibrary), categoría, instructor, conteo de lecciones.
- Listados de Orders / Invoices / Inscriptions (cada fila accede a customer, items, course).
- Navegación/menús que iteran categorías o departamentos por request.

**Fix patrón:**
```php
// ❌ N+1
$courses = Course::where('active', 1)->get();           // luego en blade: $course->category->name, $course->media...
// ✅
$courses = Course::query()
    ->with(['category:id,name', 'instructor:id,name', 'media'])
    ->withCount('lessons')
    ->where('active', 1)
    ->paginate(15);
```
**Acción concreta:** instalar `laravel-debugbar` en local y recorrer los 10 listados de mayor tráfico contando queries; añadir `with()`/`withCount()` donde el contador suba.

### A2 — 63 migraciones sin índice; FKs sin constraint
125 de 188 migraciones declaran algún índice; **63 no declaran ninguno**. Solo **17** columnas usan `foreignId(...)->constrained()` — el resto de claves foráneas son `unsignedBigInteger`/`integer` sueltas, frecuentemente **sin índice**, lo que penaliza todo JOIN y filtro por FK.

**Acción:** auditar columnas usadas en WHERE/JOIN/ORDER BY sin índice. Candidatas detectadas (40 columnas `status/slug/email/state` declaradas): añadir índices a:
- `*_id` (foreign keys) sin índice → índice simple.
- `status` / `state` (filtros de listados) → índice.
- `slug` (lookup público de cursos/páginas) → unique index.
- `email` (login/búsqueda) → unique/index.
- Pares frecuentes `(user_id, status)`, `(course_id, user_id)` en inscriptions → índice compuesto.

```php
$table->index('status');
$table->index(['course_id', 'user_id']);   // inscriptions
$table->unique('slug');
```

### A3 — Operaciones bloqueantes que deberían ir a cola
6 envíos de correo síncronos (`Mail::to(...)->send()`) en controllers, más generación de PDF (`spatie/laravel-pdf`) y optimización de imágenes (`spatie/laravel-image-optimizer`) potencialmente en request. Solo 6 Jobs definidos / 18 `ShouldQueue`.

**Impacto:** el usuario espera el envío SMTP / render PDF dentro del request HTTP (checkout, facturas, matrículas → emails de confirmación).
**Fix:** convertir mailables a `ShouldQueue`, mover exports (`maatwebsite/excel`) y generación de PDF/certificados a Jobs (`exports`, `emails`). Ver `.claude/rules/jobs.md`.

## MEDIO

### M1 — Caching limitado de datos estáticos
Solo 23 usos de `Cache::remember`. Datos que se recalculan en cada request y son buenos candidatos a caché:
- Navegación/menús por rol, categorías de cursos, departamentos, settings (`setting()` se llama en `WompiService` y probablemente en muchos sitios — verificar si `setting()` cachea o pega a BD cada vez).
- Catálogo público de cursos / home (alto tráfico, baja frecuencia de cambio).

**Fix:** `Cache::remember('nav.manager', 600, fn () => ...)` con invalidación en el `Observer`/`save` de la entidad. **Verificar urgentemente si `setting()` hace query por llamada** — sería un N+1 transversal.

### M2 — `->get()` sin paginar (261 vs 119 paginados)
Diferencia de ~142 `->get()` que no paginan. Muchos serán selects pequeños legítimos, pero los que alimentan tablas de administración deben paginar. Revisar especialmente los listados de Supports (200 rutas) y Distributors (87 rutas).

### M3 — `->all()` (15) sobre tablas potencialmente grandes
Cargar la tabla completa en memoria. Revisar cuáles operan sobre Course/Order/User/Inscription y acotar con `select()` + filtros + chunk/cursor.

### M4 — Controllers gigantes degradan también el rendimiento de mantenimiento
`IncomingMailsController` 843L, `CheckoutController` 726L, `AnalyticsController` 565L — concentran lógica + queries; AnalyticsController es candidato a queries pesadas con agregaciones (revisar `whereRaw`/`selectRaw` de los 69 detectados, varios probablemente aquí).

## POSITIVO

- ✅ Uso amplio de `->with()` (530) — el equipo conoce eager loading; el problema es cobertura desigual, no desconocimiento.
- ✅ 119 listados ya paginan.
- ✅ 23 puntos de caché y 18 `ShouldQueue` — la infraestructura existe, falta extenderla.

## Top 3 fixes de mayor impacto

1. **A1 — N+1 en los 10 listados de mayor tráfico** (cursos, orders, inscriptions): debugbar + `with()`/`withCount()`. Mayor ganancia percibida por el usuario.
2. **A2 — Índices en FKs y columnas de filtro** (status, slug, email, pares compuestos en inscriptions): ganancia transversal en todos los listados y JOINs.
3. **M1 — Cachear `setting()`/navegación/catálogo** y **A3 — colar emails/PDF/exports**: quita trabajo repetido y bloqueante del hot path.
