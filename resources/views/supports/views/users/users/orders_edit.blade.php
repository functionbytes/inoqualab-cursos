@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Editar orden - ' . $order->slack])
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formOrders" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-update-url="{{ route('support.users.orders.update') }}"
                      data-view-url-template="{{ route('support.users.orders.view', ':id') }}">

                    {{ csrf_field() }}

                    <input type="hidden" id="slack" name="slack" value="{{ $order->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar orden</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos de la orden. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Orden codigo</label>
                                    <input type="text" class="form-control" value="{{ $order->slack }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Orden referencia</label>
                                    <input type="text" class="form-control" value="{{ $order->reference }}" disabled>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Condición pago</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, $order->condition_id, ['class' => 'select2 form-control', 'name' => 'condition', 'id' => 'condition']) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Metodo pago</label>
                                    <div class="input-group">
                                        {!! Form::select('method', $methods, $order->method_id, ['class' => 'select2 form-control', 'name' => 'method', 'id' => 'method']) !!}
                                    </div>
                                    <label id="method-error" class="error d-none" for="method"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Fecha pago</label>
                                    <input type="text" class="form-control datepicker" id="payment" name="payment" data-date-format="yyyy-mm-dd" value=""
                                           data-payment-date="{{ $order->payment_at ? \Carbon\Carbon::parse($order->payment_at)->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Fecha creación</label>
                                    <input type="text" class="form-control" value="{{ date('Y-m-d', strtotime($order->created_at)) }}" disabled>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="errors d-none"></div>
                            </div>

                            <div class="col-12">
                                <div class="border-top pt-1 mt-4">
                                    <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
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
    <script src="{{ asset('supports/js/views/users/users/orders_edit.js') }}"></script>
@endpush
