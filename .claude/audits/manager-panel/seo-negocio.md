# Auditoría: SEO — Alertas, Meta, Redirects, Reportes (panel manager)

**Alcance**: `SeoAlertsController`, `SeoMetaController`, `SeoRedirectController`, `SeoReportController` (`app/Http/Controllers/Managers/Seo/`), sus vistas en `resources/views/managers/views/seo/{alerts,metas,redirects,report}` y las rutas `manager.seo.{alerts,metas,redirects,report}.*` en `routes/managers.php`. Auditoría estática, sin fixes ni navegador.

## Resumen

Los 4 controllers están cubiertos por el middleware de convención `panel.permission` (`EnforcePanelPermission`), que deriva el permiso Spatie `seo.{view|create|update|delete}` del nombre de ruta — no hay `authorize()` explícito, pero es el patrón estándar del proyecto y funciona correctamente (confirmado contra `RolesAndPermissionsSeeder`: `seo` está en `ENTITIES`). El hallazgo más importante es que **`SeoRedirectController::store()`/`update()` no impiden crear un redirect que apunta a sí mismo o a otro que cierre un ciclo** (A→A o A→B→A); `HandleSeoRedirects` (middleware global, fuera de `/panel`, `/manager`, `/api`) no tiene protección de profundidad y reenvía cada request en un único hop, por lo que un ciclo se traduce en un bucle infinito de redirecciones HTTP para cualquier visitante público. La detección de cadenas (`RedirectChainDetector`) existe pero es una acción manual (botón "detectar cadenas"), no una validación en el guardado. Segundo hallazgo relevante: `SeoService::render()` (usado en el `<head>` público vía `@seoTags`) escapa correctamente `title`/`description`/OG/Twitter con `e()`, pero el bloque JSON-LD de `schema_custom` se vuelca con `json_encode(..., JSON_UNESCAPED_SLASHES)`, lo que permite que un valor con `</script>` rompa el tag y ejecute HTML/JS arbitrario en las páginas públicas — el campo se valida solo como `'json'` (sintaxis), sin sanear contenido.

## Hallazgos

### 🔴 Crítico / Seguridad

1. **Redirect loop sin protección en `SeoRedirectController::store()`/`update()`** (`app/Http/Controllers/Managers/Seo/SeoRedirectController.php:39-55,64-81`) — No hay regla que impida `source_path === target_path` (auto-loop inmediato) ni verificación contra cadenas existentes al guardar. `HandleSeoRedirects` (`app/Http/Middleware/HandleSeoRedirects.php:25-37`) resuelve un único hop por request sin límite de profundidad ni detección de ciclo, así que un A→A o A→B→A activo produce `ERR_TOO_MANY_REDIRECTS` para cualquier visitante en el sitio público. `RedirectChainDetector` (`app/Services/RedirectChainDetector.php`) solo se ejecuta bajo demanda vía `/redirects/detect-chains` y `/redirects/resolve-chains`, nunca en el flujo de guardado. Fix sugerido: regla de validación `target_path != source_path` + chequeo de ciclo (reusando `RedirectChainDetector::detect()`) antes de `create()`/`update()`.

2. **Inyección HTML/JS en `<head>` público vía `schema_custom` (JSON-LD)** (`app/Services/SeoService.php:267-272` junto con `SeoMetaController::update` línea 93: `'schema_custom' => ['nullable', 'json']`) — El render usa `json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)`. `JSON_UNESCAPED_SLASHES` deshabilita el escape de `/`, por lo que un valor de cadena dentro del JSON que contenga literalmente `</script><script>...</script>` rompe el `<script type="application/ld+json">` e inyecta markup/JS ejecutable en cualquier página pública que cargue ese meta. La validación solo exige JSON sintácticamente válido, no sanea el contenido. Contrasta con el resto de `render()`, que sí escapa consistentemente con `e()`. Riesgo real aunque requiera el permiso `seo.update` — es el único punto de la auditoría donde el panel puede inyectar código no confiable directamente al público sin pasar por HTMLPurifier (a diferencia del WYSIWYG de blog/curso, ya saneado según el historial de commits).

### 🟡 Funcional / Datos

3. **Ningún control de colisión entre `source_path` y rutas reales de la app** (`SeoRedirectController::store()`, líneas 39-46) — `HandleSeoRedirects` solo excluye `/panel/`, `/manager/`, `/api/`, `/_*` y assets estáticos (línea 44-48 del middleware); cualquier otra ruta activa (`/courses`, `/checkout`, `/login`, etc.) puede recibir un redirect que la deja inalcanzable, sin ningún aviso en el formulario de creación/edición. Sugerencia: al guardar, resolver `source_path` contra el router (`Route::getRoutes()->match()`) y mostrar advertencia (no necesariamente bloquear, porque redirigir rutas legítimas movidas es un caso de uso válido).

4. **`status_code` inconsistente entre flujos de creación** — `store()`/`update()` solo permiten `301,302` (`in:301,302`, líneas 42/67), pero `importHtaccess()` acepta `301,302,307,308` (línea 246) y persiste registros con código no soportado por la validación normal de edición: si luego se edita ese redirect vía UI, el `update()` fallará porque `307`/`308` no pasan `in:301,302`, aunque el registro ya exista con ese valor. Bug de consistencia, no de seguridad.

5. **`importJson()` acepta cualquier `seoable_type` sin verificar que sea una clase Eloquent válida** (`SeoMetaController::importJson`, líneas 321-360) — a diferencia de `import()` (CSV), que sí resuelve `$typeMap` contra `get_declared_classes()` (líneas 226-231), `importJson` usa `$row['seoable_type']` directamente como polymorphic type sin whitelist. Permite crear `SeoMeta` con `seoable_type` arbitrario (string libre), rompiendo la relación morphTo y potencialmente `class_basename()` en reportes/exports con clases inexistentes.

6. **`translateMeta()` no falla si `DEEPL_API_KEY` cambia entre request** — menor: usa `config('services.deepl.key', env('DEEPL_API_KEY', ''))` (`SeoMetaController.php:459`) — el segundo argumento de `config()` como default nunca se evalúa si la key de config existe (comportamiento correcto), pero mezclar `env()` fuera de `config/` contradice la convención del proyecto (`env() outside config` — ver checklist). Debería resolverse solo con `config('services.deepl.key')` y definir el fallback en `config/services.php`.

### 🔵 UX / Consistencia

7. **Validación inline (`$request->validate()`) en los 4 controllers en vez de Form Requests** — `SeoMetaController::update/bulkDestroy/inlineUpdate/import/importJson/keywordSuggestions/translateMeta/createLocale`, `SeoRedirectController::store/update/bulkDestroy/importHtaccess`. Es un patrón extendido en el módulo SEO completo (`Seo404LogController`, `SeoIndexNowController`, `SeoTemplateController`, etc. también lo hacen), así que no es exclusivo de estos 4, pero contradice la regla del proyecto (`rules/controllers.md`, `rules/form-requests.md`). Ningún método usa `Store{Entity}Request`/`Update{Entity}Request`.

8. **Límites de longitud SEO están bien acotados pero sin feedback proactivo en UI** — `SeoMetaController::update()` valida `title` `max:70` y `description` `max:170` (más estrictos que el estándar 60/160, razonable dado el sufijo de sitio). Sin embargo, esto solo se conoce por el 422 al guardar; sería mejor UX un contador de caracteres en tiempo real en `metas/edit.blade.php` (no se pudo confirmar sin ver el JS completo de esa vista, pero no se detectó lógica de contador en el grep de patrones).

9. **Inline `style=""` disperso en las 4 vistas** — múltiples usos en `metas/index.blade.php`, `metas/edit.blade.php`, `metas/hreflang.blade.php`, `metas/import-json.blade.php`, `report/index.blade.php`, `redirects/analytics.blade.php` (anchos fijos, `cursor:pointer`, alturas de progress bar, `display:none` inicial). Contradice `rules/blade-views.md` ("NEVER use `style=\"\"` inline styles"). La mayoría son casos triviales (ancho de columna, altura de barra de progreso) que deberían moverse a clases utilitarias.

10. **Sin hallazgos de iconos Tabler ni `theme: 'bootstrap-5'` en select2** en estas 4 subcarpetas — cumple la convención.

### ⚪ Cobertura de tests

- `tests/Feature/Managers/Seo/SeoManagementTest.php` cubre **solo** `SeoRedirectController` (crear, validar status code invalido, update, toggle/delete, bulk-destroy) y `SeoTemplateController` (fuera de alcance de esta auditoría).
- `tests/Feature/Managers/Seo/SeoLogsRobotsTest.php` cubre `Seo404LogController::createRedirect` (atomicidad) y `SeoRobotsController` — ninguno de los 4 controllers en alcance.
- `tests/Feature/Managers/Seo/GscCallbackTest.php` cubre el callback de Google Search Console — fuera de alcance.
- **`SeoAlertsController`: 0% cobertura** (index, acknowledge, acknowledgeAll, destroy sin ningún test).
- **`SeoMetaController`: 0% cobertura** (index/edit/update/destroy/bulkDestroy/inlineUpdate/export/import/exportJson/importJson/keywordSuggestions/hreflangIndex/translateMeta/createLocale — controller con más superficie de la auditoría, sin ningún test).
- **`SeoReportController`: 0% cobertura** (index/export).
- Ningún test cubre el escenario de redirect loop (hallazgo #1) ni la importación JSON con `seoable_type` arbitrario (hallazgo #5).

## Recomendación de prioridad

1. Bloquear ciclos de redirect en el guardado (#1) — impacto directo en disponibilidad del sitio público, fix acotado (una regla de validación + una llamada a `RedirectChainDetector::detect()`).
2. Corregir el escape de `schema_custom` (`JSON_UNESCAPED_SLASHES` → quitarlo, o escapar `</` manualmente) (#2) — único vector de inyección real hacia el público en este alcance.
3. Añadir tests de `SeoMetaController` y `SeoAlertsController` (cobertura ⚪) antes de tocar el código, dado que hoy cualquier refactor en esos controllers no tiene red de seguridad.
4. Resto de hallazgos (#3–#9) son mejoras incrementales de robustez/consistencia, sin urgencia.
