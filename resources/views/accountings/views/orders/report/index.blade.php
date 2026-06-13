@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Reporte ordenes</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Distribuidor</label>
                                    <div class="input-group">
                                        {!! Form::select('distributor', $distributors, null , ['class' => 'select2 form-control' ,'name' => 'distributor', 'id' => 'distributor' ]) !!}
                                    </div>
                                    <label id="distributor-error" class="error d-none" for="distributor"></label>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Empresas</label>
                                    <div class="input-group">
                                        {!! Form::select('enterprise', [], null , ['class' => 'select2 form-control' ,'name' => 'enterprise', 'id' => 'enterprise' ]) !!}
                                    </div>
                                    <label id="enterprise-error" class="error d-none" for="enterprise"></label>
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Condición</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, null , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Tipo de factura</label>
                                    <div class="input-group">
                                        {!! Form::select('method', $methods, null , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                    </div>
                                    <label id="method-error" class="error d-none" for="method"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Tipo de factura</label>
                                    <div class="input-group">
                                        {!! Form::select('type', $types, null , ['class' => 'select2 form-control' ,'name' => 'type', 'id' => 'type' ]) !!}
                                    </div>
                                    <label id="type-error" class="error d-none" for="type"></label>
                                </div>
                            </div>
                            <div class="col-6">
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

@endsection



@push('scripts')

    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;

        $(document).ready(function() {

            $('#distributor').on('change', function() {
                var distributorId = $(this).val();

                $.ajax({
                    url: '{{ route('accounting.orders.get.enterprises') }}', // URL para obtener los cursos
                    type: 'POST', // Tipo de solicitud
                    data: {
                        distributor: distributorId
                    }, // Datos enviados en la solicitud
                    dataType: 'json', // Tipo de datos esperados en la respuesta
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Token CSRF para solicitudes seguras
                    },
                    success: function(data) {
                        // Inicializa el select2 con los datos obtenidos
                        $('#enterprise').empty().select2({
                            data: data.map(function(item) {
                                return {
                                    id: item.id, // Asume que 'id' es el identificador único del curso
                                    text: item.text // Asume que 'name' es el nombre del curso
                                };
                            }),
                            placeholder: "Seleccionar una empresa"
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                    }
                });


            });

            $("#distributors").select2({
                placeholder: "Seleccionar una distribuidor",
                minimumResultsForSearch: Infinity
            });


            $('.daterange').daterangepicker();

            $("#formReport").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    distributor: {
                        required: true,
                    },
                    enterprise: {
                        required: function(element) {
                            return $("#distributor").val() == '0' ? false : true;
                        }
                    },
                    type: {
                        required: true,
                    },
                    condition: {
                        required: true,
                    },
                    method: {
                        required: true,
                    },
                    range: {
                        required: true,
                    },
                },
                messages: {
                    distributor: {
                        required: "Es necesario una opción.",
                    },
                    enterprise: {
                        required: "Es necesario una opción.",
                    },
                    type: {
                        required: "Es necesario una opción.",
                    },
                    condition: {
                        required: "Es necesario una opción.",
                    },
                    method: {
                        required: "Es necesario una opción.",
                    },
                    range: {
                        required: "Es necesario una opción.",
                    },
                },
                submitHandler: function(form) {

                    toastr.success("Se ha generado el reporte.", "Operación exitosa", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });

                    var query = {
                        range: $("#range").val(),
                        distributor: $("#distributor").val(),
                        enterprise: $("#enterprise").val(),
                        type : $("#type").val(),
                        methods : $("#method").val(),
                        condition: $("#condition").val(),
                    }

                    var url = "{{ route('accounting.orders.generate') }}?" + $.param(query);

                    window.location = url;

                }

            });




        });

    </script>



@endpush



