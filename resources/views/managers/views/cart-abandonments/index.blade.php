@extends('layouts.managers')

@section('title', 'Carritos incompletos')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Carritos incompletos',
        'description' => 'Correos capturados en el checkout (autenticados o invitados) que todavía no generaron una orden.
                        Se recuerdan automáticamente por correo una hora después.',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list" id="cart-abandonments-index"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.cart-abandonments.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.cart-abandonments._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'registro(s)',
        'bulkActions' => [
            ['value' => 'remind', 'label' => 'Enviar recordatorio'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Detalle del carrito abandonado --}}
    <div class="modal fade" id="abandonment-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content abandonment-modal-content">

                <div class="abandonment-modal-header">
                    <button type="button" class="btn-close btn-close-white abandonment-modal-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    <div class="abandonment-avatar" id="abandonment-detail-avatar">—</div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="abandonment-modal-name text-truncate" id="abandonment-detail-name">—</div>
                        <div class="abandonment-modal-email text-truncate" id="abandonment-detail-email">—</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="abandonment-modal-total">$ <span id="abandonment-detail-header-total">0</span></div>
                        <div class="abandonment-modal-total-label">valor del carrito</div>
                    </div>
                </div>

                <div class="modal-body">
                    <h5 class="modal-title visually-hidden">Detalle del carrito abandonado</h5>

                    <div class="abandonment-info-row">
                        <div class="abandonment-info-card">
                            <div class="abandonment-info-label">Contacto</div>
                            <div class="fw-bold" id="abandonment-detail-cellphone">—</div>
                            <div class="text-muted small" id="abandonment-detail-identification"></div>
                        </div>
                        <div class="abandonment-info-card">
                            <div class="abandonment-info-label">Capturado</div>
                            <div class="fw-bold" id="abandonment-detail-created">—</div>
                        </div>
                        <div class="abandonment-info-card">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="abandonment-info-label mb-0">Seguimiento</span>
                                <span class="badge" id="abandonment-detail-status">—</span>
                            </div>
                            <div class="fw-bold" id="abandonment-detail-followup">—</div>
                        </div>
                    </div>

                    <div class="abandonment-cart-title">Carrito</div>
                    <div class="table-responsive abandonment-cart-table">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ítem</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody id="abandonment-detail-items"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold text-muted">Total</td>
                                    <td class="text-end abandonment-cart-total">$ <span id="abandonment-detail-total">0</span> <span class="abandonment-cart-total-currency">COP</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer flex-column border-0 pt-0">
                    <button type="button" id="abandonment-detail-remind-btn" class="btn abandonment-btn-primary w-100 mb-2">
                        Enviar recordatorio ahora
                    </button>
                    <a href="#" id="abandonment-detail-cart-link" target="_blank" class="btn abandonment-btn-outline w-100">
                        Ver carrito como cliente
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/cart-abandonments/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/cart-abandonments/index.js') }}"></script>
@endpush
