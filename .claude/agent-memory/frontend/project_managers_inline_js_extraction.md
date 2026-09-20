---
name: project-managers-inline-js-extraction
description: Pattern used to remove inline <script>/<style>/style=""/onclick= from resources/views/managers/views — apply the same pattern to any panel view still using inline JS/CSS
metadata:
  type: project
---

Completed 2026-09-21: removed all inline `<script>`, `<style>`, `style=""`, and `onclick=`/`onchange=`/`onsubmit=` from `resources/views/managers/views/{enterprises,distributors,certifiers,newsletter,notifications,analytics}/**` (41 blade files, 41 new `.js` files, 12 new `.css` files under `public/managers/{js,css}/views/...`). See [[project_training_structure]] for the path convention.

**Pattern per file:**
- Inline `<script>...</script>` body → moved verbatim into `public/managers/js/views/{mirror-path}.js`, referenced via `<script src="{{ asset('managers/js/views/{mirror-path}.js') }}"></script>` inside `@push('scripts')`.
- Inline `<style>...</style>` → same mirroring into `.css`, linked via `@push('css')`.
- Blade-interpolated values the JS needed (`route(...)`, model attributes, booleans) → exposed as `data-*` attributes on the nearest wrapping element (usually the `<form>` or the page's outer `.widget-content` div, given an `id`), read in the extracted JS with `$el.data('...')`. jQuery auto-casts `data-is-new="true"` to a real boolean, no manual parsing needed.
- `onSubmit="return false"` on forms using jQuery Validate (`$form.validate({...})`) is redundant — Validate already intercepts native submission via `submitHandler`. Safe to drop outright.
- `onclick="fn(...)"` on buttons → replace with a `data-*` attribute (e.g. `data-key`, `data-format`) + a class (e.g. `.btn-export`) + `$(document).on('click', '.btn-export', function () { ... $(this).data(...) ... })` in the extracted JS.
- Static `style="width:40px"` etc. directly in Blade markup → new small CSS classes (e.g. `.col-checkbox { width: 40px; }`) added to the page's extracted CSS file. A `style=""` string **inside a JS string being concatenated into innerHTML at runtime** (common in the analytics dashboard, which builds huge HTML strings in JS) is NOT in scope — that lives in the `.js` file after extraction, not in the Blade file, so it doesn't violate the "no inline styles in Blade" rule.
- `@if(...)`/`@endif` wrapping JS blocks conditionally (e.g. only bind a handler `@if($campaign && $campaign->isDraft())`) → convert the condition to a `data-*` boolean and guard with `if (...)` in JS, since the whole script is now static (no longer templated per-request by Blade).

**Bugs found and fixed during extraction (not "business logic" — pre-existing dead/broken JS that would fail `node --check` or throw at runtime):**
- Several `create`/`edit` forms (e.g. `enterprises/enterprises/{create,edit}.blade.php`, `distributors/distributors/{create,edit}.blade.php`, `distributors/staffs/create.blade.php`) called `toastr.success(message, ...)` referencing an undeclared `message` var instead of `response.message` — fixed to `response.message` (this was breaking the success/redirect path entirely).
- `enterprises/enterprises/inscription.blade.php` had a genuine unbalanced-brace syntax error in the ajax `success` callback — fixed while extracting (required for `node --check` to pass).
- `enterprises/courses/import.blade.php` Dropzone init referenced an undeclared `token` var in `params: { _token: token }` (would throw on page load, breaking the dropzone every time) — changed to read the CSRF meta tag like the rest of the codebase.
- Left several harmless-but-dead patterns untouched (pure relocation, no fix) per scope discipline: `certifiers/edit.blade.php` deletes a thumbnail via `route('manager.settings.metadata.delete', ':id')` (looks like a copy-paste from an unrelated domain, but changing it would be a routing/business-logic change, out of scope).

**Verification commands used:** `grep -rn "<script\|<style\|style=\"|onclick=|onchange=|onsubmit=" resources/views/managers/views/{...}` (only `<script src="...">` externals should remain), `node --check` on every new `.js`, `php artisan view:clear`, `vendor/bin/pint --dirty` (Pint only touches `.php`, so it reports "passed" with no changes for a pure Blade/JS/CSS task like this one).
