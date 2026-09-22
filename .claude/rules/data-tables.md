# Data Table Rules — listados del panel manager

Todo listado nuevo (`index.blade.php` con `<table>`) en `resources/views/managers/`
usa por defecto estas 4 piezas estandarizadas. No reinventar HTML/JS de
búsqueda, filtros, selección masiva o paginación por vista.

## 1. Búsqueda + filtros

**Patrón vigente (usar en toda tabla nueva): botón icon-only con popover
anclado + chips de filtros activos.** El botón trigger solo lleva el ícono
`sliders` (sin texto) + un badge numérico cuando hay filtros activos — mismo
tamaño que el botón de buscar de al lado. Las ~39 vistas legacy (modal
Bootstrap) ya fueron migradas a este patrón; el modal solo queda como fallback
del partial por si aparece una vista nueva sin migrar.

```blade
<div class="card-body border-bottom">
    @php
        $filterChips = [];
        if ((${filtro} ?? '') !== '') {
            $filterChips[] = [
                'label' => 'Etiqueta: ' . $labelDeSuValor,
                'clear_url' => route('manager.{ruta}', array_filter(['search' => $searchKey ?? ''])),
            ];
        }
    @endphp
    <form method="GET" action="{{ Request::url() }}" id="searchForm">
        <input type="hidden" name="{filtro}" id="filter{Filtro}" value="{{ ${filtro} ?? '' }}">

        @php ob_start(); @endphp
        <div class="filter-popover-field">
            <div class="filter-popover-label">{Etiqueta}</div>
            <div class="filter-popover-options">
                <label class="filter-popover-option">
                    <input type="radio" data-filter-name="popover_{filtro}" value="" {{ (${filtro} ?? '') === '' ? 'checked' : '' }}>
                    <span class="filter-popover-dot"></span>
                    <span>Todos</span>
                </label>
                {{-- una <label> más por opción, mismo patrón, value= el valor real del filtro --}}
            </div>
        </div>
        @php $popoverBody = trim(ob_get_clean()); @endphp

        @include('managers.includes.filter-toolbar', [
            'searchName' => 'search',
            'searchValue' => $searchKey ?? '',
            'searchPlaceholder' => 'Buscar por...',
            'popoverBody' => $popoverBody,     // omitir si la tabla no tiene filtros
            'filterChips' => $filterChips,
        ])
    </form>
</div>
```

Reglas del popover:
- Los controles usan `data-filter-name="popover_{filtro}"`, **nunca `name`** — si llevaran
  `name` viajarían como query param duplicado al enviar el `<form>` GET. La exclusividad
  mutua de los radios la simula `FilterToolbar` en JS (delegado sobre `data-filter-name`,
  leído con `.attr()` — **nunca `.data('filter-name')`**, jQuery normaliza guiones a
  camelCase y ese `.data()` devuelve `undefined`, lo que deja más de un radio marcado a
  la vez), no depende del agrupamiento nativo del navegador.
- Un campo por filtro dentro de `$popoverBody`; varios campos se separan con
  `<div class="filter-popover-field">` (el CSS ya pone el espaciado entre ellos).
- Opciones generadas dinámicamente desde una colección (`@foreach($coleccion as $item)`)
  o un rango (`@for($r = 5; $r >= 1; $r--)`) van igual dentro de `$popoverBody` — el
  `ob_start()` captura el HTML ya resuelto, así que el `@foreach`/`@for` se preserva tal
  cual, nunca se "aplana" a opciones literales.
- `$filterChips` es opcional pero recomendado: un elemento por filtro activo, con
  `clear_url` apuntando a la URL actual SIN ese filtro. Con opciones fijas, un array de
  labels alcanza; con una colección, `optional($coleccion->firstWhere('id', $valor))->title`
  evita tener que armar el mapa a mano. Preservar los demás filtros + `search` en la URL:
  `url()->current() . '?' . http_build_query(request()->except('{filtro}'))` es más simple
  que reconstruir `route(...)` a mano y generaliza mejor con 2-3 filtros simultáneos.

En `@push('scripts')`:

```js
FilterToolbar.init({
    fields: { filter{Filtro}: 'popover_{filtro}' },  // { hiddenInputId: data-filter-name del grupo }
});
```

`FilterToolbar` está cargado globalmente (`public/managers/js/filter-toolbar.js`),
no requiere `<script>` adicional. Sin JS a mano para abrir/cerrar el popover ni para
el botón "Aplicar".

Referencia completa: `resources/views/managers/views/settings/contacts/index.blade.php`
(caso simple) y `resources/views/managers/views/orders/orders/index.blade.php`
(3 filtros, uno generado desde una colección con `@foreach`).

### Patrón legacy (solo como fallback del partial, no debería quedar ninguna vista así)

`filter-toolbar.blade.php` todavía soporta un modo con modal Bootstrap
(`'activeFilters' => ..., 'filtersModalTarget' => '#filters-modal'` en vez de
`popoverBody`/`filterChips`) por si aparece una vista no migrada, pero las 39 vistas que
lo usaban ya se migraron al popover — no crear una vista nueva con este patrón.

## 2. Selección masiva (bulk)

Thead: `<input type="checkbox" id="select-all">`. Cada fila:
`<input type="checkbox" class="bulk-checkbox" value="{{ $item->id }}">`.

```blade
@include('managers.includes.bulk-toolbar-modal', [
    'bulkEntityLabel' => '{entidad}(s)',
    'bulkActions' => [
        ['value' => 'accion', 'label' => 'Etiqueta'],
        ['value' => 'delete', 'label' => 'Eliminar'],
    ],
])
```

En `@push('scripts')`:

```js
BulkActions.init({
    url: '{{ route('manager.{modulo}.bulk-action') }}',
    entityLabel: '{entidad}(s)',
});
```

El controller necesita `bulkAction()` — ver `.claude/rules/controllers.md`. `BulkActions`
está cargado globalmente (`public/managers/js/bulk-actions.js`).

## 3. Paginación

El query del controller SIEMPRE pagina (`->paginate($request->input('per_page', 15))`),
nunca `->get()` sin límite. En la vista, al final del `<div class="card">`, después
del `card-body` de la tabla:

```blade
@include('managers.includes.pagination-footer', [
    'paginator' => ${coleccion},
    'itemLabel' => '{entidad en plural}',
])
```

Esto renderiza "Mostrando X–Y de Z {entidad}" + los links de Bootstrap
(`->appends(request()->input())->links()`), solo si `hasPages()`. No repetir
ese bloque a mano.

## Referencia

Ejemplo completo y actualizado: `resources/views/managers/views/settings/contacts/index.blade.php`
+ `public/managers/js/views/settings/contacts/index.js`.

## Excepciones legítimas (no forzar el patrón)

- Tablas de configuración con un puñado fijo de filas (sin crecimiento real) no
  necesitan paginación ni bulk — usar criterio, no aplicar por aplicar.
- Vistas de solo lectura (reportes, auditorías, sitemaps) no necesitan bulk.
- Si una tabla no tiene una acción de búsqueda con sentido (nada que filtrar por
  texto), omitir `filter-toolbar` y dejar solo el botón de filtros si aplica.

## Ver también

- `.claude/rules/detail-forms.md` para el patrón de crear/editar UNA entidad
  (`create.blade.php`/`edit.blade.php`) — es un patrón distinto, no se mezclan.
