@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formOrders" enctype="multipart/form-data" role="form"
                      data-update-url="{{ route('accounting.orders.update') }}"
                      data-view-url-template="{{ route('accounting.orders.view', ':id') }}"
                      data-payment-date="{{ \Carbon\Carbon::parse($order->payment_at)->format('Y-m-d') }}">

                    {{ csrf_field() }}

                    <input type="hidden" id="slack" name="slack" value="{{ $order->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar orden</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Orden codigo</label>
                                        <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $order->slack  }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Orden referencia</label>
                                    <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $order->reference  }}" disabled>
                                </div>
                            </div>



                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Condición pago</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, $order->condition_id , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Metodo pago</label>
                                    <div class="input-group">
                                        {!! Form::select('method', $methods, $order->method_id , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                    </div>
                                    <label id="method-error" class="error d-none" for="method"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Fecha pago</label>
                                        <input type="text" class="form-control datepicker" id="payment"  name="payment"  data-date-format="yyyy-mm-dd"  value="" >
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Fecha creación</label>
                                        <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($order->created_at)) }}" disabled>
                                </div>
                            </div>  <div class="col-12">
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



        <div id="view-modal" class="modal fade">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                        <h4 class="my-0">¿Deseas visualizar la orden?</h4>
                        <p>Visualizaras el detalle de la factura que acabaste de genrar</p>
                        <div class="row justify-content-center mt-20  ">
                            <div class="col-sm-12 col-md-5">
                                <a href="" id="view-link" class="btn btn-danger w-100">Confirmar</a>
                            </div>
                            <div class="col-sm-12 col-md-5">
                                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('accountings/js/views/orders/orders/edit.js') }}"></script>
@endpush



