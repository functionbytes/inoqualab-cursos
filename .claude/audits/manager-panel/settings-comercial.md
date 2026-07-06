# Auditoría: Settings — Comercial/Catálogo (panel manager)

Alcance: `BundlesController`, `CouponsController`, `CertifiersController`,
`Settings\CertificationsController`, `DepartmentsController`,
`Settings\TestimoniesController`, `Settings\SlidersController`,
`Settings\TrustedsController` y sus vistas en
`resources/views/managers/views/{settings/...,certifiers/...}`.

Auditoría estática (sin navegador, sin cambios de código).

## Resumen

El grupo de controllers está protegido en su mayoría por
`EnforcePanelPermission` (deriva el permiso `{dominio}.{accion}` del nombre de
ruta) más un `abort_unless(auth()->user()->can(...))` redundante dentro del
controller. Se confirmó en BD que **todos** los permisos `{dominio}.create|update|delete|view`
de estos 8 dominios existen (`certifications.*`, `sliders.*`, `trusteds.*`,
`bundles.*`, `coupons.*`, `testimonies.*`, `departments.*`, `certifiers.*`),
por lo que el middleware sí actúa como guardián real y no en modo fail-open.

Los hallazgos más importantes son (1) **XSS almacenado sin sanitizar** en
varios campos `description` renderizados con `{!! !!}` — incluido un caso
donde el HTML crudo se inyecta dentro del `value` de un `<input type="hidden">`,
lo cual es más explotable que un simple `{!! !!}` en un `<div>` — y (2) que
**Bundles no tiene ningún Form Request ni validación**, por lo que el precio
del paquete (`$bundle->price = $request->price`) se persiste tal cual llega
del frontend, sin comprobar que sea numérico ni ≥ 0. La validación de
**Cupones** (rango de fechas, tipo/monto de descuento, límite de usos), en
cambio, está bien resuelta a nivel de Form Request.

## Hallazgos

### 🔴 Crítico / Seguridad

1. **XSS almacenado vía `{!! !!}` sin sanitizar en 5 entidades** —
   `resources/views/managers/views/settings/coupons/edit.blade.php:35,143`,
   `.../settings/bundles/edit.blade.php:20,21,103`,
   `.../certifiers/edit.blade.php:112`, `.../certifiers/view.blade.php:67`,
   `.../settings/testimonies/edit.blade.php:14,57`,
   `.../settings/certifications/edit.blade.php:14,68`.
   Los modelos `Coupon`, `Bundle`, `Certifier`, `Certification` y `Testimonie`
   no aplican ningún sanitizador (se buscó `Purifier`/`clean(` en
   `app/Models` y no hay coincidencias — el `HTMLPurifier` perfil `content`
   que sí se aplicó recientemente al WYSIWYG del aula **no cubre estas
   entidades**). El `description` se guarda crudo desde el request y se
   imprime crudo. Riesgo: un manager con permiso de solo `create`/`update`
   sobre uno de estos dominios (ej. soporte editorial) puede inyectar
   `<script>` que se ejecuta en la sesión de otro manager con más privilegios
   al abrir la vista de edición/detalle (escalada de privilegios dentro del
   panel).
   - **Caso agravado**: en `testimonies/edit.blade.php:14` y
     `certifications/edit.blade.php:14` el HTML crudo se inyecta dentro de un
     atributo: `value="{!! $testimonie->description !!}"`. Un `"` en la
     descripción rompe el atributo y permite inyectar atributos/eventos
     arbitrarios (`" onmouseover="..."`), sin necesitar siquiera una etiqueta
     `<script>`.
   - Fix: aplicar el mismo mutator `HTMLPurifier` (perfil `content`) usado en
     el módulo de cursos a estos 5 modelos, y usar `{{ }}` en los `<input
     value>` (o `old()`/`e()` explícito) en vez de `{!! !!}`.

2. **`BundlesController` no valida absolutamente nada** —
   `app/Http/Controllers/Managers/BundlesController.php:80-143`. No existe
   `StoreBundleRequest`/`UpdateBundleRequest` (se confirmó con `find` sobre
   `app/Http/Requests/Managers`), y el controller usa `Illuminate\Http\Request`
   genérico sin `$request->validate()`. Esto contradice
   `.claude/rules/form-requests.md` ("NO usar... inline validation en
   controller") pero aquí ni siquiera hay validación inline: **no hay
   validación en absoluto**.
   - `price` (línea 88/122): `$bundle->price = $request->price;` — un
     manager (o un request forjado a la ruta, ya que solo depende del
     permiso `bundles.create`/`update`, no de sanidad del payload) puede
     enviar un precio negativo, no numérico, o un string, y se persiste tal
     cual. No hay recomputo/backend authority del precio a partir de los
     cursos incluidos, contradiciendo el patrón "server-authoritative" ya
     aplicado al checkout (ver memoria `project_storefront_money_paths`).
   - `start_date`/`expire_at` (líneas 93-94, 127-128):
     `Carbon::parse($request->start_date)` sin validar formato — un valor
     inválido lanza `InvalidFormatException` (500) en vez de un 422 con
     mensaje claro.
   - Fix: crear `StoreBundleRequest`/`UpdateBundleRequest` con
     `'price' => ['required','numeric','min:0']`,
     `'start_date' => ['required','date']`,
     `'expire_at' => ['required','date','after:start_date']`, y considerar
     recalcular/tope el precio del bundle en base a la suma de precios de los
     cursos incluidos (o al menos un rango máximo) en vez de confiar 100% en
     el valor enviado.

### 🟡 Funcional / Datos

3. **Permiso inconsistente en Certifications/Sliders/Trusteds: `settings.update` en vez del permiso de dominio** —
   `CertificationsController::update/store/destroy/storeThumbnails/deleteThumbnails`,
   `SlidersController::store/update/destroy/storeThumbnails/deleteThumbnails`,
   `TrustedsController::update/store/destroy/storeThumbnails/deleteThumbnails`
   llaman todos `abort_unless(auth()->user()->can('settings.update'), 403)`,
   mientras que `EnforcePanelPermission` deriva de la ruta (`manager.certifications.store` →
   `certifications.create`, `manager.sliders.destroy` → `sliders.delete`,
   etc.) un permiso **distinto y más granular**, que también existe en BD
   (verificado con `Permission::where('name', ...)->exists()`). Resultado: un
   usuario necesita **ambos** permisos (`certifications.update` Y
   `settings.update`) para poder guardar, lo que rompe el modelo de permisos
   granulares por entidad usado en el resto del módulo (`testimonies.*`,
   `certifiers.*`, `bundles.*`, `coupons.*`, `departments.*` sí usan el
   permiso de su propio dominio). Un manager con rol "editor de contenido"
   al que se le asignaron solo `certifications.create/update/delete` (sin
   `settings.update`) recibirá 403 al intentar guardar, aunque el middleware
   ya lo autorizó.
   - Fix: reemplazar `settings.update` por `{dominio}.{accion}` en estos tres
     controllers, igual que en `TestimoniesController`.

4. **Cupones: no se valida que `end_date` sea posterior a `start_date`** —
   `CouponsController::store/update` (líneas 121-127 / 159-165). El parseo de
   `date_var` (`explode(' - ', ...)`) solo verifica que haya 2 partes; no
   valida que la fecha final sea mayor a la inicial, ni el formato dentro de
   `Form Request`. Un cupón guardado con fechas invertidas quedaría
   inconsistente con la lógica de disponibilidad que usa el carrito (ver
   memoria `project_storefront_money_paths` — el carrito valida "cupón
   available"). No es explotable por usuarios finales (solo managers crean
   cupones) pero sí es un vector de error humano con impacto monetario. Fix:
   agregar validación cruzada de fechas en el Form Request (o parsear
   `date_var` ahí en vez de en el controller) y usar `Carbon::createFromFormat`
   con manejo de excepción en vez de `strtotime` silencioso.

5. **`Bundle::courses()->attach($key, ['course_id' => $id])` es código
   frágil que depende de un detalle interno de Eloquent** —
   `BundlesController::update` línea 100-102 y `store` línea 131-133. El
   primer argumento de `attach()` es la clave del pivote relacionado; aquí se
   pasa `$key` (el índice secuencial 0,1,2... del `foreach` sobre el array
   explotado), no el id real del curso, y luego se fuerza `course_id` real
   vía el array de atributos pivote. Esto **funciona hoy** solo porque
   `array_merge($baseRecord, $attributes)` en
   `formatAttachRecord()` hace que los atributos pivote sobrescriban la clave
   base — pero es una dependencia implícita de una función interna de
   Illuminate, no documentada, muy fácil de romper en una futura actualización
   de Laravel o de quien edite el código sin conocer este detalle. Fix:
   `$bundle->courses()->attach(collect(explode(',', $request->courses))->filter()->unique()->all())`.

6. **Sin Form Requests en `DepartmentsController` ni `CertifiersController::store/update`
   (solo en subida de archivos)** — `DepartmentsController::store/update`
   (líneas 63-97) y `CertifiersController::store/update` (líneas 79-119) usan
   `Illuminate\Http\Request` sin ninguna regla de validación (`title`,
   `firstname`, `lastname`, `identification`, `profession` se asignan
   directos). No es tan grave como Bundles (no maneja dinero) pero permite
   guardar registros con `title`/`firstname` vacíos o excesivamente largos
   (columna sin límite aplicado a nivel app), y contradice
   `.claude/rules/form-requests.md`.

### 🔵 UX / Consistencia

7. **Estilos inline (`style="..."`)** en
   `resources/views/managers/views/settings/bundles/index.blade.php:91,94`,
   `.../settings/bundles/edit.blade.php:20,21` (`style="display: none"` en
   textareas ocultas — patrón repetido en varias vistas del proyecto para
   inicializar editores WYSIWYG, revisar si se puede resolver con una clase
   `.d-none` en vez de inline) y
   `.../settings/sliders/index.blade.php:70`.
   Contradice `.claude/rules/blade-views.md` ("NUNCA usar `style=""`
   inline").

8. **Confirmado: sin Tabler Icons y sin `theme: 'bootstrap-5'` de select2**
   en las 8 vistas auditadas — cumple la convención del proyecto (positivo,
   ver sección de Positivos).

### ⚪ Cobertura de tests

9. **Solo 2 de 8 controllers tienen test Feature**:
   `tests/Feature/Managers/CouponsTest.php` y
   `tests/Feature/Managers/CertifiersTest.php`. No existe cobertura para
   `Bundles`, `Certifications`, `Departments`, `Testimonies`, `Sliders`,
   `Trusteds` — ninguno de sus CRUDs, autorizaciones ni subidas de imagen
   están probados.

10. **`CouponsTest` no prueba los casos límite de descuento** — no hay ningún
    test que envíe `amount` negativo, `amount > 100` con `type=1` (porcentaje),
    o fechas invertidas, para confirmar que el `422` de
    `StoreCouponRequest`/`UpdateCouponRequest` realmente bloquea el abuso.
    Dado que la propia regla de validación (`max:100` condicional) está bien
    escrita, sería fácil (y valioso) agregar
    `test_coupon_percent_amount_over_100_is_rejected()` y
    `test_coupon_amount_negative_is_rejected()` para evitar regresiones.

11. **Ninguna de las 5 subidas de imagen (bundles/certifiers thumbnails+signatures/
    sliders/trusteds) tiene test de tipo MIME/tamaño**, a pesar de que
    `StoreCertifierFileRequest` sí valida correctamente
    (`image`, `mimes:jpeg,png,jpg,webp`, `max:5120`). `BundlesController::storeThumbnails`
    (línea 175-187) **no usa ningún Form Request** — solo comprueba
    `hasFile('file') && isValid()`, sin restringir mimes ni tamaño máximo,
    a diferencia de Certifiers/Certifications/Sliders/Trusteds que sí tienen
    `Store*Request` dedicado con esas reglas. Es la única entidad de las 8
    con subida de imagen sin control de tipo/tamaño de archivo.

## Recomendación de prioridad

1. **Ahora**: sanitizar con `HTMLPurifier` (perfil `content`, mismo patrón ya
   aplicado al aula) los campos `description` de `Coupon`, `Bundle`,
   `Certifier`, `Certification`, `Testimonie`; y cambiar `{!! !!}` dentro de
   atributos `value="..."` por interpolación escapada (hallazgo #1).
2. **Ahora**: crear `Store/UpdateBundleRequest` con validación de `price`
   numérico ≥ 0 y fechas, y un Form Request para `storeThumbnails` de Bundles
   con `mimes`/`max` (hallazgos #2 y #11).
3. **Esta semana**: unificar el permiso usado en
   Certifications/Sliders/Trusteds a `{dominio}.{accion}` en vez de
   `settings.update` (hallazgo #3) — riesgo de 403 falsos en producción con
   roles granulares.
4. **Esta semana**: agregar Form Requests a Departments y Certifiers
   store/update (hallazgo #6), y validar `end_date > start_date` en cupones
   (hallazgo #4).
5. **Backlog**: reescribir el `attach()` de Bundles de forma explícita
   (hallazgo #5), limpiar `style=""` inline (hallazgo #7), y sumar tests
   Feature para los 6 controllers sin cobertura + casos límite de cupón
   (hallazgos #9, #10).

## Positivo

- `StoreCouponRequest`/`UpdateCouponRequest` validan correctamente el
  descuento: `min:0` siempre, y `max:100` condicional solo cuando
  `type === '1'` (porcentaje) — exactamente el control que se pidió revisar,
  y está bien resuelto salvo el gap de fechas (#4).
- `EnforcePanelPermission` funciona como se documentó en memoria: los
  permisos de dominio (`bundles.*`, `coupons.*`, etc.) existen todos en BD,
  por lo que no hay fail-open real en estas 8 rutas.
- `CertifiersController` (thumbnails/signatures) y
  `CertificationsController`/`SlidersController`/`TrustedsController`
  (thumbnails) sí usan Form Requests dedicados con `mimes`+`max:5120` para
  las subidas de imagen — el único hueco es `BundlesController::storeThumbnails`.
- Ninguna de las 8 vistas usa Tabler Icons ni `theme: 'bootstrap-5'` en
  select2 — cumplen la convención del proyecto.
- `TestimoniesController` es el único de los tres "settings.update" que en
  realidad sigue el patrón correcto (`testimonies.create/update/delete`),
  útil como referencia para corregir el hallazgo #3.
