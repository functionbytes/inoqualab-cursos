@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formInvoices" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear factura</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Distribuidor</label>
                                    <div class="input-group">
                                        {!! Form::select('distributor', $distributors, null , ['class' => 'select2 form-control' ,'name' => 'distributor', 'id' => 'distributor' ]) !!}
                                    </div>
                                    <label id="distributor-error" class="error d-none" for="distributor"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado de factura</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, null , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>  
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Metodo pago</label>
                                    <div class="input-group">
                                        {!! Form::select('method', $methods, null , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                    </div>
                                    <label id="method-error" class="error d-none" for="method"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input type="text" id="range" name="range" class="form-control daterange" />
                                        <span class="input-group-text">
                                          <i class="fas fa-calendar fs-5"></i>
                                        </span>
                                    </div>
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


    <div id="view-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                    <h4 class="my-0">¿Deseas visualizar la factura?</h4>
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

    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            $('.daterange').daterangepicker();

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
                    distributor: {
                        required: true,
                    },

                },
                messages: {
                    condition: {
                        required: "Es necesario un estado.",
                    },
                    method: {
                        required: "Es necesario un estado.",
                    },
                    distributor: {
                        required: "Es necesario un distribuidor.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formInvoices');
                    var formData = new FormData($form[0]);
                    var distributor = $("#distributor").val();
                    var condition = $("#condition").val();
                    var method = $("#method").val();
                    var range = $("#range").val();

                    formData.append('distributor', distributor);
                    formData.append('condition', condition);
                    formData.append('methods', method);
                    formData.append('range', range);

                    $.ajax({
                        url: "{{ route('accounting.invoices.store') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){

                                // toastr.success("Se ha generado una factura.", "Operación exitosa", {
                                //     closeButton: true,
                                //     progressBar: true,
                                //     positionClass: "toast-bottom-right"
                                // });

                                var url = "{{ route('accounting.invoices.view', ':id') }}";
                                url = url.replace(':id', response.data);

                                $("#view-modal").modal("show");
                                $("#view-link").attr("href", url);

                            }else{
                                error = response.message;

                                toastr.warning(error, "Operación fallida", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });


                                $('.errors').text(error);
                                $('.errors').removeClass('d-none');
                            }
                        }
                    });

                }

            });

        });

    </script>


@endpush



