# Auditoría: Empresas (panel manager)

## Resumen

Alcance revisado: `app/Http/Controllers/Managers/Enterprises/*` (Enterprises, User, Course, Rates),
`app/Models/Enterprise/*`, `resources/views/managers/views/enterprises/**`, rutas en
`routes/managers.php` (bloque `prefix('enterprises')`), imports/exports asociados
(`App\Imports\Managers\UsersImport`, `CoursesImport`, `App\Exports\Managers\IncomesExport`,
`UsersExport`, `CoursesExport`) y `App\Services\InscriptionService`.

No existen Form Requests en `app/Http/Requests/Managers/Enterprises/` — toda la validación (la que
existe) es inline o inexistente. El dominio tiene un patrón sólido para evitar el antipatrón
Eloquent `scope...()->first() ?? Builder` (todos los `scopeSlack`/`scopeId` de los modelos tocados
usan `abort_unless($model !== null, 404)`), así que esa clase de bug ya está resuelta aquí.

El hallazgo más grave es funcional-crítico y no de seguridad "clásica": el import masivo de cursos
(`CoursesImport::collection()`) construye `Order::create()` e `Inscription::create()` con columnas
que **no existen** en las tablas reales (`orders.course_id`, `orders.subtotal`, `orders.discount`,
`orders.total`, `orders.enroll_start`, `orders.enroll_expire`) y omite `inscriptions.slack`, que es
`NOT NULL` sin default — es decir, la funcionalidad de "importar usuarios a un curso vía CSV" está
rota y lanzará una `QueryException` en cada fila procesada. Junto a eso, hay múltiples endpoints de
mutación sin ningún control de autorización y al menos un IDOR real en `RatesController::update`
donde se puede sobreescribir el precio de cursos de *otra* empresa.

## Hallazgos

### 🔴 Crítico / Seguridad

1. **IDOR en precios de empresa** — `app/Http/Controllers/Managers/Enterprises/RatesController.php:26-48`.
   `update()` resuelve `$enterprise` desde el slack pero nunca lo usa para acotar la escritura: hace
   `EnterpriseCourse::find($id)` sobre los IDs enviados en `$request->courses` sin verificar
   `enterprise_id === $enterprise->id`. Un manager (o un request forjado) puede enviar IDs de
   pivotes `enterprise_course` que pertenecen a otra empresa y sobreescribir su tarifa. Además no
   hay `abort_unless(auth()->user()->can(...))` en todo el controller — ninguna acción de Rates
   está protegida por permiso.
   Fix: `EnterpriseCourse::where('id', $id)->where('enterprise_id', $enterprise->id)->first()` y
   agregar `abort_unless($user->can('enterprises.update'), 403)`.

2. **IDOR en reasignación de cursos** — `CourseController.php:237-265` (`actionReasign`).
   Cuando `$users[0] != 0`, hace `User::identification($identifier)` **sin acotar por empresa** y
   reasigna sus inscripciones (`Inscription::where('user_id', $user->id)->where('course_id', $old->id)`)
   al nuevo curso. Si se escribe la identificación de un usuario que no pertenece a `$enterprise`
   (cliente público u otra empresa), sus inscripciones/certificados/exámenes igual se reasignan
   (ver `reassignInscription()`, que además borra respuestas de examen/quiz). No hay tampoco
   `abort_unless` de permiso en este método. Fix: validar
   `$enterprise->users()->where('users.id', $user->id)->exists()` antes de tocar inscripciones, y
   verificar permiso.

3. **Enrollment "simple" (costo cero) sin acotar por empresa** — `EnterprisesController::generate()`
   (líneas 164-178) y `CourseController::includes()` (líneas 385-394) llaman a
   `InscriptionService::enrollSimpleBulk($identifications, $course)`, que internamente hace
   `User::identification($identification)` de forma **global** (`InscriptionService.php:206-213`).
   Cualquier identificación válida en el sistema —no solo las de la empresa mostrada en pantalla—
   puede ser matriculada gratis en el curso. No hay verificación de pertenencia usuario↔empresa en
   ningún punto de la cadena. Mismo problema conceptual que el punto 2.

4. **Autorización ausente en la mayoría de acciones de mutación**. Ningún método de
   `UserController` tiene `abort_unless`/policy (`store`, `update`, `import`, `importation`,
   `generate`, `incoming`, `report`, `income`, `create`, `edit` — cero). En `CourseController`
   faltan en `actionReasign`, `postponeCourses`, `postponeUsers`, `actionCourses`, `actionUsers`,
   `includes`, `generate`, `import`, `importation`, `progress`, `reasign`, `insert`, `report`,
   `user`, `view` (solo `store`/`destroy`/`destroys` están protegidos). En `EnterprisesController`
   faltan en `create`, `edit`, `navegation`, `inscriptions`, `generate`. Solo el middleware de rol
   `manager` (a nivel de grupo de rutas) protege estos endpoints; no hay capa de permiso Spatie
   granular como exige la convención `{alias}.action` del proyecto. Cualquier usuario con acceso al
   panel manager (independientemente de sus permisos Spatie específicos) puede ejecutar estas
   acciones.

5. **Sin validación de tipo/tamaño de archivo en imports** —
   `UserController::importation()` (líneas 247-269) y `CourseController::importation()`
   (líneas 436-459). Solo verifican `hasFile('file') && isValid()` (o ni eso, en el caso de
   Courses). No hay Form Request con `['required','file','mimes:xlsx,csv','max:2048']`, por lo que
   se puede subir cualquier tipo/tamaño de archivo al servidor antes de que Maatwebsite Excel falle
   (o no falle) al parsearlo. Adicionalmente, `CoursesImport` (a diferencia de `UsersImport`) no
   implementa `WithValidation`: no hay *ninguna* validación de las filas del CSV (identificación
   vacía, formato, duplicados), a pesar de que el punto 2 de la guía de auditoría pide exactamente
   esto.

### 🟡 Funcional / Datos

6. **Import de cursos por CSV está roto (bug crítico funcional)** —
   `App\Imports\Managers\CoursesImport::collection()` (líneas 39-70).
   - `Order::create([...'subtotal'=>0,'discount'=>0,'total'=>0,'course_id'=>...,'enroll_start'=>...,'enroll_expire'=>...])`:
     ninguna de esas columnas existe en la tabla `orders` real (verificado con `Schema::getColumnListing('orders')`;
     columnas reales usan `total_before_discount`/`total_after_discount`/`total_order_amount`, y no
     existe `course_id` ni `enroll_start/expire` en `orders`). Al no estar en `$fillable` de `Order`,
     Eloquent las descarta silenciosamente — la orden creada queda sin importe, sin curso y sin
     fechas de matrícula.
   - `Inscription::create(['user_id'=>...,'course_id'=>...,'order_id'=>...,'culminated'=>0,'culminated_at'=>null])`
     omite `slack`, que es `NOT NULL` sin default en la tabla `inscriptions` (confirmado via
     `SHOW COLUMNS`) → **lanza `QueryException` en cada fila** que se intente importar. También
     omite `enroll_start`/`enroll_expire`/`percent` aunque el propio archivo calcula `$now` para
     ese propósito y nunca lo usa. En la práctica, esta pantalla de "importar usuarios a un curso"
     (`courses/import.blade.php` → `importation()`) no puede completar ninguna fila hoy.
   - Nota: el propio comentario en `CourseController::actionReasign` línea 244-245 documenta que
     "orders.course_id no existe" — es decir, ya se sabía del desajuste de esquema en otra parte del
     mismo módulo, pero no se corrigió en `CoursesImport`.

7. **`incoming()` (reporte de ingresos) no resuelve la empresa vía modelo** —
   `UserController::incoming()` (líneas 281-292) toma `$request->enterprise` crudo y lo pasa
   directo a `IncomesExport`, en vez de `Enterprise::slack()/id()` (que sí abortarían 404 si el
   valor es inválido). Es inconsistente con `generate()` unas líneas arriba, que sí usa
   `Enterprise::id($request->enterprise)`. No es inyección SQL (los value bindings de
   `DB::table()->where()` son seguros), pero un ID inexistente o `null` no produce error visible,
   solo un reporte vacío — inconsistente y difícil de depurar.

8. **Parseo de rango de fechas sin validar formato** — se repite en
   `CourseController::actionCourses` (líneas 361-371), `actionUsers` (líneas 373-383) y
   `UserController::incoming` (líneas 281-292): `explode(' - ', $request->range)` seguido de
   `$date_var[1]` sin comprobar que el explode produjo 2 elementos. Un `range` mal formado (o
   ausente) genera un warning de índice indefinido y probablemente una excepción de Carbon, no un
   422 controlado. Debería ser un Form Request con regla de formato de fecha explícita.

9. **Comparación floja / falta de casteo en `actionReasign`** — `$users[0] == 0` (línea 247) para
   decidir "reasignar a todos". Si el frontend envía identificaciones alfanuméricas que empiecen
   con letras, la comparación floja de PHP8 es segura (`"ABC" == 0` es `false`), pero sigue siendo
   frágil: preferible `$request->input('users.0') === '0'` explícito.

10. **Naming inconsistente en rutas** — `routes/managers.php:263`: parámro `{enterprice}` (typo,
    debería ser `{enterprise}`) en la ruta de destroy de cursos. Convive con `{enterprises}`
    (plural) en otras rutas del mismo bloque (líneas 264-268) mientras el resto del proyecto usa
    singular. No rompe nada funcionalmente pero es inconsistente con la convención de
    `rules/routes.md`.

11. **Duplicación de lógica `createCourses`/`createUsers`** — `CourseController.php:295-314`:
    métodos estáticos que solo instancian un DTO (`App\Structure\Courses`/`Users`) copiando 4
    propiedades campo a campo; podrían resolverse con `Courses::make([...])` o simplemente no
    necesitar el helper. `create()` (líneas 55-96) además hace dos loops manuales con
    `prepend()` + `diffKeys()` para calcular "cursos disponibles para asignar" cuando
    `Course::whereNotIn('id', $enterprise->courses->pluck('id'))->pluck('title','id')` haría lo
    mismo en una sola query.

### 🔵 UX / Consistencia

12. **`Form::select()` con `{!! !!}`** en 11 vistas (`courses/report`, `courses/reasign` x2,
    `courses/create`, `courses/insert`, `enterprises/inscription` x2, `enterprises/edit`,
    `users/income`, `users/report`, `users/edit`). El helper `Form::select` del paquete
    `laravelcollective/html` escapa internamente las opciones, por lo que el riesgo XSS práctico es
    bajo siempre que `$courses`/`$users`/`$modalities` vengan de `pluck()` sobre columnas propias
    (title/identification) — pero sigue siendo el patrón `{!! !!}` que la lista de anti-patrones del
    proyecto pide señalar explícitamente. Ninguna vista en este módulo usa `{!! !!}` para imprimir
    contenido de usuario libre (no se encontró interpolación directa de HTML arbitrario).

13. **Sin FormRequest / validación de formulario visible en ninguna vista del módulo** — los
    formularios de creación/edición de empresa y de usuario de empresa no muestran mensajes de
    error por campo provenientes de `$errors` porque el backend nunca los genera (todo son
    respuestas JSON ad-hoc con un único `message` genérico). Esto contradice
    `.claude/rules/form-requests.md` (`authorize()` + `rules()` + `messages()` en español) en la
    totalidad del submódulo.

14. **`RatesController` sin vista de importación coherente con el resto** — el resto del módulo
    (`users`, `courses`) sigue el flujo index→create→import→report; `rates/index.blade.php` es la
    única pantalla de tarifas y no tiene bulk actions/dropdown de fila estándar (`fa-ellipsis-vertical`)
    documentado en `ui-patterns/list-patterns.md`; se editó directamente en tabla.

### ⚪ Cobertura de tests

15. **Cobertura parcial**. Existen `tests/Feature/Managers/EnterprisesTest.php` (listar, crear,
    actualizar, rechazo de email duplicado, usuario no autorizado no puede crear — 5 tests) y
    `tests/Feature/Managers/EnterpriseUserTest.php` (asignación de `enterprise_id` según rol,
    hash de password una sola vez — 3 tests). **No hay ningún test** para: `CourseController`
    (index/create/store/destroy/reasign/postpone/import/report/generate — 0% cobertura),
    `RatesController` (0% cobertura, incluyendo el IDOR del hallazgo #1), import/export de
    usuarios y cursos (`UsersImport`, `CoursesImport`, `UsersExport`, `CoursesExport`,
    `IncomesExport`), ni de los flujos de matrícula (`enrollSimpleBulk`, `actionReasign`). Dado que
    el bug crítico #6 (import de cursos roto) y el IDOR #1/#2 viven exactamente en el código sin
    tests, un test de feature que suba un CSV de ejemplo o llame a `actionReasign`/`update` de Rates
    con una empresa distinta habría detectado ambos problemas.

## Recomendación de prioridad

1. Corregir `CoursesImport::collection()` (hallazgo #6) — es un bug 100% reproducible que rompe una
   funcionalidad visible del panel; agregar test de feature que suba un CSV y verifique que se crean
   `Order`/`Inscription` completos.
2. Cerrar el IDOR de `RatesController::update` (#1) y `actionReasign`/`enrollSimpleBulk` (#2, #3)
   acotando por `enterprise_id` antes de escribir, y agregar policy/`abort_unless` a **todas** las
   acciones de mutación del módulo (#4) — hoy dependen solo del middleware de rol `manager`.
3. Introducir Form Requests dedicados para Enterprises/Users/Courses/Rates (create/update/import),
   incluyendo `mimes:xlsx,csv` + `max:` en los imports (#5, #13) y activar `WithValidation` en
   `CoursesImport` igual que ya existe en `UsersImport`.
4. Escribir tests de feature para `CourseController` y `RatesController` (#15), priorizando los
   casos de aislamiento entre empresas (empresa A no debe poder afectar datos de empresa B),
   siguiendo el patrón ya usado en otros dominios del proyecto (`order/certificate ajeno -> 404`).
5. Limpieza menor: typo de ruta `{enterprice}` (#10), refactor de `createCourses`/`create()` en
   `CourseController` (#11), y estandarizar la pantalla de Rates a los patrones de
   `ui-patterns/list-patterns.md` (#14).
