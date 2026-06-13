# Auditoría de Calidad de Código y Arquitectura
**Fecha:** 2026-06-13  
**Alcance:** `app/Http/Controllers/`, `app/Models/`, `app/Services/`, `app/` root dirs  
**Stack real:** Laravel 12, monolito, Herd nativo

---

## Métricas Generales

| Indicador | Valor |
|---|---|
| Controllers totales | 213 |
| Services totales | 26 (incl. subdirs) |
| Modelos | 108 |
| FormRequest classes | 20 |
| Controllers con inline `$request->validate()` | 38 archivos / 72 llamadas |
| Controllers con `$this->authorize()` | 0 |
| Modelos con `$guarded = []` | 0 |
| Modelos con `$fillable` explícito | 105 |
| Modelos con `$casts` property (old-style) | 2 |
| Modelos con `casts()` method (correcto) | 23 |
| Methods de modelo sin return type | 81 |
| `DB::table` en controllers (fuera de whitelist) | 16 |
| Llamadas SQL raw (`whereRaw`, `DB::raw`, `DB::select`) | 30 |
| Inline styles `style=""` en vistas | 846 |
| Salida no escapada `{!!` en vistas | 506 |
| Líneas totales de controllers | 27,766 |

---

## CRÍTICO

### 1. Cero autorización en controllers
**Severidad: CRÍTICO**

`$this->authorize()` aparece **0 veces** en los 213 controllers. No existen Policies registradas. El acceso a recursos depende únicamente de que el usuario esté autenticado (middleware `auth`), pero no hay verificación de permisos por rol ni por ownership.

Ejemplos afectados: `IncomingMailsController.php:843 líneas`, `CheckoutController.php:726 líneas`, `AnalyticsController.php:565 líneas` — operaciones sensibles sin restricción de rol.

**Fix requerido:** Implementar `$this->authorize('action', Model::class)` o middleware `can:` en todas las rutas sensibles, siguiendo `rules/policies.md`.

---

### 2. Fat Controllers — Lógica de negocio sin delegar a Services
**Severidad: CRÍTICO**

Ratio crítico: **213 controllers vs 26 services**. Los controllers más grandes contienen lógica de negocio compleja incrustada:

| Controller | Líneas |
|---|---|
| `Managers/IncomingMails/IncomingMailsController.php` | 843 |
| `Pages/CheckoutController.php` | 726 |
| `Managers/AnalyticsController.php` | 565 |
| `Managers/Seo/SeoMetaController.php` | 519 |
| `Managers/Enterprises/CourseController.php` | 479 |
| `Managers/Courses/CoursesController.php` | 459 |
| `Managers/MigrationController.php` | 427 |
| `Supports/Users/ManagementController.php` | 426 |
| `Supports/Users/UsersController.php` | 415 |

Los únicos controllers que delegan correctamente a Services son los relacionados con `InscriptionService` (Supports) y `NewsletterService`. El 95% del resto contiene lógica inline.

---

### 3. 72 validaciones inline — cero FormRequests nuevos
**Severidad: CRÍTICO**

Existen 20 FormRequest classes pero 72 llamadas a `$request->validate()` directamente en controllers, en 38 archivos distintos. Esto viola `rules/form-requests.md` y concentra reglas de validación que deberían ser reutilizables y testeables de forma independiente.

Ejemplos:
- `app/Http/Controllers/Managers/Enterprises/CourseController.php` — múltiples `$request->validate()`
- `app/Http/Controllers/Pages/CheckoutController.php` — validación inline en controller de 726 líneas

---

### 4. 506 salidas no escapadas `{!!` en vistas (XSS)
**Severidad: CRÍTICO**

506 ocurrencias de `{!!` en `resources/views/`. Sin revisión de origen de los datos, cualquier entrada de usuario almacenada y renderizada sin escapar es un vector XSS. Requiere auditoría caso a caso para determinar cuáles son legítimas (HTML de editor WYSIWYG) vs cuáles exponen datos de usuario sin sanitizar.

---

## ALTO

### 5. Duplicación masiva entre dominios
**Severidad: ALTO**

El mismo CRUD existe replicado 3 veces para Managers/Supports/Distributors:

| Entidad | Archivos duplicados | Líneas totales |
|---|---|---|
| `Enterprises/CourseController` | 3 (479+312+311) | 1,102 |
| `Enterprises/UserController` | 3 (293+358+348) | 999 |
| `IncomingMails/IncomingMailsController` | 2 (843+318) | 1,161 |
| `Invoices/InvoicesController` | 2 (329+327) | 656 |
| `Orders/OrdersController` + Report + Resumen | 3 dominios | ~900 |

La lógica de negocio de inscripciones, cursos y usuarios se replica con variaciones menores para cada rol. Debería extraerse a Services compartidos con diferenciación de scope mediante parámetros de contexto o policies.

---

### 6. `DB::table` en controllers y 30 llamadas SQL raw
**Severidad: ALTO**

16 usos de `DB::table()` en controllers que deberían usar `Model::query()` según `rules/controllers.md` y `rules/models.md`. Adicionalmente, 30 llamadas a `whereRaw`, `DB::raw` o `DB::select` en `app/` que presentan riesgo de SQL injection si los parámetros no están correctamente vinculados.

Verificar manualmente que todos usen bindings (`?` o named params), no concatenación de strings.

---

### 7. 846 inline styles en vistas
**Severidad: ALTO**

846 ocurrencias de `style=""` en vistas, violando `rules/blade-views.md` que prohíbe explícitamente estilos inline. Impide theming consistente y dificulta mantenimiento de UI.

---

## MEDIO

### 8. Divergencia documentación-realidad (DEUDA CLAVE)
**Severidad: MEDIO (impacto operativo ALTO)**

Los archivos `.claude/rules/` y el `CLAUDE.md` del proyecto describen una arquitectura modular con `nwidart/laravel-modules` (`modules/ModuleName/`) y un entorno Docker. **Ninguno de estos existe en el código real:**

- No existe directorio `modules/`
- `nwidart/laravel-modules` no aparece en `composer.json`
- El proyecto corre con Herd nativo, no Docker
- Las rutas descritas en `rules/routes.md` (`panel/{alias}`) no se corresponden con la estructura real de `app/Http/Controllers/`
- Las reglas de FormRequest en `rules/form-requests.md` describen namespace `Modules\{ModuleName}\Http\Requests` inexistente

**Consecuencia:** Todo agente IA o desarrollador que siga el CLAUDE.md generará código en namespaces y rutas incorrectos. Las 20 FormRequest classes existentes usan namespace `App\Http\Requests`, no modular.

**Acción requerida:** O bien actualizar `.claude/rules/` para reflejar la arquitectura monolítica real, o bien documentar explícitamente el plan de migración a módulos con timeline.

---

### 9. Carpetas anómalas — posible dead code
**Severidad: MEDIO**

| Directorio | Contenido | Diagnóstico |
|---|---|---|
| `app/Model/` | `FormBuilder.php` (109L), `HtmlBuilder.php` (11L) | Namespace duplicado de `app/Models/` (sin `s`). Posible dead code o legacy. |
| `app/Html/` | `FormBuilder.php`, `HtmlBuilder.php` | Duplicado de `app/Model/`. Confusión de namespace. |
| `app/Structure/` | 5 archivos, 63 líneas totales | Clases de estructura de datos muy pequeñas. Uso no verificado — verificar si están referenciadas. |

`app/Model/` y `app/Html/` contienen aparentemente el mismo código en dos ubicaciones diferentes. Uno de los dos es dead code.

---

### 10. helpers.php — God file (552 líneas, 36 funciones)
**Severidad: MEDIO**

`app/helpers.php` con 36 funciones globales de dominio mixto. Funciones globales no testeables unitariamente, sin type hints declarados. Deberían migrar a clases de soporte (`Support/`) o métodos estáticos con namespace.

---

### 11. 81 métodos de modelo sin return type
**Severidad: MEDIO**

De ~350 métodos públicos en modelos, 81 carecen de return type declarations. Esto dificulta el análisis estático (PHPStan/Larastan) y viola `rules/models.md`. Las relaciones sin tipo (`BelongsTo`, `HasMany`, etc.) son el caso más frecuente.

---

## BAJO

### 12. 2 modelos con `$casts` property (old-style)
**Severidad: BAJO**

`app/Models/MailTemplate.php` y `app/Models/MailLog.php` usan `protected $casts = [...]` en lugar del método `casts()` de Laravel 11+. Inconsistente con los 23 modelos que ya usan el método correcto.

---

## Tabla de Adherencia a `.claude/rules/`

| Regla | Estado | Cumplimiento |
|---|---|---|
| `rules/controllers.md` — FormRequests | INCUMPLIDA | 72 inline validates, solo 20 FormRequests |
| `rules/controllers.md` — `$this->authorize()` | INCUMPLIDA | 0 llamadas en 213 controllers |
| `rules/controllers.md` — Delegar a Services | PARCIAL | ~5% de controllers usan Services |
| `rules/controllers.md` — `Model::query()` | PARCIAL | 16 `DB::table` sin justificación |
| `rules/models.md` — `$fillable` explícito | CUMPLIDA | 105/108 modelos |
| `rules/models.md` — `$guarded = []` prohibido | CUMPLIDA | 0 ocurrencias |
| `rules/models.md` — `casts()` method | PARCIAL | 23 correcto, 2 old-style |
| `rules/models.md` — Return types en relaciones | PARCIAL | ~75% tienen tipo |
| `rules/blade-views.md` — No inline styles | INCUMPLIDA | 846 ocurrencias |
| `rules/blade-views.md` — No `{!!` sin justificación | RIESGO | 506 ocurrencias |
| `rules/form-requests.md` — Namespace modular | INAPLICABLE | No existe `modules/` |
| `rules/policies.md` — Policies obligatorias | INCUMPLIDA | 0 policies, 0 authorize() |

---

## Top 4 Mejoras Estructurales de Mayor Impacto

### 1. Implementar autorización (Policies + authorize)
Impacto en seguridad: máximo. Crear Policy base por dominio y añadir `$this->authorize()` empezando por controllers de gestión de usuarios, pagos y cursos. Tiempo estimado: alto, pero no se puede diferir.

### 2. Extraer lógica de negocio duplicada a Services compartidos
Crear `CourseEnrollmentService`, `UserManagementService`, `InvoiceService` que sirvan a los 3 dominios (Managers/Supports/Distributors). Eliminar ~3,000 líneas de código duplicado. Prioridad: `CourseController` (1,102 líneas en 3 archivos) y `UserController` (999 líneas).

### 3. Alinear `.claude/rules/` con la arquitectura real
Actualizar las reglas de agente IA para reflejar namespace `App\Http\Controllers\{Domain}\`, entorno Herd, y rutas reales. O documentar el roadmap de migración a módulos. Sin esto, cualquier generación de código con IA produce artefactos con namespace incorrecto.

### 4. Convertir validaciones inline a FormRequests
38 controllers con 72 `$request->validate()` → crear FormRequest por entidad. Beneficio secundario: habilita tests de validación independientes y centraliza mensajes de error en español.
