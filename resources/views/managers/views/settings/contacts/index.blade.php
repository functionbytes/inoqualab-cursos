@extends('layouts.managers')

@section('title', 'Contactos')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/contacts/index.css') }}">
@endpush

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Contactos',
        'description' => 'Gestiona los mensajes de contacto recibidos',
    ])
@endsection

@section('content')


    <div id="contactsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.contacts.bulk-action') }}">

        <div id="ajax-table-root">
            @include('managers.views.settings.contacts._table', [
                'contacts' => $contacts,
                'searchKey' => $searchKey ?? '',
                'reviewed' => $reviewed ?? '',
            ])
        </div>
    </div>

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'contacto(s)',
        'bulkActions' => [
            ['value' => 'reviewed', 'label' => 'Marcar como gestionado'],
            ['value' => 'pending', 'label' => 'Marcar como pendiente'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/contacts/index.js') }}"></script>
@endpush
