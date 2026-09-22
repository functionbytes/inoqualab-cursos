@extends('layouts.managers')


@section('page_header')
    @include('accountings.includes.card', ['title' => 'Editar factura'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formInvoices" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('accounting.invoices.update') }}"
                  data-distributor-invoices-url="{{ url('accounting/distributors/invoices') }}"
                  data-payment-date="{{ \Carbon\Carbon::parse($invoice->payment_at)->format('Y-m-d') }}">

                {{ csrf_field() }}

                <input type="hidden" id="slack" name="slack" value="{{ $invoice->slack }}">

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Editar factura</h6>
                        <p class="text-muted mb-3">
                            Actualiza los datos de la factura. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Orden</label>
                                <input type="text" class="form-control" id="reference"  name="reference"  placeholder="Ingresa" value=" {{ $invoice->reference  }}" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha desde</label>
                                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($invoice->from_at)) }}" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha hasta</label>
                                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($invoice->to_at)) }}" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha creación</label>
                                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($invoice->created_at)) }}" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado de factura</label>
                                <div class="input-group">
                                    {!! Form::select('condition', $conditions, $invoice->condition_id , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                </div>
                                <label id="condition-error" class="error d-none" for="condition"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Metodo pago</label>
                                <div class="input-group">
                                    {!! Form::select('method', $methods, $invoice->method_id , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                </div>
                                <label id="method-error" class="error d-none" for="method"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Fecha pago</label>
                                <input type="text" class="form-control datepicker" id="payment"  name="payment"  data-date-format="yyyy-mm-dd"  value="" >
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>

                </div>

            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre esta factura</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">La orden y las fechas son generadas por el sistema y no se pueden modificar. Cambia el estado, el método de pago y la fecha de pago según corresponda.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('accountings/js/views/invoices/invoices/edit.js') }}"></script>
@endpush


