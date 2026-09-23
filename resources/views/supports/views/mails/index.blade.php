@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Correos entrantes',
        'description' => 'Correos interceptados por IMAP en espera de convertirse en órdenes',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list" id="mails-list"
         data-bulk-url="{{ route('support.mails.bulk-discard') }}"
         data-discard-url="{{ route('support.mails.discard', ':slack') }}">

        <div id="ajax-table-root">
            @include('supports.views.mails._table')
        </div>
    </div>

    {{-- Modal descartar --}}
    <div id="discard-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Descartar correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-warning"><i class="fa-duotone fa-triangle-exclamation"></i></div>
                    <h4 class="my-0">¿Descartar este correo?</h4>
                    <p>El correo será marcado como ignorado y no generará una orden.</p>
                    <div class="row justify-content-center mt-3">
                        <div class="col-sm-12 col-md-6">
                            <button type="button" id="discard-confirm-btn" class="btn btn-danger w-100 mb-2">Confirmar</button>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'correo(s)',
        'bulkActions' => [
            ['value' => 'discard', 'label' => 'Descartar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/mails/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/mails/index.js') }}"></script>
@endpush
