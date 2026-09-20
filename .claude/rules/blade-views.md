---
globs: "resources/views/{managers,supports,distributors,enterprises,accountings,customers}/**/*.blade.php"
---

# Blade View Rules — PANELES ADMIN Y PORTALES (managers/supports/distributors/enterprises/accountings/customers)

> ⚠️ Este proyecto es un monolito en `app/` y `resources/views/{dominio}/` — NO existe `modules/`.
> Estas reglas aplican a los paneles/portales protegidos. Para vistas públicas (`resources/views/pages/`) ver `rules/pages-views.md`.

- Icons: Font Awesome 6 ONLY (`fas fa-*`, `far fa-*`, `fab fa-*`). NEVER use Tabler Icons (`ti ti-*`)
- JavaScript: jQuery + AJAX. NEVER use Livewire or Inertia.js
- **NO `<script>` inline**: todo JS va en un archivo `.js` propio bajo `public/{perfil}/js/{misma-ruta-que-la-vista}.js`, cargado con `@push('scripts')<script src="{{ asset(...) }}"></script>@endpush`. Datos de Blade (`route()`, variables, `@json()`) se pasan vía atributos `data-*`, nunca interpolados dentro del `.js`
- **NO `<style>` inline ni `style=""`**: todo CSS va en un archivo `.css` propio bajo `public/{perfil}/css/{misma-ruta-que-la-vista}.css`, cargado con `@push('css')`. Excepción: plantillas renderizadas vía `Pdf::loadView()` (dompdf, `enable_remote=false`) pueden mantener CSS inline porque dompdf no carga `<link>` externos
- **NO `onclick=`/`onchange=`/`onsubmit=`/`onerror=` inline**: usar clases/`data-*` + `$(document).on('evento', '.selector', fn)` (event delegation). Excepción: `onerror` en `<img>` no hace bubbling, así que su fallback se maneja con un listener delegado en fase de captura (`addEventListener('error', fn, true)`), no con jQuery `.on()`
- Section titles: capitalize only first word (`Informacion basica`, NOT `Informacion Basica`)
- Use Bootstrap 5.3 classes over custom CSS. NEVER use `style=""` inline styles
- CSRF token in AJAX: `headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }`
- Handle 422 errors: parse `xhr.responseJSON.errors` for per-field messages
- Use `toastr` for success/error notifications
- Table actions: ALWAYS dropdown with `fa-ellipsis-vertical`, no icons in items, no `text-danger` on delete
- Modals: ALWAYS `modal-dialog-centered` with footer buttons w-100 stacked (primary mb-2 top, secondary bottom)
- select2: NEVER use `theme: 'bootstrap-5'` (CSS not loaded)
- Primary color: `#008bce` (Bootstrap Modernize — `--bs-primary`)
