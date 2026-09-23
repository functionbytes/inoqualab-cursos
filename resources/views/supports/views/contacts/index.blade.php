@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Contactos',
        'description' => 'Solicitudes recibidas desde el formulario de contacto',
    ])
@endsection

@section('content')

    <div id="contactsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-action-url="{{ route('support.contacts.bulk-action') }}">

        <div id="ajax-table-root">
            @include('supports.views.contacts._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'contacto(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/contacts/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/contacts/index.js') }}"></script>
@endpush
