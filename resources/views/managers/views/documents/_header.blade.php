{{-- Cabecera de orden/factura en cualquier perfil: título + migas + selector
     de diseño + menú ⋮ de acciones (mismo botón que los listados).
     Params: $title, $breadcrumbs, $design,
     $actions = [['label' => ..., 'url' => ..., 'newTab' => bool], ...] --}}
@php ob_start(); @endphp
<div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
    @include('managers.includes.design-switcher', ['design' => $design])
    @if(! empty($actions))
        <div class="btn-group">
            <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones" aria-label="Acciones">
                <i class="fas fa-ellipsis-vertical"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                @foreach($actions as $action)
                    <a class="dropdown-item" href="{{ $action['url'] }}"
                       @if($action['newTab'] ?? false) target="_blank" rel="noopener" @endif>{{ $action['label'] }}</a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@php $headerActions = trim(ob_get_clean()); @endphp
@include('managers.includes.card', [
    'title' => $title,
    'breadcrumbs' => $breadcrumbs,
    'actions' => $headerActions,
])
