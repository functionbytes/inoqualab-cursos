# Plan Maestro V2 — Estado actual (post-mejoras de auditoría)

> Fecha: 2026-06-23 · Reconciliación de las 6 dimensiones contra la auditoría del 13-jun (`.claude/audits/full-audit/`) + evidencia concreta del estado de hoy. Working dir: `/Users/developerts/Herd/training`.
> Honestidad ante todo: varias cosas de la audit vieja YA están resueltas. Este plan distingue lo hecho de lo pendiente con evidencia file:line.

## Qué cambió desde el 13-jun

| Dimensión / hallazgo del 13-jun | Estado anterior | Estado hoy | Evidencia |
|---|---|---|---|
| `spatie/laravel-permission` instalado | NO (ficción en reglas) | **Resuelto** (^8.0) | `composer.json:25` |
| Modelo de autorización del panel | 0 control granular | **Resuelto (patrón nuevo)**: convención por middleware | `app/Http/Middleware/EnforcePanelPermission.php` |
| IDOR cross-tenant portales (distributor/enterprise/accounting/support) | 39 hallazgos abiertos | **Resuelto/Parcial**: 18+21 corregidos y verificados en navegador | `SECURITY_AUDIT_PORTALS_V2.md` |
| Policies por entidad sensible | 0 Policies | **Parcial**: 7 Policies creadas… pero NO invocadas | `app/Policies/` (7), `authorize()` en controllers = **0** |
| `.env.example` | Ausente | **Resuelto** | `.env.example` (6.7 KB) |
| `.gitignore`: `.env.testing` + analytics | Sin proteger | **Resuelto** | `.gitignore:29-30` |
| `pint.json` | Ausente | **Resuelto** | `pint.json` |
| CI (GitHub Actions) | Ausente | **Resuelto** | `.github/workflows/ci.yml` |
| `herd` fantasma en package.json | Presente | **Resuelto** | grep `"herd"` = 0 |
| `artesaos/seotools` constraint `*` | Sin fijar | **Resuelto** (^1.4) | `composer.json:9` |
| Tabler icons en admin | 190 en 70 archivos | **Resuelto** | grep `ti ti-` en admin = **0 archivos** |
| Tests | ~9 | **Parcial**: 28 archivos de test (incl. regresión quiz/exam, auth, authorization) | `find tests -name *Test.php` = 28 |
| `Mail::to()->send()` síncrono | 6 | **Resuelto/Parcial** en controllers | grep en controllers = 0 |
| Commits granulares | 2 commits | **Resuelto** | `git rev-list --count` = 26 |
| Validación inline → FormRequest | 72 | **Parcial**: bajó a 47 | grep `$request->validate(` = 47 |
| Inline styles | 846 ocurr. / 147 arch. | **Parcial**: 150 archivos (sin gran avance) | grep = 150 archivos |
| `{!!` sin escape | 506/221 | **Pendiente**: 221 archivos | grep = 221 archivos |
| `<img>` sin alt | 61 | **Parcial**: ~49 | grep = 49 |
| SQLite in-memory en phpunit | Comentado | **Pendiente** (sigue comentado) | `phpunit.xml:24-25` |
| Factories | 5/108 | **Parcial**: 12 | `database/factories/` = 12 |
| SQL raw a auditar | 69 | **Pendiente** (73 ahora, parsers nuevos) | grep = 73 |

## Diagnóstico en una página

| Dimensión | Salud | Hallazgo dominante REAL hoy |
|---|---|---|
| Seguridad / Autorización panel | 🟢 | Patrón nuevo `EnforcePanelPermission` cubre RBAC por convención; IDOR cross-tenant de portales corregido y verificado. |
| Seguridad / Ownership entidades | 🟠 | **Brecha clave**: 7 Policies con ownership existen pero **0 `authorize()` en controllers** → las Policies son código muerto. Customers/ (datos propios) depende de filtros ad-hoc (8/11 controllers usan `auth()->id()`), no de Policy. |
| Seguridad / inyección | 🟡 | 73 `whereRaw/DB::raw/selectRaw` sin auditar 1×1 (alguno nuevo de parsers IncomingMail). |
| Testing | 🟠 | De 9→28 tests (gran avance: regresión quiz/exam, auth, authorization), pero **SQLite in-memory sigue comentado** (`phpunit.xml:24-25`) → suite lenta contra MySQL; 12/109 factories. |
| Rendimiento | 🟡 | Emails ya no son síncronos en controllers; **índices y N+1 siguen pendientes** (63 migraciones sin índice; sin debugbar pass). |
| Calidad / arquitectura | 🟡 | Fat controllers y duplicación 3× intactos; validación inline 72→47 (parcial). |
| Frontend | 🟡 | Tabler→FA6 **resuelto**; quedan 221 `{!!`, 150 archivos con inline style, 49 `<img>` sin alt, includes de nav duplicados por rol. |
| DevOps / deps | 🟢 | `.env.example`, `.gitignore`, `pint.json`, CI, seotools, herd-fantasma: todo resuelto. Falta observabilidad (Horizon/Sentry). |
| Docs vs realidad | 🟡 | `CLAUDE.md` actual ya describe monolito+Herd correctamente; persisten reglas con lenguaje "modules/" en `.claude/rules/` (form-requests, routes, blade-views, cache-commands). |

**Lo que SÍ está bien (no romper):** webhook Wompi con `hash_equals`; `EnforcePanelPermission` (patrón limpio, con `DOMAIN_ALIASES` y `ACTION_MAP`); fixes IDOR de portales verificados en navegador (propio 200 / ajeno 404); 105 modelos con `$fillable`; CI + pint.json en marcha.

## Quick wins seguros (hacer ya)

Solo ítems aislados, sin tocar lógica de negocio ni controllers críticos. Aplicables sin riesgo de romper la app.

1. **Borrar comentarios `dd()` muertos** (cosmético, evita confusión y futuros leaks). Esfuerzo S.
   - `app/Http/Controllers/Supports/Users/InscriptionsController.php:33`
   - `app/Http/Controllers/Supports/Users/ManagementController.php:357`
   - `app/Http/Controllers/Managers/MigrationController.php:184`
2. **Habilitar SQLite in-memory en tests** — descomentar 2 líneas. Acelera la suite ~10× y elimina riesgo sobre MySQL real. Esfuerzo S.
   - `phpunit.xml:24-25` (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`). *Nota: validar que las 73 SQL raw/funciones MySQL-only no rompan en SQLite; si rompen, dejar como mejora M y mantener `.env.testing` MySQL.*
3. **Añadir `alt` a `<img>`** — 49 ocurrencias, contenido estático. Esfuerzo S-M. Aislado por archivo (WCAG 2.1 A).
4. **`style="display:none"` → `d-none`** en los includes/nav (subconjunto seguro de los 150 archivos). Esfuerzo S por archivo. Evitar los `style="background-color:{{$dato}}"` (esos requieren approach distinto).
5. **Alinear lenguaje "modules/" en `.claude/rules/`** con la realidad monolito (`App\Http\Controllers\{Domain}`). Solo docs, no código. Esfuerzo M. Archivos: `.claude/rules/{form-requests,routes,blade-views,seeders,laravel-cache-commands}.md`.
6. **Migración solo-índices** (additive, sin `->change()`): índices en FKs `*_id`, `status/state`, `slug` unique, par `(course_id,user_id)` en inscriptions. Esfuerzo M. Seguro si la migración solo AÑADE índices y trae `down()`. Probar `migrate` + `migrate:rollback --step=1` (NUNCA `migrate:fresh`).

## Hoja de ruta por fases

Orden por dependencia: seguridad/ownership no diferible → tests como red → rendimiento → refactor → frontend → observabilidad. **Regla dura**: ningún refactor de controllers vía fan-out de agentes (ya rompió la app). Cambios de backend = a mano + prueba en navegador.

### Fase 0 — Blindaje y DevOps (mayormente HECHO)
- [x] Commits granulares (26 commits).
- [x] `.env.example` completo.
- [x] `.gitignore`: `.env.testing` + `/storage/app/analytics`.
- [x] `pint.json` (`{"preset":"laravel"}`).
- [x] Eliminar `herd` fantasma de package.json.
- [x] Fijar `artesaos/seotools: ^1.4`.
- [x] CI básico (`.github/workflows/ci.yml`).
- [x] Instalar `spatie/laravel-permission ^8`.
- [x] Decisión arquitectónica de autorización: convención por middleware (`EnforcePanelPermission`).
- [ ] Limpiar `dd()` comentados (quick win #1). Riesgo bajo, a mano.

### Fase 1 — Seguridad / Ownership (CRÍTICO, parcial — no diferible la brecha de Policies)
- [x] IDOR cross-tenant portales distributor/enterprise/accounting/support corregido y verificado (`SECURITY_AUDIT_PORTALS_V2.md`).
- [x] Escalada/toma de cuenta (reset password cross-user) corregida en Accountings/Distributors/Supports settings.
- [x] Throttle login/registro/reset (cubierto por tests `Auth/*`; verificar rutas).
- [ ] **Cablear las 7 Policies existentes** (`Order, Invoice, Inscription, Certificate, Course, User`): hoy `app/Policies/` tiene 7 clases con ownership pero **0 `authorize()` en controllers** → no se ejecutan. *Riesgo de implementar: ALTO* (toca show/edit/update/destroy de dominios con dinero/datos). Hacer **a mano, dominio por dominio, empezando por Customers/ y Accountings/**, con prueba en navegador (propio 200 / ajeno 403/404). NO fan-out.
- [ ] Confirmar que el patrón `EnforcePanelPermission` **no** cubre ownership intra-rol en Customers/ (panel cliente con datos propios). Hoy 8/11 controllers de Customers/ filtran por `auth()->id()`; los 3 restantes son sospechosos de IDOR — revisar a mano. *Riesgo: ALTO.*
- [ ] Vigilar fail-open: `EnforcePanelPermission` deja pasar si el permiso no existe en BD (`permissionExists()` falso → sin abort). Dominio nuevo debe sembrarse o aliasarse en `DOMAIN_ALIASES`. *Riesgo: MEDIO; mitigación = test que falle si una ruta de panel no tiene permiso sembrado.*
- [ ] Auditar las 73 `whereRaw/DB::raw/selectRaw` buscando interpolación de input (foco: parsers IncomingMail nuevos, AnalyticsController, filtros de listados). *Riesgo: MEDIO, a mano.*
- [ ] Migrar a FormRequest las 47 validaciones inline restantes, **empezando por flujos de dinero** (Checkout/Orders/Invoices). *Riesgo: MEDIO-ALTO en checkout; incremental + navegador.*

### Fase 2 — Red de tests (ALTO, parcial — habilita el refactor)
- [x] Regresión quiz/exam customer (`tests/Feature/Customers/QuizExamRegressionTest.php`).
- [x] Auth (registration, password reset) y Authorization (`EnforcePanelPermissionTest`, `AuthorizationTest`).
- [x] Suites manager (Coupons, Enterprises, Certifiers, Mailer, Newsletter, Seo, Analytics, smoke).
- [ ] **SQLite in-memory** (quick win #2) — `phpunit.xml:24-25`. Riesgo bajo (validar SQL raw).
- [ ] Idempotencia webhook Wompi (doble procesamiento, bundle multi-curso). *Riesgo: bajo (solo tests).*
- [ ] Factories faltantes para los modelos core que aún no las tienen (12/109 hoy): completar `Order, OrderItem, Coupon, Quiz/Exam, Certificate` si no están. *Riesgo: bajo.*

### Fase 3 — Rendimiento (ALTO, pendiente)
- [x] Emails fuera del request en controllers (0 `Mail::to` síncrono en controllers).
- [ ] Migración solo-índices additive (quick win #6). *Riesgo: bajo-medio si solo añade índices.*
- [ ] N+1: debugbar local + `with()`/`withCount()` en los 10 listados de mayor tráfico (cursos, orders, inscriptions). *Riesgo: medio; cambios localizados + navegador.*
- [ ] Cachear `setting()`/navegación por rol/catálogo si `setting()` pega a BD por llamada (verificar). *Riesgo: medio (invalidación).*

### Fase 4 — Refactor de arquitectura (MEDIO-ALTO, pendiente — NO antes de Fase 2)
- [ ] Extraer Services compartidos (`CourseEnrollmentService`, `UserManagementService`, `InvoiceService`) para reducir la duplicación 3× (CourseController 1.102L, UserController 999L). *Riesgo: ALTO. A mano, con red de tests, NO fan-out de agentes.*
- [ ] Adelgazar fat controllers (`IncomingMailsController` 843L, `CheckoutController` 726L, `AnalyticsController` 565L). *Riesgo: ALTO (checkout = dinero). Incremental + navegador.*
- [ ] Dead code: `app/Model/` vs `app/Html/` duplicados; verificar `app/Structure/`. *Riesgo: bajo-medio; confirmar 0 referencias antes de borrar.*

### Fase 5 — Frontend (MEDIO, parcial)
- [x] Tabler → FA6 (0 en admin).
- [ ] `<img>` sin alt (quick win #3).
- [ ] `style="display:none"` → `d-none` (quick win #4); reducir progresivamente los 150 archivos con inline style.
- [ ] Componentizar header/nav/notification (6 copias por rol → 1 parametrizado). *Riesgo: medio; tocar layouts compartidos requiere navegador en cada rol.*
- [ ] Sanitizar los 221 `{!!` con datos de usuario (HTMLPurifier en `description/short/learn/detail` de cursos/instrucciones). *Riesgo: medio; verificar que no rompa HTML legítimo de WYSIWYG.*

### Fase 6 — Observabilidad (continuo, pendiente)
- [ ] `laravel/horizon` para la cola Redis.
- [ ] Error tracking producción (`FLARE_KEY` o `sentry/sentry-laravel`).
- [ ] Pre-commit hooks (pint + test).

## Recomendación de secuencia

**(a) Quick wins seguros — aplicables ahora sin riesgo de romper la app** (todos aislados, sin lógica de negocio):
1. Borrar los 3 `dd()` comentados.
2. `<img>` alt (49) y `style="display:none"` → `d-none` en includes/nav.
3. Habilitar SQLite in-memory en `phpunit.xml:24-25` (validando SQL raw; si rompe, posponer).
4. Migración solo-índices additive (con `down()`, probada con `migrate` + `rollback --step=1`).
5. Alinear lenguaje "modules/" residual en `.claude/rules/` con el monolito real.

**(b) Backend incremental + prueba en navegador (NO fan-out de agentes):**
La prioridad nº1 real de hoy NO es "crear autorización" (ya existe el patrón de panel + Policies), sino **cerrar la brecha entre las 7 Policies y los controllers**: hoy `authorize()` = 0, por lo que las Policies de `Order/Invoice/Inscription/Certificate/Course/User` no se ejecutan. Atacar **Customers/ y Accountings/ primero** (datos propios + dinero), cableando `$this->authorize()` método por método y verificando en navegador (propio → 200, ajeno → 403/404), antes de tocar nada más. En paralelo, añadir un test que detecte rutas de panel sin permiso sembrado (mitiga el fail-open de `EnforcePanelPermission`). Solo con esa red (Fase 2 completada con SQLite) abordar el refactor de fat controllers y duplicación 3× — nunca antes, y siempre a mano + navegador, porque el refactor masivo autónomo ya rompió la app.
