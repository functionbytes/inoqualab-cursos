@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.enterprises.staffs.create', $enterprise->slack) }}" class="btn btn-primary btn-icon" title="Nuevo empleado" aria-label="Nuevo empleado">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Empleados',
        'description' => 'Empleados de ' . Str::words(Str::upper(Str::lower($enterprise->title)), 8, '...'),
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.enterprises.staffs._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empleado(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/enterprises/staffs/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/staffs/index.js') }}"></script>
@endpush
