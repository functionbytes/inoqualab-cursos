@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.enterprises.create') }}" class="btn btn-primary btn-icon" title="Nueva empresa" aria-label="Nueva empresa">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Empresas',
        'description' => 'Gestiona las empresas registradas en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.enterprises.enterprises.enterprises._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empresa(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/enterprises/enterprises/enterprises/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/enterprises/enterprises/index.js') }}"></script>
@endpush
