# Auditoría: Distribuidores (panel manager)

## Resumen
Dominio con 7 controllers (`Distributors`, `Staff`, `Course`, `Enterprise`, `Rates`, `Orders`, `Invoices`) sin un solo `FormRequest` propio: toda la validación de alta/edición vive únicamente en jQuery Validate del lado cliente, trivialmente evitable. La autorización de escritura existe (aunque duplicada/inconsistente entre `distributors.*` y `staff.*`), pero las acciones de solo lectura no tienen ningún chequeo explícito en controller y dependen 100% del middleware de convención `EnforcePanelPermission`. `RatesController::update` ya implementa el patrón correcto de ownership + validación numérica; ese mismo patrón falta en `CourseController`/`EnterpriseController`. Cero cobertura de tests.

## Hallazgos

### 🔴 Crítico / Seguridad

1. **Sin validación server-side en alta/edición** — `DistributorsController::store/update` y `StaffController::store/update` no usan `FormRequest` ni `$request->validate()`; toda la validación (obligatoriedad, longitud, formato de email, formato numérico de celular) está solo en `create.blade.php`/`edit.blade.php` vía jQuery Validate (líneas ~134-218 de `distributors/create.blade.php`). Una petición POST directa (curl/Postman) con sesión válida puede crear/editar distribuidores y empleados con título vacío, NIT/email arbitrarios, celular no numérico, etc. Contradice `.claude/rules/form-requests.md` y `.claude/rules/controllers.md`.
   Archivos: `app/Http/Controllers/Managers/Distributors/DistributorsController.php:79-156`, `StaffController.php:120-209`.
   Fix: crear `StoreDistributorRequest`/`UpdateDistributorRequest` y `StoreDistributorStaffRequest`/`UpdateDistributorStaffRequest` en `app/Http/Requests/Managers/Distributors/` (el directorio no existe hoy).

2. **Autorización ausente en acciones de lectura** — `index`, `create`, `edit`, `view`, `navegation`, `history` en los 7 controllers no llaman `$this->authorize()` ni `abort_unless(...->can(...))`; solo `store`/`update`/`destroy` lo hacen. Dependen exclusivamente del middleware `panel.permission` (`EnforcePanelPermission`), que **falla abierto** si el permiso derivado no existe en el catálogo (comportamiento documentado y aceptado en el proyecto, pero que aquí se combina con el hallazgo #3).
   Fix: replicar el `abort_unless(auth()->user()->can('distributors.view'), 403)` también en los métodos de lectura, igual que ya se hace en los de escritura.

3. **Espacio de permisos inconsistente para "staff"** — `EnforcePanelPermission` deriva el dominio del permiso SIEMPRE del segundo segmento del nombre de ruta (`manager.distributors.staffs.store` → dominio `distributors`), por lo que exige `distributors.create/update/delete` para TODAS las subrutas (staff, courses, enterprises, rates, orders, invoices). Sin embargo `StaffController` comprueba explícitamente permisos de un dominio distinto: `staff.create`, `staff.update`, `staff.delete`. Hoy no se nota porque el rol `manager` tiene sincronizados absolutamente todos los permisos (`RolesAndPermissionsSeeder.php:107-109`), pero el rol `distributor` del propio seeder ya tiene `staff.*` sin `distributors.update` — y viceversa. Es una trampa de mantenimiento para cualquier rol futuro más granular.
   Archivos: `app/Http/Middleware/EnforcePanelPermission.php:37-44` (`DOMAIN_ALIASES`), `StaffController.php:122,162,213`.
   Fix: añadir `'staffs' => 'staff'` a `DOMAIN_ALIASES`, o cambiar el controller para usar `distributors.update`/`distributors.delete` de forma consistente con el resto del dominio.

4. **`attach()` sin validar existencia de IDs** — `CourseController::update` y `EnterpriseController::update` parten `$request->courses`/`$request->enterprises` (string separado por comas) y hacen `attach($id)` para cada uno sin comprobar que sea numérico ni que exista en `courses`/`enterprises`. Se confirmó contra el esquema real que `distributor_courses` y `distributor_enterprises` sí tienen FK constraint hacia `courses`/`enterprises`, así que un id inválido no corrompe datos pero sí dispara una `QueryException` no controlada (500) en vez de un 422 limpio. `RatesController::update` (mismo controller sibling) ya implementa el patrón correcto — ownership + `is_numeric($price)` — solo falta replicarlo aquí.
   Archivos: `CourseController.php:44-56`, `EnterpriseController.php:48-57` vs. `RatesController.php:40-56` (patrón correcto).

### 🟡 Funcional / Datos

5. Las pantallas de asignación de cursos/empresas y edición de tarifas comparten el mismo problema #1: no hay `FormRequest`, todo llega vía `$request->courses`/`$request->enterprises`/`$request->courses[$id]` sin tipado ni validación previa.
6. `InvoicesController::index` reutiliza la vista genérica `managers.views.invoices.invoices.index` (módulo global de Facturación) para el listado de facturas *de un distribuidor concreto*. La query ya está bien filtrada (`Distributor::slack($slack)->invoices()`), pero la vista no muestra en ningún lado a qué distribuidor pertenece el listado ni ajusta acciones como el botón "Reporte" (que apunta a `manager.invoices.report`, el reporte global). Contraste con `OrdersController::index`, que sí usa una vista propia bajo `distributors/orders/`.
7. El modelo `Distributor` tiene tres relaciones que apuntan a datos de órdenes con nombres casi idénticos y confusos: `orders()` → `OrderActivity`, `ordersActitity()` → también `OrderActivity` (nombre con typo), `ordersActititys()` → `Order` vía `hasManyThrough` (typo en el nombre, es la única realmente usada por `OrdersController`). El propio código deja un comentario advirtiendo del riesgo ("`Distributor::orders()` apunta a OrderActivity... `ordersActititys()` es la relación correcta"). Alto riesgo de que un desarrollador use la relación equivocada en el futuro.
   Archivo: `app/Models/Distributor/Distributor.php:104-130`.
8. `StaffController::store` no valida `password` en absoluto (ni longitud mínima ni presencia). Al no existir `FormRequest`, si el campo llega vacío el mutador del modelo lo hashea igual, pudiendo crear un usuario con contraseña vacía si el formulario se omite o se salta client-side.

### 🔵 UX / Consistencia

9. `distributors/create.blade.php` y `edit.blade.php` extienden `layouts.managers` (layout antiguo) en vez de `layouts.theme` + `core::components.card`/`alerts`, quedando visualmente desalineados de la convención `ui-patterns` ya adoptada en otros módulos migrados.
10. Ningún campo obligatorio (título, nit, dirección, email, gerente, soporte — todos `required: true` en jQuery Validate) muestra asterisco ni indicación visual de "obligatorio", inconsistente con el resto del panel.
11. Mensajes de validación client-side con erratas ("Debe contener al menos 100 caracter" para `maxlength`, debería ser "como máximo"; "Porfavor"; "regitrada" en vez de "registrada") en `create.blade.php` y `StaffController`'s mensajes de respuesta JSON.
12. Copy ambiguo en el select `enterprise_generate` ("Permisos empresa" con opciones "Si/No") — no queda claro qué habilita ese permiso sin leer el modelo de negocio.
13. Puntos positivos confirmados: no se encontró `{!! !!}` sobre contenido de usuario (los únicos usos son `Form::select()` de LaravelCollective, que escapa las opciones internamente), no hay `theme: 'bootstrap-5'`, no hay `ti ti-`, no hay `style=""` inline, y el botón "Filtros" es consistente (ícono + badge, sin texto) en las vistas que lo tienen.

### ⚪ Cobertura de tests

14. No existe ningún archivo bajo `tests/` que mencione "distributor" (búsqueda `find tests -iname "*distributor*"` vacía). Cero cobertura para: alta/edición de distribuidores, asignación de cursos/empresas, actualización de tarifas (incluida la lógica de ownership ya implementada en `RatesController`), alta/baja de personal (incluida la protección anti-escalada de privilegios de `RestrictsManageableUsers`), ni para los permisos diferenciados por rol (`distributor`, `support`, `accounting`) que ya definen acceso real a estas pantallas.

## Recomendación de prioridad

1. Crear los `FormRequest` faltantes para `Distributor` y `DistributorStaff` (store + update) — cierra el mayor hueco de validación server-side (#1).
2. Escribir tests Feature que fijen el comportamiento de seguridad ya existente (ownership de tarifas, roles gestionables, permisos por rol) antes de tocar nada más — hoy no hay red de seguridad (#14).
3. Alinear el permiso `staff.*` vs `distributors.*` en `EnforcePanelPermission`/`StaffController` (#3) y añadir `authorize()` explícito en las acciones de lectura (#2).
4. Replicar en `CourseController`/`EnterpriseController` la guarda numeric+exists que ya tiene `RatesController` (#4).
5. Homogeneizar `create`/`edit` de Distribuidor al layout y patrones UI estándar del resto del panel (#9, #10).
