@extends('layouts.managers')

@section('title', 'Certificados')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.certificate.broad', $user->slack) }}"
                               class="btn btn-primary">
                                Descargar todos
                            </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Certificados del usuario',
        'description' => 'Historial de certificados obtenidos por curso',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="users-certificates-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}">

                <div id="ajax-table-root">
            @include('managers.views.users.certificates._table')
        </div>
    </div>

    

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/users/certificates/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/users/certificates/index.js') }}"></script>
@endpush
