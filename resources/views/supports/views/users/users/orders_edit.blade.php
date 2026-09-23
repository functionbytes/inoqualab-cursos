@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Editar orden - ' . $order->slack])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formOrders" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-update-url="{{ route('support.users.orders.update') }}"
                  data-view-url-template="{{ route('support.users.orders.view', ':id') }}">

                {{ csrf_field() }}

                <input type="hidden" id="slack" name="slack" value="{{ $order->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Datos de la orden</h6>
                        <p class="text-muted small mb-0">
                            Actualiza el estado de pago de la orden.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Orden codigo</label>
                                <input type="text" class="form-control" value="{{ $order->slack }}" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Orden referencia</label>
                                <input type="text" class="form-control" value="{{ $order->reference }}" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Condición pago</label>
                                <div class="input-group">
                                    {!! Form::select('condition', $conditions, $order->condition_id, ['class' => 'select2 form-control', 'name' => 'condition', 'id' => 'condition']) !!}
                                </div>
                                <label id="condition-error" class="error d-none" for="condition"></label>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Metodo pago</label>
                                <div class="input-group">
                                    {!! Form::select('method', $methods, $order->method_id, ['class' => 'select2 form-control', 'name' => 'method', 'id' => 'method']) !!}
                                </div>
                                <label id="method-error" class="error d-none" for="method"></label>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha pago</label>
                                <input type="text" class="form-control datepicker" id="payment" name="payment" data-date-format="yyyy-mm-dd" value=""
                                       data-payment-date="{{ $order->payment_at ? \Carbon\Carbon::parse($order->payment_at)->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha creación</label>
                                <input type="text" class="form-control" value="{{ date('Y-m-d', strtotime($order->created_at)) }}" disabled>
                            </div>

                            <div class="col-12">
                                <div class="errors d-none"></div>
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
                    <h6 class="mb-0 fw-bold">Sobre esta orden</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        La fecha de pago solo se conserva cuando la condición es "Pagada". Al cambiar a
                        cualquier otra condición, la fecha se limpia automáticamente al guardar.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ url('managers/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('supports/js/views/users/users/orders_edit.js') }}"></script>
@endpush
