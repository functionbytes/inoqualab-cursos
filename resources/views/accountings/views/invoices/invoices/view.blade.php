@extends('layouts.managers')
@section('content')
    
    @include('accountings.includes.card', ['title' => 'Detalle Factura '. $invoice->slack])
    
    <div class="row">
        <div class="col-lg-12 ">
            <div class="checkout">
                <div class="card ">
                    <div class="card-body p-4">
                        <div class="wizard-content">
                            <form action="#" class="tab-wizard wizard-circle wizard clearfix" role="application" id="steps-uid-0">
                                <div class="steps clearfix">

                                </div>
                                <div class="content clearfix">
                                    <section id="steps-uid-0-p-1" role="tabpanel" >
                                        <div class="billing-address-content">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="text-left mx-3">
                                                        <address>
                                                            <h4 class="mb-3">Para</h4>
                                                            <h6 class="mt-0 mb-0 fw-bold invoice-customer">
                                                                <span>Distribuidor :</span>
                                                                <strong>{{Str::upper(($invoice->distributor->title ?? 'N/D'))}}</strong>
                                                            </h6>
                                                            <p class="mt-0 mb-0 {{ ($invoice->distributor->address ?? null) !=null ? '' : 'd-none' }}">
                                                                <span>Dirección :</span>
                                                                <strong>{{Str::upper(($invoice->distributor->address ?? ''))}}</strong>
                                                            </p>
                                                            <p class="mt-0 mb-0">
                                                                <span>Referencia :</span>
                                                                <strong>{{  $invoice->reference}}</strong>
                                                            </p>
                                                            @if($invoice->payment_at!=null)
                                                                <p class="mt-0 mb-0">
                                                                    <span>Fecha de la factura :</span>
                                                                    <strong>{{ date('Y-m-d', strtotime($invoice->payment_at)) }}</strong>
                                                                </p>
                                                            @endif
                                                            <p class="mt-0 mb-0 {{ $invoice->from_at ? '' : 'd-none' }}">
                                                                <span>Desde :</span>
                                                                <strong>{{ $invoice->from_at ? date('Y-m-d', strtotime($invoice->from_at)) : '' }}</strong>
                                                            </p>
                                                            <p class="mt-0 mb-0 {{ $invoice->to_at ? '' : 'd-none' }}">
                                                                <span>Hasta :</span>
                                                                <strong>{{ $invoice->to_at ? date('Y-m-d', strtotime($invoice->to_at)) : '' }}</strong>
                                                            </p>
                                                        </address>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="table-responsive">
                                                    <hr>
                                                    <table class="table align-middle text-nowrap mb-0">

                                                        <thead>
                                                            <tr>
                                                                <th scope="col" class="fw-bolder text-uppercase">Descripción</th>
                                                                <th scope="col" class=" fw-bolder text-uppercase">Cantidad</th>
                                                                <th scope="col" class=" fw-bolder text-uppercase">Subtotal</th>
                                                                <th scope="col" class=" fw-bolder text-uppercase">Total</th>
                                                            </tr>
                                                        <!-- end row -->
                                                        </thead>

                                                        <tbody>
                                                        @foreach($invoice->items as $item)


                                                            <tr>

                                                                <td class="border-bottom-0">
                                                                    <div class="d-flex align-items-center gap-3">
                                                                            <h6 class="fw-semibold fs-2 mb-0">{{ $item->course->title }}</h6>
                                                                    </div>
                                                                </td>
                                                                <td class="border-bottom-0">
                                                                    <div class="d-flex align-items-center gap-3">
                                                                            <p class="mb-0">{{ ceil($item->quantity)}}</p>
                                                                    </div>
                                                                </td>
                                                                <td class="border-bottom-0">
                                                                    <div class="d-flex align-items-center gap-3">
                                                                        <p class="mb-0">$ {{ number_format($item->subtotal)}}</p>
                                                                    </div>
                                                                </td>
                                                                <td class="border-bottom-0">
                                                                    <div class="d-flex align-items-center gap-3">
                                                                        <p class="mb-0">${{ number_format($item->total)}}</p>
                                                                    </div>
                                                                </td>


                                                        </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="order-summary border rounded p-4 my-4">
                                                <div class="mt-3 mb-3">
                                                    <h5 class="fs-5 fw-semibold mb-4 text-uppercase">Resumen de factura</h5>

                                                    @if($invoice->total_discount_amount>0)
                                                        <div class="d-flex justify-content-between mb-4">
                                                            <p class="mb-0 fs-4">Descuentos</p>
                                                            <h6 class="mb-0 fs-4 fw-semibold">${{ number_format($invoice->total_discount_amount) }}</h6>
                                                        </div>
                                                    @endif

                                                    @if($invoice->total_tax_amount>0)
                                                    <div class="d-flex justify-content-between mb-4">
                                                        <p class="mb-0 fs-4">Impuestos 19%</p>
                                                        <h6 class="mb-0 fs-4 fw-semibold">${{ number_format($invoice->total_tax_amount) }}</h6>
                                                    </div>
                                                    @endif

                                                    <div class="d-flex justify-content-between mb-4">
                                                        <h6 class="mb-0 fs-4 ">Subtotal</h6>
                                                        <h6 class="mb-0 fs-5 fw-semibold">${{ number_format($invoice->total_after_discount) }}</h6>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-0 fs-4 fw-semibold">Total</h6>
                                                        <h6 class="mb-0 fs-5 fw-semibold">${{ number_format($invoice->total_before_discount) }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                         </section>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection