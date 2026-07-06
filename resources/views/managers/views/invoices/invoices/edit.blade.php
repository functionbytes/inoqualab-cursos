@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formInvoices" enctype="multipart/form-data" role="form" onSubmit="return false">

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
                                    <label class="control-label col-form-label">Metodo pago</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, $invoice->condition_id , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Condición pago</label>
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

    <script type="text/javascript">
        $(document).ready(function() {

            $(".datepicker").datepicker({
                format: 'yyyy-mm-dd', // Formato de año-mes-día
                autoclose: true,
                todayHighlight: true
            });

            // Formatear la fecha de 'Y-m-d' a 'Y-m-d'
            var paymentDate = '{{ \Carbon\Carbon::parse($invoice->payment_at)->format('Y-m-d') }}';

            // Establecer la fecha en el datepicker
            $('.datepicker').datepicker('setDate', paymentDate);

            $("#formInvoices").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    condition: {
                        required: true,
                    },
                    method: {
                        required: true,
                    },
                    payment: {
                        required: function() {
                            return $("#condition").val() == 4 ? true : false
                        },
                    },

                },
                messages: {
                    condition: {
                        required: "Es necesario un estado.",
                    },
                    method: {
                        required: "Es necesario un estado.",
                    },
                    payment: {
                        required: "Es necesario una fecha",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formInvoices');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var condition = $("#condition").val();
                    var method = $("#method").val();
                    var payment = $("#payment").val();

                    formData.append('slack', slack);
                    formData.append('condition', condition);
                    formData.append('methods', method);
                    formData.append('payment', payment);

                    $.ajax({
                        url: "{{ route('manager.invoices.update') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {


                            var distributor = response.data.distributor; // Asegúrate de usar "var" para declarar la variable

                            if(response.success == true){

                                toastr.success("Se ha editado correctamente la factura.", "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });
                                setTimeout(function() {
                                    window.location.href = "{{ route('manager.distributors.invoices', ['slack' => 'SLACK_PLACEHOLDER']) }}".replace('SLACK_PLACEHOLDER', distributor);
                                }, 2000);
                            }else{

                                toastr.warning(response.error, "Operación fallida", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                            }
                        }
                    });

                }

            });

        });

    </script>


@endpush



