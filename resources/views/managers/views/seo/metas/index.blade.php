@extends('layouts.managers')

@section('title', 'Meta SEO')

@section('page_header')
    @php ob_start(); @endphp
<div class="btn-group">
                            <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
                                <i class="fas fa-ellipsis-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.export') }}">Exportar CSV</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.import') }}">Importar CSV</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.export-json') }}">Exportar JSON</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.import-json') }}">Importar JSON</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('manager.seo.audit.index') }}">Auditoría SEO</a>
                                <button class="dropdown-item" type="button" data-action="reload">Actualizar</button>
                            </div>
                        </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Panel de control SEO',
        'description' => 'Auditoría centralizada de configuraciones SEO de todos los modelos del sistema',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

                <div id="ajax-table-root">
            @include('managers.views.seo.metas._table')
        </div>
    </div>

    @include('managers.includes.delete')

    <div id="metas-config" class="d-none"
         data-inline-base-url="{{ url('panel/seo/metas') }}"
         data-bulk-action-url="{{ route('manager.seo.metas.bulk-action') }}"></div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'meta(s) SEO',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/seo-badges.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/metas/index.css') }}?v={{ @filemtime(public_path('managers/css/views/seo/metas/index.css')) ?: 1 }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/metas/index.js') }}?v={{ @filemtime(public_path('managers/js/views/seo/metas/index.js')) ?: 1 }}"></script>
@endpush
