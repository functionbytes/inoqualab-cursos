# Detail Form Rules — crear/editar UNA entidad, en cualquier panel/portal admin

Todo formulario nuevo de crear/editar una sola entidad (`create.blade.php`,
`edit.blade.php`, o un `setting.blade.php`/`index.blade.php` de una sola
pantalla de configuración) en `resources/views/{managers,supports,distributors,
enterprises,accountings}/` usa por defecto el layout de 2 columnas: formulario
a la izquierda + sidebar de ayuda contextual a la derecha. No reinventar el
layout viejo de una sola card ancha (`col-lg-12 d-flex align-items-stretch` /
`card w-100`) en vistas nuevas.

Para el patrón de **listados** (`index.blade.php` con `<table>`) ver
`.claude/rules/data-tables.md` — es un patrón distinto, no se mezclan.

## Patrón

```blade
<div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">
        <form ...>
            @csrf {{-- o {{ csrf_field() }} --}}

            <div class="card">

                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">{Título de sección}</h6>
                    <p class="text-muted mb-3">{descripción breve de la sección}</p>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">{Campo} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" ...>
                            <small class="text-muted d-block mt-1">{ayuda opcional del campo}</small>
                        </div>
                        {{-- más campos --}}
                    </div>
                </div>

                <hr class="my-0"> {{-- separador entre secciones de la MISMA card --}}

                <div class="card-body">{{-- siguiente sección --}}</div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Guardar</button>
                </div>

            </div>
        </form>
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Sobre {tema}</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">{tip breve y VERIFICADO — ver regla abajo}</p>
            </div>
        </div>
    </div>

</div>
```

Referencia canónica (la más completa, con 4 secciones y sidebar de 3 tarjetas):
`resources/views/managers/views/settings/system/uploading.blade.php`.

## Reglas

- **Una sola card** en la columna izquierda; secciones separadas con
  `<hr class="my-0">` entre `card-body`, no con `card w-100` + `border-top`
  repetido (patrón viejo).
- **Labels**: `form-label fw-semibold` (nunca `control-label col-form-label`,
  que es el patrón viejo).
- **Botón guardar**: `btn btn-primary w-100` dentro de `card-footer` (nunca
  `btn-info px-4 waves-effect waves-light`, que es el patrón viejo).
- **Sidebar corto**: 1-3 párrafos cortos por tarjeta. Solo afirmar lo que se
  pueda verificar con grep al modelo/controller/migración — nunca inventar
  reglas de negocio. Si no hay nada verificable que decir, omitir el sidebar
  antes que rellenarlo con paja.
- **No tocar** `name=`, `id=`, `route(...)`, `asset(...)`, ni ningún atributo
  `data-*` que el `.js` asociado (mismo nombre en `public/{perfil}/js/views/...`)
  use como selector — leer el `.js` antes de reestructurar el `.blade.php`. Es
  una reestructuración visual, no un rediseño de lógica.
- **Bug de copy-paste conocido**: varias vistas viejas tienen, en una sección
  "Imagen"/"Foto"/"Logo" que en realidad NO es una foto de perfil, el texto
  genérico mal copiado *"Este espacio está diseñado para que puedas actualizar
  y modificar la foto de tu perfil..."*. Si aparece, corregirlo con una
  descripción real de qué es esa imagen (verificar con grep al modelo/vista
  pública antes de escribir, no inventar).

## ⚠️ Bug del compilador de Blade con `@json([...])` — aplica a `data-config`/`data-urls`

`@json([...])` con un array literal de **más de una clave de nivel superior**
(separadas por coma) rompe el compilador de Blade → 500 garantizado en toda la
vista (causó caída de 42 vistas el 20-sep-2026, y reapareció en vistas nuevas
varias veces desde entonces). Ver `[[reference_blade_json_directive_bug]]` en
memoria del agente para el detalle completo.

- **Seguro**: mover el array a una variable con `@php ... @endphp` y pasar
  `@json($variable)`:
  ```blade
  data-config='@php $__jsonInline1 = [
      "routes" => [ "a" => route("x"), "b" => route("y") ],
  ]; @endphp@json($__jsonInline1)'
  ```
- **Inseguro** (nunca escribir esto con más de una clave de nivel superior):
  `data-config='@json(["a" => ..., "b" => ...])'` directo.
- Una sola clave de nivel superior en `@json([...])` sí es segura tal cual.
- Verificación tras tocar cualquier archivo con `@json(`:
  ```bash
  php artisan tinker --execute="\$p=base_path('resources/views/RUTA.blade.php'); app('blade.compiler')->compile(\$p); echo app('blade.compiler')->getCompiledPath(\$p);"
  # luego: php -l <ruta_impresa>   → debe decir "No syntax errors detected"
  ```

## Excepciones legítimas (no forzar el patrón)

- **Selectores visuales tipo grid** (tarjetas con miniatura + descripción,
  varias opciones lado a lado) — el ancho de 8/12 columnas las aprieta.
  Ejemplo: `resources/views/managers/views/settings/portal/setting.blade.php`.
- **Matrices de permisos / checkboxes en grilla ancha** — necesitan ancho
  completo para ser usables. Ejemplo: `resources/views/managers/views/settings/roles/form.blade.php`.
- **Editores de código** (CodeMirror/HTML) — necesitan ancho completo.
  Ejemplo: `mailer/components/*`, `mailer/templates/*` (create/edit/versions).
- **Vistas de solo lectura** (detalle de orden/factura tipo recibo, con tabla
  de ítems y totales) — no son formularios, son documentos. Ejemplo:
  `resources/views/managers/views/orders/orders/view.blade.php`.
- **CRUD por modal** (crear/editar dentro de un `<div class="modal">` en el
  propio `index.blade.php`, no en una página separada) — el patrón de 2
  columnas es para páginas completas, no modales. Ejemplo: capítulos/clases/
  anuncios de curso, quiz/examen, SEO Redirects.
- **Varias tarjetas con guardado independiente** (cada una su propio `<form>`
  y botón, sin fusionar) — SÍ llevan el layout de 2 columnas (todas las cards
  en la izquierda, sidebar a la derecha), pero NO se fusionan en un solo
  `<form>`. Ejemplo: `resources/views/managers/views/settings/seo/index.blade.php`.
- **`print.blade.php`** (plantillas renderizadas vía `Pdf::loadView()`,
  dompdf) — mantienen su propio layout con CSS inline, no aplica ninguna
  regla de este archivo (ver excepción ya documentada en `blade-views.md`).

## Antes de dar por huérfano o por terminado un archivo

- Un archivo puede "verse" como el CRUD real de una entidad y no serlo: el
  controller puede renderizar una vista con OTRO path (duplicados de una
  reorganización vieja). Verificar con
  `grep -rl "view('{dotted.path}'" app/ routes/` (y con comillas dobles
  también) antes de asumir que un archivo se usa — o antes de borrarlo por
  huérfano.
- Si se borra una vista huérfana, borrar también su `.js`/`.css` exclusivo
  **solo** si nada más lo referencia (`grep -rl` sobre el path del asset en
  `resources/views/`).

## Ver también

- `.claude/rules/data-tables.md` para el patrón de listados (`index.blade.php`).
- `.claude/rules/form-requests.md` para la validación server-side del mismo formulario.
- `.claude/rules/blade-views.md` para las reglas generales de JS/CSS/iconos del panel.
