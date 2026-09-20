---
globs: "**/*.js,public/**/js/**/*.js"
---

# JavaScript Rules

- Use jQuery + AJAX for ALL dynamic interactions. NEVER use Livewire, Inertia, React, or Alpine.js
- **NO JS inline en Blade**: todo JS vive en un archivo `.js` propio bajo `public/{perfil}/js/...` (mismo path relativo que la vista), referenciado con `<script src="{{ asset(...) }}">`. Un `.js` estático no puede interpolar Blade (`{{ }}`, `route()`, `@json()`) — esos valores se pasan como atributos `data-*` en el HTML y se leen con `.data()`
- CSRF token header on every AJAX request: `'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')`
- Use event delegation for dynamic content: `$(document).on('click', '.selector', handler)`. Excepción: el evento `error` de `<img>` no hace bubbling, así que su fallback requiere `document.addEventListener('error', fn, true)` (fase de captura) en vez de `.on()`
- Use DevExpress jQuery widgets for data grids, charts, and complex UI
- Handle 422 validation errors: parse `xhr.responseJSON.errors`
- Use `toastr` for notifications (success, error, warning, info)
- Use `$.ajax()` for complex requests, `$.get()`/`$.post()` for simple ones
