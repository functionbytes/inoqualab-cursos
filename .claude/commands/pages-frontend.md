# Role: Frontend de Páginas Públicas

> ⚠️ Este comando es para el **frontend público** (`resources/views/pages/`).
> Para el panel admin usar `/frontend`.

Activa el modo de desarrollo para vistas públicas. Aplica las reglas de `rules/pages-views.md`.

## Reglas clave
- **Template**: Wellearn (Education & LMS, WebTend)
- **CSS**: `public/pages/css/style.css` — revisar clases existentes ANTES de escribir nueva CSS
- **Bootstrap**: 4.5.3 para layout/grid; JS del template puede ser v5 (verificar atributos)
- **Iconos**: Font Awesome 6 (`fas fa-*`, `far fa-*`, `fab fa-*`)
- **Colores** — SOLO del template Wellearn:
  - Oscuro: `#081A28` (botones primarios, headings)
  - Azul acento: `#008bce` (hover, links activos)
  - NUNCA usar `#90bb13` ni cualquier verde en pages
- **Botones**:
  - Primario: `class="btn"` o `class="theme-btn"`
  - Secundario: `class="btn btn-outline-brand"` (definido en style.css)
- **NO** DevExpress, NO toastr, NO `style=""` inline con colores

## Flujo
1. Leer vista existente y vistas hermanas en `resources/views/pages/`
2. Revisar `public/pages/css/style.css` para clases disponibles del template
3. Implementar usando clases del template Wellearn
4. Si se necesita nueva clase CSS → agregar al FINAL de `public/pages/css/style.css`
5. Verificar visualmente en el browser

## Estructura de views
```
resources/views/
  pages/
    views/          ← vistas principales (index, view, etc.)
    partials/
      sections/     ← secciones reutilizables por página
    includes/       ← header y footer del layout
  layouts/
    pages.blade.php ← layout principal (CSS/JS del template)
```

Aplica estas reglas a: $ARGUMENTS
