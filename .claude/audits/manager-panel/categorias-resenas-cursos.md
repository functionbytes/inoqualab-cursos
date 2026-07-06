# Auditoría: Categorías y Reseñas de Cursos (panel manager)

Alcance revisado:
- `app/Http/Controllers/Managers/Courses/CategoriesController.php`
- `app/Http/Controllers/Managers/Courses/ReviewsController.php`
- `resources/views/managers/views/courses/categories/{index,create,edit,view}.blade.php`
- `resources/views/managers/views/courses/reviews/index.blade.php`
- `app/Models/Course/CourseCategorie.php`, `app/Models/Course/CourseReview.php`
- `app/Http/Requests/Customers/StoreReviewRequest.php`
- `routes/managers.php` (grupos `courses` prefix `/categories` y `reviews`)
- `tests/` (búsqueda de cobertura)

## Resumen

Categorías de curso **no fue migrado** al patrón de modal usado en el resto de Cursos esta sesión: `create` y `edit` siguen siendo páginas completas con markup legacy (labels sin asterisco de obligatoriedad, sin `select2` inicializado, JS con jQuery Validate a la antigua). Reseñas nunca tuvo create/edit propio (es un listado de moderación posterior al hecho), así que no aplica la migración a modal, pero sí carece de cualquier acción de moderación real: solo puede eliminar, no aprobar/rechazar. No hay Form Request ni validación alguna en `CategoriesController::store/update` (los campos se asignan directo desde `$request`). La autorización es inconsistente: las acciones de lectura (`index`, `create`, `edit`, `view` de Categorías; `index` de Reseñas) no verifican ningún permiso Spatie, solo las de escritura lo hacen. No se encontró ningún test para ninguno de los dos submódulos.

## Hallazgos

### 🔴 Crítico / Seguridad

1. **Sin validación en `CategoriesController::store()` y `update()`** (`app/Http/Controllers/Managers/Courses/CategoriesController.php:73-106`) — No hay Form Request ni `$request->validate()` inline; `title` y `available` se asignan directamente desde el request sin ninguna regla (ni siquiera `required`/`max`). Un `title` vacío o un `available` con un valor arbitrario se guardan tal cual. Contradice `rules/form-requests.md` y `rules/controllers.md` ("Use Form Request classes for ALL validation"). Fix sugerido: crear `StoreCourseCategorieRequest` / `UpdateCourseCategorieRequest` con `title` required/max:255 y `available` boolean.

2. **`index`, `create`, `edit`, `view` sin `authorize()`/permiso** en `CategoriesController` (líneas 12-71) y `index` en `ReviewsController` (líneas 11-38) — Solo `store`, `update`, `destroy` (Categorías) y `destroy` (Reseñas) verifican `auth()->user()->can('courses.*')`. Cualquier usuario con rol `manager` (sin importar sus permisos Spatie específicos) puede ver los listados/formularios; el 403 solo aparece al intentar guardar. Es una inconsistencia respecto a `rules/policies.md` ("Skip `authorize()` en controllers" está en la lista de NO usar) y respecto al resto del módulo Cursos migrado esta sesión. No es explotable más allá de la UI (la escritura sigue bloqueada), pero expone formularios/datos a roles que no deberían verlos y es fácil de corregir agregando `abort_unless(...->can('courses.view'), 403)` al inicio de cada método de lectura.

### 🟡 Funcional / Datos

3. **Bug: `view.blade.php` referencia columnas inexistentes** (`resources/views/managers/views/courses/categories/view.blade.php:25,31,38`) — Usa `$categorie->name` y `$categorie->description`, pero el modelo `CourseCategorie` solo tiene `title`, `slug`, `slack`, `available`, `position` (`app/Models/Course/CourseCategorie.php:17-25`). La página "Detalle de categoría" siempre mostrará "Nombre" y "Slug" vacíos (Eloquent devuelve `null` en atributos no definidos, no lanza excepción, pero el dato correcto — `title` — nunca se muestra). Fix: cambiar a `$categorie->title` y eliminar el bloque de `description` (no existe en el esquema) o agregarla a la migración si se necesita.

4. **Reseñas: no hay workflow de moderación (aprobar/rechazar)** — `CourseReview` no tiene columna `status`/`approved` (ver migración `2026_06_09_154338_create_course_reviews_table.php`). Una reseña se publica de inmediato en `Customers/CoursesController::review()` (`app/Http/Controllers/Customers/CoursesController.php:254-296`) y aparece en la página pública del curso sin pasar por el panel. El panel manager (`ReviewsController`) solo permite **ver y eliminar**, no aprobar/ocultar selectivamente. Si el negocio requiere moderación previa, esto es una brecha funcional; si el diseño es intencional (publicación directa + moderación reactiva por eliminación), conviene documentarlo explícitamente para que no se lea como un descuido.

5. **Reseñas duplicadas: correctamente prevenidas** — Constraint único `(user_id, course_id)` en la migración + `CourseReview::firstOrNew(['user_id'=>..., 'course_id'=>...])` en `CoursesController::review()` (líneas 279-291) hace *upsert* de la reseña existente en vez de crear una duplicada. Esto es un acierto, no un hallazgo negativo — se documenta para que quede claro que ya está cubierto.

### 🔵 UX / Consistencia

6. **Categorías de curso: crear/editar siguen siendo página completa, NO modal** (`categories/create.blade.php`, `categories/edit.blade.php`) — A diferencia de Anuncios, Capítulos, Clases, Quiz y Examen (migrados esta sesión al patrón modal), Categorías todavía usa `@extends('layouts.managers')` con formulario de página completa, jQuery Validate old-style, sin asteriscos de campo obligatorio, y `select2 form-control` sin inicialización visible en el propio archivo (depende de un init global — a confirmar que cubre este selector). **Este es el hallazgo de UX más visible**: cualquiera que navegue de Clases/Quiz (modal) a Categorías (página completa) dentro del mismo módulo Cursos notará la inconsistencia de inmediato. Candidato directo a la misma migración a modal aplicada al resto de Cursos. (No se implementa aquí, solo se señala per alcance de la tarea.)

7. **Reseñas: no aplica migración a modal** — Reseñas nunca tuvo create/edit (las reseñas las crea el cliente, no el manager); el panel es puramente un listado de moderación con `destroy`. No hay página completa de formulario que migrar aquí. Sí se detectó un `style="white-space:normal;max-width:360px"` inline en la celda de comentario (`reviews/index.blade.php:89`), que viola la regla de "no inline styles" — mover a una clase CSS (`.rev-comment-cell`).

8. **`CourseCategorie::scopeSlug()`/`scopeSlack()`/`scopeId()` devuelven un Model, no un Builder** — Reutilizan el patrón ya conocido de este proyecto (ver memoria `project_scopes_first_builder_pitfall`): `abort_unless($model !== null, 404)` dentro del scope. Correcto aquí (a diferencia de otros casos donde el 404 faltaba), solo se deja constancia de que sigue el patrón establecido.

### ⚪ Cobertura de tests

9. **Sin ningún test para Categorías ni Reseñas de Cursos** — Búsqueda en `tests/` por `CourseCategorie`, `CourseReview`, `manager.categories.courses`, `manager.reviews` no arrojó resultados. No hay cobertura de: creación/edición de categoría (ni de la ausencia de validación del hallazgo #1), eliminación bloqueada por categoría con cursos asociados (`CategoriesController::destroy`, línea 116), listado/filtro de reseñas, eliminación de reseña y recálculo de rating (`$course->recalculateRating()`), ni de la restricción de una reseña por usuario/curso.

## Recomendación de prioridad

1. Agregar Form Request a `CategoriesController::store/update` (hallazgo #1) — es la única escritura sin validación de todo el módulo Cursos auditado hasta ahora.
2. Cerrar el gap de autorización en los métodos de lectura de ambos controllers (hallazgo #2) — cambio pequeño y de bajo riesgo.
3. Corregir `view.blade.php` (hallazgo #3) — bug visible, un campo mostrado en blanco.
4. Migrar Categorías al patrón modal (hallazgo #6) — mismo esfuerzo ya aplicado al resto de Cursos esta sesión; dejarlo pendiente acumula deuda de inconsistencia visual.
5. Definir si se requiere moderación previa de reseñas (hallazgo #4) — decisión de producto, no solo de código.
6. Escribir tests Feature mínimos para ambos submódulos (hallazgo #9) antes de tocar cualquiera de los puntos anteriores, para no regresar sin red de seguridad.
