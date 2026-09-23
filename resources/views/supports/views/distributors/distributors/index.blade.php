@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.distributors.create') }}" class="btn btn-primary btn-icon" title="Nuevo distribuidor" aria-label="Nuevo distribuidor">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Distribuidores',
        'description' => 'Gestiona los distribuidores registrados en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-url="{{ route('support.distributors.bulk-action') }}"
         data-bulk-entity-label="distribuidor(es)">

        <div id="ajax-table-root">
            @include('supports.views.distributors.distributors._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'distribuidor(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/views/distributors/distributors/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/distributors/index.js') }}"></script>
@endpush
