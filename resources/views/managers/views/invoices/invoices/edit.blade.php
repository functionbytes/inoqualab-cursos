@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100" id="invoices-edit"
                 data-config='@php $__jsonInline1 = [
                    "paymentDate" => \Carbon\Carbon::parse($invoice->payment_at)->format("Y-m-d"),
                    "routes" => [
                        "update" => route("manager.invoices.update"),
                        "distributorInvoices" => route("manager.distributors.invoices", ["slack" => "SLACK_PLACEHOLDER"]),
                    ],
                 ]; @endphp@json($__jsonInline1)'>

                <form id="formInvoices" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}

                    <input type="hidden" id="slack" name="slack" value="{{ $invoice->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar factura</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos de la factura. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Orden</label>
                                        <input type="text" class="form-control" id="reference"  name="reference"  placeholder="Ingresa" value=" {{ $invoice->reference  }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha desde</label>
                                    <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($invoice->from_at)) }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha hasta</label>
                                    <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($invoice->to_at)) }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha creación</label>
                                    <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($invoice->created_at)) }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Condición pago</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, $invoice->condition_id , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Metodo pago</label>
                                    <div class="input-group">
                                        {!! Form::select('method', $methods, $invoice->method_id , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                    </div>
                                    <label id="method-error" class="error d-none" for="method"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Fecha pago</label>
                                        <input type="text" class="form-control datepicker" id="payment"  name="payment"  data-date-format="yyyy-mm-dd"  value="" >
                                </div>
                            </div>

                            <div class="col-12">
                            <div class="border-top pt-1 mt-4">
                                <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                </button>
                            </div>
                        </div>

                        </div>
                    </div>


                </form>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('managers/js/views/invoices/invoices/edit.js') }}"></script>
@endpush



