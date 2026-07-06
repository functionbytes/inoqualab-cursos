# Auditoría de seguridad — Portales secundarios (v2)

> Fecha: 2026-06-13 · Alcance: controllers y modelos de los portales **distributor / enterprise / accounting / support** (monolito `App\`, NO modules).
> Método: 6 agentes read-only por área disjunta → verificación adversarial → **reality-check de esquema** (vs columnas reales en BD) → verificación manual + pruebas en navegador (test positivo + negativo) de cada fix implementado.

## Resumen

| Categoría | Hallazgos | Estado |
|-----------|-----------|--------|
| IDOR / ownership | 39 | 18 corregidos y verificados · 21 documentados (batch 2) |
| Escalada de privilegios / toma de cuenta | 3 | **3 corregidos** (crítico) |
| Bugs de correctitud | 44 | safe fixes aplicados · resto = código muerto (ver nota esquema) |
| Performance (orderBy ambiguo → 500) | 1 | corregido |

**Causa raíz del IDOR** (idéntica en todo el código): muchos métodos `edit/view/update/destroy/navegation/store` resuelven el recurso con `Model::slack($x)` tomando el slack del **request**, y lo usan/editan **sin filtrar por la entidad del portal autenticado**. El `index()` del mismo controller sí suele filtrar (`app('distributor')->enterprises()`, `app('enterprise')->users()`), pero los demás métodos no. Resultado: un distributor/enterprise/accounting/support puede **ver, editar, borrar o exportar datos de otro tenant** cambiando el slack en la URL.

**Patrón de fix** (aplicado): resolver el recurso a través de la relación del portal con `firstOrFail()`, o validar pertenencia con `whereExists` sobre la tabla pivote. El happy-path legítimo se preserva (el usuario sigue accediendo a SUS recursos → 200); el acceso cruzado da **404**.

---

## 🔴 CRÍTICO — Escalada / toma de cuenta (CORREGIDO)

| Archivo | Problema | Fix |
|---------|----------|-----|
| `Accountings/Settings/SettingsController::update` | Editaba email/nombre **y password** de CUALQUIER usuario (incl. manager) vía `User::slack($request->slack)` → toma de cuenta total | Usa `auth()->user()` (siempre el autenticado) |
| `Distributors/Settings/SettingsController::updateUser` | Igual: reseteo de password de cualquiera | `auth()->user()` |
| `Distributors/Settings/SettingsController::updateDistributor` + `updateNotifications` | Editaba cualquier distribuidor por slack | `app('distributor')` |
| `Supports/Users/UsersController::resetpassword` + `forgotpassword` | Un soporte podía resetear la contraseña de un **manager/support** | `$this->guardManageableUser(...)` (bloquea roles privilegiados) |

## 🟠 ALTO — IDOR cross-tenant (CORREGIDO y verificado en navegador)

Verificado con test positivo (propio → 200) + negativo (ajeno → 404):

- `Distributors/Enterprises/EnterprisesController` → `edit`, `navegation`
- `Distributors/Enterprises/StaffController` → `index`, `create`, `edit`, `update`, `store`, `destroy` (helpers `managedEnterprise` / `managedStaff` vía `enterprise_staff`)
- `Distributors/Enterprises/UserController` → 12 métodos (`managedEnterprise` / `managedUser` vía `enterprise_user`)
- `Enterprises/Users/CertificatesController` → `index`, `user`, `course`, `broad`
- `Enterprises/Users/ResultsController` → `index`, `view`, `download`

## 🟠 ALTO — IDOR batch 2 (CORREGIDO y verificado en navegador)

Mismo patrón y fix; verificado propio → 200 / ajeno → 404 (o AJAX con datos vs vacío):

- `Distributors/Enterprises/CourseController` (`view`, `destroy`, `destroyInscription`, `update`, `includes`, `progress`, `details`, `reasign`, `report`, `assign`, `index`) — helpers `managedEnterprise` / `managedInscription`
- `Distributors/Enterprises/ReassignController` (`all`, `single`, `reassignAll`, `reassignSingle`)
- `Distributors/Inscriptions/{Inscriptions,InscriptionsMassives}Controller` — `enroll`/`store` ahora usan `app('distributor')` (NUNCA el distribuidor del request) + validan empresa y membresía del usuario; AJAX `getCourses`/`getUsers` scopeadas
- `Distributors/Invoices/InvoicesController::detail/view` — `app('distributor')->invoices()`
- `Distributors/Users/{Certificates,Results}Controller` — `managedUser` / `assertManagedUser`
- `Distributors/Registers/RegistersController::store`
- `Enterprises/Enterprises/CoursesController` (`progress`, `details`) — `managedInscription`
- `Enterprises/Users/ReportController::generate` — usa `app('enterprise')->id`
- `Accountings/Enterprises/EnterprisesOrdersController`, `Accountings/Distributors/EnterprisesController`: `slack` ambiguo → calificado a `orders.slack` (fix del **500**)
- `Supports/Enterprises/StaffController::update` — aplica `guardManageableUser`

**Bug raíz adicional corregido:** `Enterprise::users()` tenía `orderBy('updated_at')` sin calificar → **500** en cualquier join (lo expuso un helper). Calificado a `users.updated_at`. (Igual que `Distributor::staffs()`.)

## 🟢 Bugs seguros (CORREGIDO)

- `Enterprise/EnterpriseUser::scopeDisabled` — lógica invertida (`where('available', 1)` → `0`).
- `Distributor::staffs()` — `orderBy('created_at')` sin calificar (ambiguo en join → 500) → `orderBy('users.created_at')`.

## ⚠️ Nota de esquema — bugs que NO se tocaron (código muerto)

El reality-check contra la BD confirmó que estas columnas/tablas **no existen**:
`orders.{distributor_id, course_id, status, order_type, total, enroll_expire}`, `orders_activity.{user_id, item_id}`, tabla `distributor_course`.

Por tanto, los métodos/relaciones del modelo `Order`, `OrderActivity`, `Distributor::ordersDistributor()`, `DistributorCourse` que filtran por esas columnas están **rotos pero son código muerto** (la app funciona en producción, no se invocan en los flujos vivos). "Arreglarlos" exige rediseñar el esquema/data-model → **riesgo alto, ROI nulo**. Se documentan pero **no se modifican**. Otros bugs de 500/null-handling (imports faltantes `UsersExport/CoursesExport`, `first_name/last_name`, dashboards con variables sin definir) quedan para batch 2 con verificación dedicada.

## Verificación

- `php -l` OK en los 10 archivos modificados · `vendor/bin/pint` passed.
- Pruebas en navegador (Chrome DevTools) con test positivo + negativo en distributor y enterprise: **propio 200 / ajeno 404** en edit, navegation, staff, certificación, resultados, broad.
- Sin regresiones: los listados y dashboards de los 4 portales siguen `200`.
