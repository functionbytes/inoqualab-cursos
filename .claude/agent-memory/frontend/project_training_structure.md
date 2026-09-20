---
name: project-training-structure
description: Actual view/asset structure of the "training" Laravel project — NOT the modules/ pattern described elsewhere in this memory store
metadata:
  type: project
---

The "training" repo (Herd native, no Docker) is a **monolith**, not a modules/ package structure. Panel admin ("managers") views and assets live at:

- Blade views: `resources/views/managers/views/{domain}/{entity}/{action}.blade.php` (e.g. `resources/views/managers/views/enterprises/users/index.blade.php`)
- Shared layout: `resources/views/layouts/managers.blade.php` (has `@stack('css')` in `<head>` and `@stack('scripts')` before `</body>`) — DO NOT edit, it's already correct
- Shared partials: `resources/views/managers/includes/*.blade.php` (e.g. `bulk-toolbar-modal.blade.php`, `delete.blade.php`)
- JS assets: `public/managers/js/views/{domain}/{entity}/{action}.js` (mirrors the blade path, `.blade.php` → `.js`)
- CSS assets: `public/managers/css/views/{domain}/{entity}/{action}.css` (same mirroring)
- Reference via `asset('managers/js/views/...')` / `asset('managers/css/views/...')`, never `url()` for these (use `url()` only for pre-existing vendor libs under `public/managers/libs/`)

**Why:** the earlier memories in this file (Nestable2, Menu module, `@stack('styles')`) reference a `modules/ModuleName/resources/views/` layout that does not exist in this repo (confirmed against `CLAUDE.md`: "No existe `modules/`"). Those notes are stale/foreign to this project — likely bled in from a different codebase. Do not apply them here.

**How to apply:** when doing frontend work in `/Users/developerts/Herd/training`, always locate views under `resources/views/managers/views/`, never search for `modules/`. See [[project_managers_inline_js_extraction]] for the JS/CSS de-inlining pattern already applied across this tree.
