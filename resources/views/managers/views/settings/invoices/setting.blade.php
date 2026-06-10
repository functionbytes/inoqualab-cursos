@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formInvoices" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Configuración de facturación</h5>
                        </div>

                        <div class="row mt-20">

                            <div class="col-12 ">
                                <div class="mb-4 mt-3">
                                    <label  class="control-label col-form-label ">Consecutivo de facturación</label>
                                    <p class="card-subtitle mb-3 mt-0">(Este sera el consecutivo de la facturación es importante tener presente que al momento de cambiarlo .)</p>
                                    <input type="text" class="form-control" id="invoice_default"  name="invoice_default" value="{{ setting('invoice_default') }}">
                                </div>
                            </div>

                            <div class="col-12 ">
                                <div class="mb-4 mt-3">
                                    <label  class="control-label col-form-label ">Tiempo de facturacion</label>
                                    <p class="card-subtitle mb-3 mt-0">(Cada cuantos dias se factura el cliente.)</p>
                                    <input type="text" class="form-control" id="invoice_days"  name="invoice_days" value="{{ setting('invoice_days') }}">
                                </div>
                            </div>



                            <div class="col-12 border-top">
                                <div class="mb-4 mt-3">
                                    <div class="row align-items-center">
                                        <div class=" col-sm-11 ">
                                            <label  class="control-label col-form-label ">Habilitar correo CC</label>
                                            <p class="card-subtitle mb-3 mt-0">(Si "Habilita" esta configuración de "Correo CC", las opciones del campo de entrada de Correo CC aparecerán en las páginas Crear ticket, Crear ticket de administrador y Ticket de invitado).</p>
                                        </div>
                                        <div class="col-sm-1 justify-content-end d-flex align-items">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="invoices_notification_email_enable" id="invoices_notification_email_enable"   @if(setting('invoices_notification_email_enable')=='true' ) checked @endif/>
                                            </div>

                                        </div>
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

    <script type="text/javascript">
        Dropzone.autoDiscover = false;

        $(document).ready(function() {



            $("#formInvoices").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    invoice_default: {
                        required: true,
                        minlength: 1,
                        maxlength: 4,
                    },
                    invoice_days: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 365,
                    },
                },
                messages: {
                    invoice_default: {
                        required: "El parametro es necesario.",
                        minlength: "Debe contener al menos 1 caracter",
                        maxlength: "Debe contener al menos 4 caracter",
                    },
                    invoice_days: {
                        required: "El parametro es necesario.",
                        number: 'Solo se puede ingresar números.',
                        min: "Debe mayor o igual a 0",
                        max: "Debe ser menor o igual a 365",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formInvoices');
                    var formData = new FormData($form[0]);
                    var invoice_default = $("#invoice_default").val();
                    var invoice_days = $("#invoice_days").val();
                    var invoices_notification_email_enable = $("#invoices_notification_email_enable").is(':checked');

                    formData.append('invoice_default', invoice_default);
                    formData.append('invoice_days', invoice_days);
                    formData.append('invoices_notification_email_enable', invoices_notification_email_enable);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('manager.settings.invoices.update') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){

                                message = response.message;

                                toastr.success(message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                setTimeout(function() {
                                    window.location.href = "{{ route('manager.dashboard') }}";
                                }, 2000);

                            }else{

                                $submitButton.prop('disabled', false);
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





