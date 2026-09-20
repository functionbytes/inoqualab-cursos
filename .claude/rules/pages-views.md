---
globs: "resources/views/pages/**/*.blade.php,resources/views/layouts/pages.blade.php"
---

# Blade View Rules — FRONTEND PÚBLICO (pages/)

> ⚠️ Estas reglas aplican SOLO a `resources/views/pages/` y `resources/views/layouts/pages.blade.php`.
> Para el panel admin (`modules/`) ver `rules/blade-views.md`.

## Template
**Wellearn** — Education & LMS HTML Template (WebTend)

## Stack
- CSS: `public/pages/css/style.css` + `public/pages/css/bootstrap-4.5.3.min.css`
- JS: jQuery 3.6.0 + `bootstrap.min.js` + plugins del template (slick, magnific-popup, WOW, etc.)
- Iconos: Font Awesome 6 (`fas fa-*`, `far fa-*`, `fab fa-*`)
- NO DevExpress, NO toastr (usar modales Bootstrap o alerts del template)

## Colores del template (NO usar `#90bb13` en pages)
| Token               | Valor       | Uso                                  |
|---------------------|-------------|--------------------------------------|
| Oscuro principal    | `#081A28`   | Fondo `.btn`, headings, texto oscuro |
| Azul hover/acento   | `#008bce`   | Hover de botones, links activos      |
| Texto secundario    | `#5A7093`   | Descripciones, meta info             |
| Texto suave         | `#8D9DB5`   | Precio tachado, placeholders         |
| Borde suave         | `#081A2812` | Separadores, bordes de listas        |

## Botones
- **Primario sólido**: `class="btn"` → fondo `#081A28`, texto blanco
- **Secundario contorno**: `class="btn btn-outline-brand"` → borde `#081A28`, fondo transparente, hover oscuro
- **Theme sólido**: `class="theme-btn"` → fondo `#081A28`, hover `#008bce`
- **Theme contorno**: `class="theme-btn style-three"` → transparente con borde
- NUNCA usar `style=""` inline para colores — agregar clase a `style.css` si se necesita

## Acordeones
- IDs SIEMPRE únicos: `id="collapse{{ $item->id }}"` (NO `id="collapseOne"` fijo)
- Atributo heading también único: `id="heading{{ $item->id }}"`
- Primer ítem: `class="accordion-button"` + `aria-expanded="true"` + div con `collapse show`
- Resto de ítems: `class="accordion-button collapsed"` + `aria-expanded="false"` + div con `collapse`

## Imágenes de cursos (Spatie Media Library)
- Thumbnail: `$model->getFirstMediaUrl('thumbnail')` con fallback a imagen default
- Preview curso: `asset('images/course/' . $course->preview_image)`
- Fallback de imagen rota: clase `js-img-fallback` + `data-fallback-src="{{ asset(...) }}"` (o `data-fallback-action="hide-sibling"` para el patrón "ocultar imagen y mostrar el ícono placeholder hermano"). El listener global vive en `public/pages/js/layout.js` — NUNCA usar `onerror=""` inline

## JS y CSS
- **NO `<script>`/`<style>` inline**: extraer a `public/pages/js/{misma-ruta}.js` / `public/pages/css/{misma-ruta}.css`, cargados vía `@push('scripts')` / `@push('css')` (el layout `layouts/pages.blade.php` ya expone ambos stacks)
- **NO `onclick=`/`onchange=`/`onsubmit=` inline**: event delegation jQuery (`$(document).on('evento', '.selector', fn)`)
- Datos de Blade que el JS necesita → atributos `data-*`, nunca interpolados en el `.js`

## Evitar
- `#90bb13` (color del panel admin — nunca en pages)
- `#008bcd` o cualquier azul como color de acción principal (es solo hover)
- Inline styles con colores hardcoded
- Clases de Bootstrap 5 utility que no estén en bootstrap-4.5.3 (`fw-bold`, `fs-*`, `gap-*`, `me-*`, `pe-*`)
