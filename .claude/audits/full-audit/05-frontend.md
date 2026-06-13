# Auditoria Frontend/Blade — resources/views/ (684 archivos)

**Fecha:** 2026-06-13  
**Alcance:** `resources/views/` completo — panel admin (managers/accountings/supports/customers/distributors/enterprises) + publico `pages/`  
**Nota:** No existen `modules/*/resources/views/` en este repo; todas las vistas viven en `resources/views/`.

---

## 1. Inline styles (`style=""` hardcodeado)

| Contexto | Archivos afectados |
|---|---|
| Admin (non-pages) | **116** archivos |
| Publico (pages/) | **31** archivos |
| **Total** | **147 archivos** |

Ejemplos representativos:
- `customers/includes/notification.blade.php:83` — `style="background-color: {{$notification->data['mailsendtagcolor']}}"` (dato de BD en inline style)
- `customers/views/quizs/quiz.blade.php:120` — `style="display: none;"` (deberia ser clase `.d-none`)
- `customers/views/certificates/download.blade.php:235` — layout de certificado con `style="height: 100%; width: 100%; overflow: hidden; position: relative;"`
- `customers/includes/nav.blade.php:49` — `style="display: none;"` (ocultar sidebar item)

**Patron mas frecuente:** `display:none` que deberia ser `.d-none`, y colores dinamicos de BD en inline styles (segunda categoria es aceptable si no hay alternativa CSS, pero deben revisarse).

---

## 2. Tabler Icons (`ti ti-*`) — PROHIBIDO en admin

| Contexto | Archivos | Ocurrencias |
|---|---|---|
| Admin (non-pages) | **70** archivos | **190** ocurrencias |
| Publico (pages/) | 0 | 0 |

Ejemplos:
- `customers/includes/header.blade.php:6` — `<i class="ti ti-menu-2">`
- `customers/includes/notification.blade.php:5` — `<i class="ti ti-bell-ringing">`
- `accountings/includes/nav.blade.php:13` — `<i class="ti ti-dots nav-small-cap-icon fs-4">`
- `customers/includes/nav.blade.php:6` — `<i class="ti ti-dots nav-small-cap-icon fs-4">`

**Patron:** Afecta principalmente los `includes/` de navegacion (header, nav, notification) de cada rol. Estos componentes son compartidos y se repiten en customers, accountings, managers, supports, enterprises, distributors.

---

## 3. Livewire / Inertia / Alpine / React — restos prohibidos

| Framework | Archivos afectados |
|---|---|
| `wire:` / `x-data=` / `@livewire` / `<livewire:` | **0** archivos |

**Resultado: LIMPIO.** No hay restos de Livewire, Alpine, Inertia ni React.

---

## 4. CSRF en AJAX

**Estado: CORRECTO en todos los layouts.**

Todos los layouts del proyecto configuran `$.ajaxSetup` con `X-CSRF-TOKEN` globalmente:

| Layout | ajaxSetup | X-CSRF-TOKEN |
|---|---|---|
| `layouts/managers.blade.php` | SI | SI |
| `layouts/customers.blade.php` | SI | SI |
| `layouts/supports.blade.php` | SI | SI |
| `layouts/accountings.blade.php` | SI | SI |
| `layouts/distributors.blade.php` | SI | SI |
| `layouts/enterprises.blade.php` | SI | SI |
| `layouts/pages.blade.php` | SI | SI |
| `layouts/auth.blade.php` | SI | SI |
| `layouts/maintenance.blade.php` | SI | SI |

Patron usado en todos: `$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })`.

Archivos con `$.ajax()`: 232 archivos. Al estar el token configurado globalmente via `ajaxSetup`, no requieren declararlo individualmente. **Sin violaciones.**

---

## 5. `{!! !!}` unescaped — riesgo XSS

| Contexto | Archivos afectados |
|---|---|
| Admin (non-pages) | **207** archivos |
| Publico (pages/) | **14** archivos |
| **Total** | **221** archivos |

Casos de mayor riesgo (datos de usuario sin sanitizar):
- `customers/views/instructions/view.blade.php:35` — `{!! $instruction->description !!}` (contenido rico de BD)
- `customers/views/courses/content.blade.php:73` — `{!! $course->short !!}`, `{!! $course->learn !!}` (campos de texto libre)
- `customers/views/courses/lesion.blade.php:64` — `{!! $classing->detail !!}` (descripcion de leccion)
- `customers/views/courses/content.blade.php:88` — `{!! $announsment->description !!}` (anuncio de admin)
- `supports/views/instructions/instructions/edit.blade.php:40` — `{!! Form::select(...) !!}` (Laravel Collective — esto es seguro, es un helper de formulario)

**Observacion:** La mayoria son contenidos HTML enriquecidos editados por admins (cursos, instrucciones). Estos son aceptables si el campo pasa por un sanitizador (HTMLPurifier). Los `Form::select` con `{!! !!}` son seguros (Laravel Collective genera HTML interno). **Riesgo real:** campos `description`/`short`/`learn` si no hay sanitizacion en el modelo o en el servicio de guardado.

---

## 6. Colores hardcodeados en Blade

| Contexto | Archivos con hex |
|---|---|
| Total (todas las vistas) | **138** archivos |
| `#90bb13` prohibido en pages/ | **0** archivos |

El color prohibido `#90bb13` no aparece en `pages/`. Los 138 archivos con hex incluyen principalmente el layout de certificados (`certificates/`) y algunas configuraciones de charts.

---

## 7. Duplicacion de markup / falta de componentes `@include`

**Nivel: ALTO.** El proyecto NO usa `@include('core::')` ni componentes compartidos:

- `grep "@include('core::" resources/views/` → **0 ocurrencias**
- 312 tablas (`<table`) en admin — cada una duplica estructura de header/body/footer
- Cada modulo rol (customers, supports, managers, accountings, etc.) duplica sus propios `includes/header.blade.php`, `includes/nav.blade.php`, `includes/notification.blade.php` con patrones identicos

**Patron mas duplicado:** Los archivos `includes/header.blade.php` y `includes/nav.blade.php` son copias con leves variaciones en cada rol (6 layouts × 3 includes = 18 archivos de ~3 archivos reales).

---

## 8. Accesibilidad: `<img>` sin `alt`

| Metrica | Valor |
|---|---|
| Archivos con `<img>` sin `alt` | **14** archivos |
| Tags `<img>` sin `alt` (total) | **61** ocurrencias |

Estos 61 img tags carecen del atributo `alt`, incumpliendo WCAG 2.1 nivel A.

---

## 9. Mezcla Bootstrap (BS4 en pages / BS5 en admin)

### BS5 utility classes en `pages/` (contexto Bootstrap 4.5.3)

| Archivos afectados | Clases BS5 encontradas |
|---|---|
| **21** archivos | `fw-bold`, `fs-3`, `me-*`, `pe-*`, `ms-*`, `ps-*`, `gap-*` |

Ejemplos:
- `pages/includes/header.blade.php:129` — `pe-0` (BS5, en BS4 seria `pr-0`)
- `pages/includes/header.blade.php:141` — `ms-3` (BS5, en BS4 seria `ml-3`)
- `pages/includes/header.blade.php:142` — `fs-3` (BS5, no existe en BS4)
- `pages/includes/header.blade.php:165` — `ps-3` (BS5, en BS4 seria `pl-3`)

### BS4 legacy classes en admin (contexto Bootstrap 5.3)

| Archivos afectados | Clases BS4 encontradas |
|---|---|
| **92** archivos | `float-left`, `float-right`, `mr-*`, `ml-*` |

Ejemplos:
- `customers/views/orders/payment.blade.php:76` — `mr-1` (BS4, en BS5 seria `me-1`)
- `supports/views/orders/payment.blade.php:76` — `mr-1`
- `managers/views/settings/payments/setting.blade.php:99` — `mr-1` en boton `fas fa-save`

---

## Tabla de adherencia a reglas

| Regla | Cumplimiento | Violaciones |
|---|---|---|
| Font Awesome 6 solamente (no Tabler) | ROJO | 70 archivos, 190+ ocurrencias en admin |
| No `style=""` inline | ROJO | 147 archivos (116 admin + 31 pages) |
| jQuery+AJAX (no Livewire/Alpine/Inertia) | VERDE | 0 violaciones |
| CSRF en AJAX via ajaxSetup | VERDE | Configurado globalmente en todos los layouts |
| `{!! !!}` solo para HTML seguro | AMARILLO | 221 archivos — mayoria son campos admin (riesgo bajo), pero faltan sanitizadores documentados |
| Sin colores prohibidos (`#90bb13`) en pages/ | VERDE | 0 violaciones |
| Componentes `@include` reutilizables | ROJO | 0 uso de `core::components`, 312 tablas duplicadas |
| `<img>` con `alt` | ROJO | 61 tags sin alt en 14 archivos |
| Bootstrap correcto por contexto | AMARILLO | 21 archivos pages/ con BS5 classes; 92 archivos admin con BS4 legacy |
| Sin `@livewire`/`wire:`/`x-data` | VERDE | 0 violaciones |

---

## Top 4 mejoras de mayor impacto

### 1. Eliminar Tabler Icons de los includes de navegacion (70 archivos, ~190 ocurrencias)
Los archivos `includes/header.blade.php`, `includes/nav.blade.php` y `includes/notification.blade.php` de los 6 roles usan masivamente `ti ti-*`. Reemplazar con Font Awesome 6 equivalentes (`fa-bars` para `ti-menu-2`, `fa-ellipsis` para `ti-dots`, `fa-bell` para `ti-bell-ringing`). Como son archivos de include compartidos, el impacto de correccion es bajo (18 archivos, no 190).

### 2. Extraer includes de navegacion a componentes compartidos (reduce 18 archivos a 3)
`header.blade.php`, `nav.blade.php` y `notification.blade.php` son copias casi identicas en cada rol. Extraer a un componente Blade parametrizado (`@include('layouts.partials.header', ['role' => 'customer'])`) elimina 80%+ de la duplicacion de markup y facilita mantenimiento. Esto tambien resuelve automaticamente el punto 1.

### 3. Sustituir `style="display: none;"` por clases CSS en los 147 archivos con inline styles
El patron mas frecuente es `style="display: none;"` → reemplazar por `d-none`. Los `style="background-color: ..."` con datos dinamicos de BD (color de tag de notificacion) requieren un approach diferente: generar una clase CSS dinamica via `<style>` en el componente o usar una variable CSS. Este cambio mejora mantenibilidad y CSP.

### 4. Auditar y sanitizar campos `{!! !!}` con contenido de BD (207 archivos admin)
Los campos `description`, `short`, `learn`, `detail` de cursos e instrucciones se renderizan sin escape. Verificar que el modelo o servicio aplique HTMLPurifier antes de guardar. Si no hay sanitizacion, agregar `clean()` helper o un cast personalizado en el modelo. Prioridad: `courses/content.blade.php` y `instructions/view.blade.php` que renderizan contenido de usuarios/admins directamente.
