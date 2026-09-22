@extends('layouts.managers')

@section('title', 'Historial de versiones: ' . $template->name)

@section('page_header')
    @include('managers.includes.card', ['title' => 'Historial de versiones: ' . $template->name])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div id="ajax-table-root">
        @include('managers.views.mailer.templates._versions', ['template' => $template, 'versions' => $versions])
    </div>

    {{-- Confirm restore modal --}}
    <div class="modal fade" id="confirm-restore-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar restauración</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" id="confirm-restore-message">¿Restaurar esta versión?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning w-100 mb-2" id="confirm-restore-btn">Restaurar</button>
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/templates/versions.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/templates/versions.js') }}"></script>
@endpush
