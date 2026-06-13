# Plan Maestro de Mejoras — Training (Laravel 12, LMS + e-commerce)

> Fecha: 2026-06-13 · Basado en auditoría de 6 dimensiones (seguridad, rendimiento, calidad, testing, frontend, devops).
> Informes detallados: `01-security.md` … `06-devops-deps.md` en esta misma carpeta.

## Diagnóstico en una página

**Qué es:** monolito Laravel 12 (NO modular, pese a lo que dice el CLAUDE.md), plataforma LMS + e-commerce con pagos (Wompi), organizado por dominios en `app/Http/Controllers/{Accountings,Customers,Distributors,Enterprises,Managers,Supports,Pages}`. 1511 PHP, 677 Blade, 108 modelos, 213 controllers, 188 migraciones.

**Estado de salud por dimensión:**

| Dimensión | Estado | Hallazgo dominante |
|---|---|---|
| 🔴 Seguridad / Autorización | **Crítico** | 0 `authorize()` y 0 Policies en 213 controllers → IDOR; `spatie/permission` no instalado pero asumido |
| 🔴 Testing | **Crítico** | ~3-4% cobertura (9 tests); flujos de dinero y LMS sin regresión; factories 5/108 |
| 🟠 Higiene de repo / secrets | **Alto** | solo 2 commits con ~100 archivos sin versionar; `.env.example` ausente; secrets versionable |
| 🟠 Rendimiento | **Alto** | N+1 en listados; 63 migraciones sin índice; emails/PDF síncronos |
| 🟡 Calidad / Arquitectura | **Medio-Alto** | fat controllers (843L); duplicación 3x (~3.000 líneas); 72 validaciones inline |
| 🟡 Frontend | **Medio** | 846 inline styles; 190 Tabler icons (prohibidos); 506 `{!!` sin sanitizar |
| 🟡 Docs vs realidad | **Medio (op. alto)** | CLAUDE.md/rules describen Docker + módulos inexistentes → IA genera código inválido |

**Lo que SÍ está bien (no romper):** webhook Wompi con firma `hash_equals`; 105/108 modelos con `$fillable`; sin secrets hardcodeados; separación por rol con middleware; eager loading usado 530 veces; `.env.testing` existe.

---

## Hoja de ruta por etapas

Orden por dependencias: cada etapa habilita la siguiente. Las etapas 0-2 son **no diferibles** (riesgo de seguridad/pérdida de datos). Las 3-6 son mejora incremental.

### 🚦 Fase 0 — Estabilización y blindaje *(rápido, máximo retorno/esfuerzo)*
**Objetivo:** detener la hemorragia de riesgo sin tocar lógica de negocio.
- [ ] Hacer commits por feature del trabajo actual (~100 archivos sin versionar). **Urgente: un `reset --hard` borra semanas.**
- [ ] Crear `.env.example` completo (plantilla en `06-devops-deps.md §3.3`).
- [ ] `.gitignore`: añadir `.env.testing` y `storage/app/analytics/`. Rotar la contraseña de `.env.testing` si ya se filtró.
- [ ] Garantizar `APP_DEBUG=false` y `LOG_LEVEL=error` en producción (Ignition no debe exponerse).
- [ ] Crear `pint.json` (`{"preset":"laravel"}`); correr `vendor/bin/pint`.
- [ ] Eliminar dependencia fantasma `herd: ^1.0.0` de `package.json`.
- [ ] **Decisión arquitectónica:** o se instala `spatie/laravel-permission` (RBAC real) o se formaliza el modelo de roles por middleware. De esto depende toda la Fase 1.
- [ ] **Alinear `CLAUDE.md` y `.claude/rules/`** con la realidad (monolito + Herd, namespace `App\Http\Controllers\{Domain}`, rutas reales). Sin esto, todo código generado con IA nace roto.

### 🔐 Fase 1 — Seguridad y autorización *(CRÍTICO, no diferible)*
**Objetivo:** cerrar IDOR y exposición de datos de clientes.
- [ ] Implementar la decisión de Fase 0 (permission o roles formalizados).
- [ ] Crear Policies con check de ownership para entidades sensibles: **Order, Invoice, Inscription, Certificate, Course, User**.
- [ ] Añadir `$this->authorize()` en `show/edit/update/destroy` de esos dominios, empezando por **Customers/ y Accountings/** (datos personales y dinero).
- [ ] Auditar los **69 usos de SQL crudo** (`whereRaw/DB::raw/selectRaw`) en busca de interpolación de input → binding parametrizado.
- [ ] Migrar las validaciones inline de los flujos de dinero (Checkout, Orders, Invoices) a FormRequest.
- [ ] Añadir throttle explícito a login/registro/reset de contraseña.

### ✅ Fase 2 — Red de seguridad de tests *(ALTO, habilita refactor seguro)*
**Objetivo:** poder refactorizar sin romper; cubrir lo que mueve dinero.
- [ ] Infra: descomentar SQLite in-memory en `phpunit.xml` (suite ~10x más rápida).
- [ ] Factories críticas: `Order, Inscription, Quiz, Exam, Coupon, Certificate` (hoy solo 5/108).
- [ ] **Regresión quiz/exam customer** — el último commit arregló 8+ bugs aquí sin ningún test. *Máxima prioridad.*
- [ ] Idempotencia del webhook Wompi (doble procesamiento, bundle multi-curso).
- [ ] Flujo HTTP de cupones (`CouponUsage`, límite de usos) y checkout.
- [ ] `AuthenticationTest`: customer no accede a rutas manager; estados inactive/unvalidated.
- [ ] CI básico: GitHub Actions que corra `pint --test` + `php artisan test` en cada PR.

### ⚡ Fase 3 — Rendimiento *(ALTO)*
**Objetivo:** quitar trabajo repetido/bloqueante del hot path.
- [ ] Migración de **índices**: FKs sin `constrained`, `status/slug/email/state` (40 columnas), pares compuestos en inscriptions (`course_id,user_id`). 63 migraciones sin ningún índice.
- [ ] N+1: instalar debugbar en local, recorrer los 10 listados de mayor tráfico (cursos, orders, inscriptions) y añadir `with()`/`withCount()`.
- [ ] Colar emails (`ShouldQueue`), generación de PDF/certificados y exports Excel → Jobs.
- [ ] Cachear `setting()` (verificar si pega a BD por llamada), navegación por rol y catálogo público.

### 🧱 Fase 4 — Refactor de arquitectura *(MEDIO-ALTO)*
**Objetivo:** reducir ~3.000 líneas duplicadas y adelgazar controllers.
- [ ] Extraer Services compartidos: `CourseEnrollmentService`, `UserManagementService`, `InvoiceService` para los 3 dominios (Managers/Supports/Distributors).
- [ ] Adelgazar fat controllers: `IncomingMailsController` (843L), `CheckoutController` (726L), `AnalyticsController` (565L).
- [ ] Eliminar dead code: `app/Model/` vs `app/Html/` (duplicados), verificar `app/Structure/`.
- [ ] Migrar `helpers.php` (36 funciones globales) a clases `Support/` testeables.
- [ ] Completar las 72→FormRequest restantes; return types en los 81 métodos de modelo; `casts()` en los 2 modelos old-style.

### 🎨 Fase 5 — Frontend y consistencia *(MEDIO)*
**Objetivo:** consistencia visual y cierre de XSS.
- [ ] Reemplazar **190 Tabler icons** por Font Awesome 6 (concentrados en ~18 archivos de `includes/` → arregla el 100%).
- [ ] Componentizar header/nav/notification (6 copias por rol → 1 parametrizado, ~80% menos duplicación).
- [ ] `style="display:none"` → `d-none`; reducir progresivamente los 846 inline styles.
- [ ] Sanitizar los `{!! ... !!}` con datos de usuario (HTMLPurifier en descripciones de cursos/instrucciones).
- [ ] Añadir `alt` a las 61 `<img>` sin texto alternativo.

### 📈 Fase 6 — Observabilidad y DevOps maduro *(continuo)*
**Objetivo:** operar con visibilidad.
- [ ] `laravel/horizon` para la cola Redis (hoy ciega).
- [ ] Error tracking en producción (configurar `FLARE_KEY` o `sentry/sentry-laravel`).
- [ ] CI/CD completo + pre-commit hooks.
- [ ] Fijar constraints: `artesaos/seotools: ^1.4`; evaluar `maatwebsite/excel ^4.0` y Vite 6.

---

## Secuenciación recomendada

```
Fase 0 ──> Fase 1 ──> Fase 2 ──┬─> Fase 3 (rendimiento)
(blindaje) (seguridad)(tests)  ├─> Fase 4 (refactor, ya con red de tests)
                               └─> Fase 5 (frontend)
                                       Fase 6 corre en paralelo (continuo)
```

**Regla de oro:** no abordar Fase 4 (refactor) antes de Fase 2 (tests) — refactorizar 3.000 líneas duplicadas sin red de tests es introducir regresiones a ciegas.

## Métricas de éxito por fase
- Fase 0: repo con commits granulares, `.env.example` presente, 0 secrets versionables.
- Fase 1: 100% de entidades sensibles con Policy + ownership; 0 IDOR en Customers/Accountings.
- Fase 2: cobertura de flujos de dinero y quiz/exam > 80%; CI verde en cada PR.
- Fase 3: -X queries por listado (medido con debugbar); 0 emails/PDF síncronos en request.
- Fase 4: duplicación de CRUD eliminada; ningún controller > 300 líneas.
- Fase 5: 0 Tabler icons; `{!!` con datos de usuario sanitizados.
- Fase 6: colas y errores de producción observables.
