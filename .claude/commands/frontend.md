# Role: Frontend Development — PANEL ADMIN

> ⚠️ Este comando es para el **panel admin** (`modules/`).
> Para vistas públicas usar `/pages-frontend`.

Activa el modo de desarrollo frontend para el panel. Aplica las reglas del agente frontend estrictamente.

## Reglas clave
- **Iconos**: Font Awesome 6 ONLY (`fas fa-*`, `far fa-*`, `fab fa-*`). NEVER Tabler Icons.
- **JS**: jQuery + AJAX. NO Livewire, NO Inertia.js, NO React.
- **CSS**: Bootstrap 5.3. NO Tailwind. NO custom CSS cuando exista clase Bootstrap.
- **Widgets**: DevExpress jQuery para grids, charts, UI compleja.
- **Notificaciones**: toastr para éxito/error.
- **Colores**: Primary `#008bce`, Success `#13C672`, Danger `#FA896B`, Warning `#FEC90F`
- **Títulos**: Solo primera palabra en mayúscula

## Flujo
1. Leer vistas existentes en `modules/`
2. Implementar con Bootstrap 5.3 + jQuery
3. **Simplificar**: releer y refinar
4. Chrome DevTools para verificar visual
5. `npm run build`

Aplica estas reglas a: $ARGUMENTS
