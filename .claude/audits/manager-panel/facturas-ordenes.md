# Auditoría: Facturas, Órdenes y Reportes (panel manager)

## Resumen

Alcance: `app/Http/Controllers/Managers/Invoices/*`, `app/Http/Controllers/Managers/Orders/*`,
`app/Http/Controllers/Managers/ReportController.php`, vistas en
`resources/views/managers/views/invoices/` y `.../orders/`, modelos `App\Models\Invoice\*` y
`App\Models\Order\*`, y las rutas correspondientes en `routes/managers.php`.

La autorización real de este dominio **no vive en los controllers** sino en el middleware
`App\Http\Middleware\EnforcePanelPermission`, que deriva el permiso Spatie del nombre de ruta
(`manager.invoices.*` → `invoices.{view|create|update|delete}`). Se verificó que los permisos
`invoices.*` y `orders.*` sí existen en `RolesAndPermissionsSeeder` (se generan para las 5
acciones estándar por entidad), por lo que la cobertura real de autorización es más sólida de lo
que sugiere la ausencia de `$this->authorize()` en los controllers — pero el patrón es frágil e
inconsistente con las reglas del propio proyecto (`rules/controllers.md`, `rules/policies.md`),
que exigen autorización explícita en el controller. Existen además `InvoicePolicy` y `OrderPolicy`
registradas en `AppServiceProvider` que **no se usan en ningún controller de este dominio**
(código muerto o solo aspiracional).

Se encontró un bug real de integridad financiera en `InvoicesController::store()`: el monto por
ítem se multiplica dos veces por la cantidad cuando se arma el desglose `invoice_items` (ver
Crítico #2). El total de cabecera de la factura sí es correcto porque se calcula desde
`order->total_order_amount` (fuente de verdad), pero el desglose por curso queda inflado para
líneas con `quantity > 1` (paquetes/bundles).

No hay Form Requests en ningún controller de este dominio (validación 100% inline), y no hay
tests para el dominio Orders del panel manager (sí existen para Invoices).

## Hallazgos

### 🔴 Crítico / Seguridad

1. **Autorización 100% implícita vía convención de nombre de ruta, sin defensa en profundidad** —
   `app/Http/Controllers/Managers/Invoices/InvoicesController.php` (métodos `index`, `print`,
   `view`, `details`, `edit`, `create`), `app/Http/Controllers/Managers/Orders/OrdersController.php`
   (métodos `index`, `view`, `edit`), `app/Http/Controllers/Managers/Orders/ReportController.php`,
   `app/Http/Controllers/Managers/Orders/ResumenController.php`,
   `app/Http/Controllers/Managers/Invoices/ReportController.php`. Ninguno de estos métodos llama
   `$this->authorize()` ni usa Policy. La única razón de que no sean de acceso libre es que
   `EnforcePanelPermission` deriva un permiso del nombre de ruta y ese permiso existe en el
   catálogo. Si alguien renombra una ruta (p. ej. quita el sufijo `.view`, o el action mapeado cae
   en un sufijo no reconocido tipo `pdf`/`generate`/`report` que dependen del **método HTTP** para
   decidir `view` vs `update` por defecto) el endpoint queda expuesto a cualquier usuario
   autenticado con rol manager, sin aviso ni test que lo detecte. Esto viola directamente
   `rules/controllers.md` ("Use `$this->authorize()` or policies for authorization") y
   `rules/policies.md` ("Skip `authorize()` en controllers" está en la lista de "NO usar"). Fix:
   añadir `$this->authorize('view', ...)` / `viewAny` explícito en cada método usando las Policies
   `InvoicePolicy`/`OrderPolicy` que ya existen pero están sin cablear.

2. **Duplicación de cantidad al calcular el desglose de factura (`invoice_items`)** —
   `app/Http/Controllers/Managers/Invoices/InvoicesController.php:255-277` (método `store`).
   `OrderItem::amount` ya es el **total de línea** (`unit * qty`), fijado así en
   `app/helpers.php::cartCheckoutLines()` (`$amount = (float) $unit * $qty;`) y confirmado en
   `CheckoutController.php:700` (`$orderItem->amount = $line['amount'];`). Sin embargo
   `InvoicesController::store()` vuelve a multiplicar:
   ```php
   $itemPrice = $orderItem->amount;       // ya es total de línea, no precio unitario
   $orderItemCount = $orderItem->quantity;
   $orderItemAmount = $itemPrice * $orderItemCount; // duplica la cantidad
   ```
   Para cursos el bug es invisible porque `qty` se fuerza a 1 en el checkout, pero para
   **bundles/paquetes con `quantity > 1`** el total por curso en `invoice_items` (usado para el
   desglose que ve el distribuidor) queda inflado por un factor de `qty`. El total de cabecera de
   la factura (`total_invoices_amount`) es correcto porque se toma de
   `$order->total_order_amount`, así que hay **inconsistencia entre el total mostrado y la suma del
   desglose** — el tipo de discrepancia que un distribuidor notaría y reclamaría. `InvoiceDetails`
   (mismo método, bloque de abajo) sí usa `$orderItem->amount` sin re-multiplicar, o sea que
   conviven dos cálculos distintos para el mismo dato dentro del mismo método. Fix: usar
   `$orderItem->amount` directamente (sin `* $orderItemCount`) al acumular en `$coursesItems`.

### 🟡 Funcional / Datos

3. **Sin control de estado sobre facturas/órdenes ya pagadas o emitidas** —
   `InvoicesController::update()` y `OrdersController::update()`
   (`app/Http/Controllers/Managers/Invoices/InvoicesController.php:145-171`,
   `app/Http/Controllers/Managers/Orders/OrdersController.php:103-125`). Ambos permiten cambiar
   `condition_id`/`method_id` sin verificar el estado actual — se puede revertir una factura de
   "Pagada" a "Generada" (o viceversa) sin dejar rastro de por qué, sin motivo obligatorio, y sin
   bloqueo tras haber generado el PDF (`manager.invoices.pdf`) o tras que el distribuidor ya la vio.
   No hay ningún check tipo `abort_if($invoice->condition->slug === 'pagada' && ...)`.

4. **Ramas muertas / lógica incompleta en `update()`** —
   `InvoicesController.php:151-156`:
   ```php
   if ($request->condition == 4) {
       $invoice->payment_at = Carbon::parse($request->payment);
   } elseif ($request->condition == 2) {
   } elseif ($request->condition == 3) {
   }
   ```
   Los bloques `elseif` vacíos no hacen nada — probablemente pensados para limpiar `payment_at`
   cuando la condición deja de ser "pagada", pero nunca se implementó. Resultado: si una factura
   pasa de condición 4 a condición 2, `payment_at` **conserva la fecha de pago anterior**, dejando
   el registro en un estado contradictorio (condición "no pagada" con fecha de pago poblada). El
   magic number `4` (condición "pagada") tampoco está resuelto contra `InvoiceCondition::slug` sino
   hardcodeado como ID, frágil ante reordenamientos del catálogo.

5. **Sin validación de existencia/tipo en `condition`/`methods`/`distributor`** — en los cuatro
   controllers (`InvoicesController::update/store`, `OrdersController::update`,
   `ReportController::generate` de ambos dominios) los parámetros llegan directo de
   `$request->condition`, `$request->methods`, `$request->distributor` sin `FormRequest` ni regla
   `exists:invoice_conditions,id` / `exists:invoice_methods,id`. Un ID inexistente no truena en el
   momento (los `BelongsTo` simplemente no resuelven), pero corrompe silenciosamente el registro
   (relación rota, dropdown roto en la vista de edición la próxima vez). Esto contradice
   `rules/form-requests.md` (validación explícita) y `rules/controllers.md` ("Form Request classes
   for ALL validation").

6. **Ningún controller de este dominio usa Form Request** — confirmado por grep: cero
   ocurrencias de `FormRequest` o `$request->validate()` en
   `Managers/Invoices/*` y `Managers/Orders/*`. Toda la validación (rango de fechas, existencia de
   condición/método) es manual con `if`/`try-catch` repetido casi idéntico en 4 archivos
   (`InvoicesController::store`, `Invoices/ReportController::generate`,
   `Orders/ReportController::generate`, y el parseo de rango en `Order::filterOrders`). Candidato
   claro a extraer a un `Rule`/`FormRequest` compartido (`DateRangeRequest`).

### 🔵 UX / Consistencia

7. **Duplicación de controllers "resumen" vs "report" en Orders** —
   `Orders/ReportController` y `Orders/ResumenController` son casi idénticos (mismos filtros:
   distributor/enterprise/condition/type/method/range), uno exporta a Excel y el otro renderiza una
   vista con los mismos datos vía `Order::filterOrders()`. Vale la pena evaluar si `ResumenController`
   debería ser simplemente una acción adicional de `ReportController` en vez de un controller
   paralelo con sus propias rutas y vistas (`orders/report/index.blade.php` vs
   `orders/resumen/index.blade.php` son casi el mismo formulario).

8. **`Form::select` con `{!! !!}` en 12 puntos** — `invoices/edit.blade.php`,
   `invoices/create.blade.php`, `orders/edit.blade.php`, `orders/resumen/index.blade.php`,
   `orders/report/index.blade.php`. Es el helper de LaravelCollective (`Form::select`) que escapa
   internamente las opciones, así que no es XSS explotable con los datos actuales (títulos de
   catálogos administrados solo por manager), pero es el único patrón de este dominio que usa
   `{!! !!}` — inconsistente con el resto del panel que ya no depende de LaravelCollective/Form
   helper en otros módulos auditados. No es urgente pero conviene homologar a `<select>` nativo con
   Blade puro si se está migrando el resto del panel.

9. **`GenerateController::generate()` tiene un chequeo `if (! $invoice) abort(404)` inalcanzable** —
   `app/Http/Controllers/Managers/Invoices/GenerateController.php:15-19`. `Invoice::slack()` ya
   hace `abort_unless($model !== null, 404)` dentro del scope (`app/Models/Invoice/Invoice.php:67-73`),
   así que nunca puede devolver `null` — o aborta antes, o siempre devuelve un modelo. Código muerto,
   confunde sobre el contrato real del scope.

### ⚪ Cobertura de tests

10. **Invoices tiene tests, Orders (panel manager) no tiene ninguno** —
    `tests/Feature/Invoices/InvoiceTest.php` cubre autorización básica (guest/no-manager
    redirigidos), creación de factura desde órdenes de un distribuidor, caso sin órdenes en rango,
    actualización de condición, vista de edición, y distribuidor inexistente (8 tests). Buena base,
    aunque **no cubre** el bug de duplicación de cantidad (#2) ni las ramas muertas de `update()`
    (#4) — ambos serían detectables con un test que use un `OrderItem` con `quantity > 1`.
    `OrdersController`, `Orders/ReportController` y `Orders/ResumenController` **no tienen ningún
    test** (`tests/Feature/Console/OrderCommandsTest.php` es de comandos de consola, no de este
    panel). Dado que este dominio mueve dinero, la ausencia total de cobertura en Orders es más
    grave que en otros módulos ya auditados.

## Recomendación de prioridad

1. Corregir la duplicación de cantidad en `InvoicesController::store()` (#2) — es el único bug que
   produce un monto incorrecto persistido en base de datos, y afecta directamente lo que un
   distribuidor ve facturado.
2. Cablear `$this->authorize()`/Policies explícitas en los métodos de solo-lectura de ambos
   dominios (#1) para no depender exclusivamente de la convención de nombres de ruta como única
   capa de defensa.
3. Resolver las ramas vacías de `update()` y decidir una regla de negocio explícita para
   bloquear/permitir reversión de condición en facturas pagadas (#3, #4).
4. Añadir Form Requests con `exists:` para condition/method/distributor (#5, #6).
5. Escribir tests Feature para `OrdersController`/`Orders/ReportController`/`ResumenController`,
   y un test específico para el caso `quantity > 1` en la generación de facturas (#10).
6. Limpieza menor: unificar `Orders/ReportController` y `ResumenController` (#7), eliminar el
   chequeo inalcanzable en `GenerateController` (#9).
