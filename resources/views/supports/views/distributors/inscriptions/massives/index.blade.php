@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <!-- ---------------------
                                    start Donation
                                ---------------- -->
            <div class="card w-100">

                <form id="formInscriptions" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input id="distributor" name="distributor" type="hidden" value="{{ $distributor->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Inscripciones</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera
                            eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que
                            corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier
                            información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Empresas</label>
                                    <div class="input-group">
                                        {!! Form::select('enterprise', $enterprises, null , ['class' => 'select2 form-control' ,'name' => 'enterprise', 'id' => 'enterprise']) !!}
                                    </div>
                                    <label id="enterprise-error" class="error d-none" for="enterprise"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Cursos</label>
                                    <div class="input-group">
                                        {!! Form::select('courses[]', [], null, [
                                            'class' => 'select2 form-control',
                                            'id' => 'courses',
                                            'multiple' => 'multiple'
                                        ]) !!}
                                    </div>
                                    <label id="course-error" class="error d-none" for="course"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Usuarios</label>
                                    <div class="input-group">
                                        {!! Form::select('users[]', [], null, [
                                            'class' => 'select2 form-control',
                                            'id' => 'users',
                                            'multiple' => 'multiple'
                                        ]) !!}
                                    </div>
                                    <label id="user-error" class="error d-none" for="user"></label>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="action-form border-top mt-2">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                            Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>


    <div id="error-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                    <h4 class="my-0">Este usuario ya esta inscrito a este curso</h4>
                    <p><span class="customer"></span></p>
                    <p><span class="course"> <span> con fecha <span class="start"> <span></span></p>
                    <div class="row justify-content-center mt-20  ">
                        <div class="col-sm-12 col-md-5 w-100">
                            <a class="btn btn-primary w-100 registration" data-course="" data-user="" data-enterprise="" data-distributor="{{ $distributor->id }}" >Inscribir nuevamente</a>
                            <button type="button" class="btn btn-primary w-100 mt-1 registration-close" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection



@push('scripts')

    <script type="text/javascript">

        let errorQueue = [];
        $(document).ready(function() {

            $("#enterprise").select2({
                minimumResultsForSearch: 0
            });

            $("#courses").select2({
                minimumResultsForSearch: 0
            });


            $(document).on("click", ".registration", function () {
                const enterprise = $(this).data("enterprise");
                const course = $(this).data("course");
                const user = $(this).data("user");
                const distributor = $(this).data("distributor");

                $.ajax({
                    url: '{{ route("support.distributors.inscriptions.massives.enroll") }}',
                    type: 'POST',
                    data: {
                        enterprise: enterprise,
                        distributor: distributor,
                        course: course,
                        user: user,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.message, "Operación exitosa", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-bottom-right"
                            });


                            errorQueue.shift();

                        } else {
                            toastr.error(response.message || "Error al inscribir al usuario.");
                        }
                        showNextError();
                        $("#error-modal").modal("hide");
                    },
                    error: function (xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                        $("#error-modal").modal("hide");
                    }
                });
            });

            $(document).on("click", ".registration-close", function () {
                errorQueue.shift();
                $("#error-modal").modal("hide");
            });


            $('#error-modal').on('hidden.bs.modal', function () {
                // Usamos un retraso para garantizar que el modal se haya cerrado completamente antes de abrir el siguiente
                setTimeout(() => {
                    showNextError();
                }, 300);
            });

            function showNextError() {

                $('.registration').attr('data-enterprise', '').attr('data-course', '').attr('data-user', '');

                if (errorQueue.length === 0) {
                    $("#error-modal").modal("hide");

                    // Restablecer los campos una vez que se han procesado todos los errores
                    $('#users').val(0).trigger('change');
                    $('#courses').val(0).trigger('change');
                    $('#enterprise').val(0).trigger('change');

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', false);
                    return;
                }

                let error = errorQueue[0]; // Mostrar el primer error sin eliminarlo aún


                // Asignar datos al modal
                $('.registration')
                    .attr('data-enterprise', error.enterprise_enroll)
                    .attr('data-course', error.course_enroll)
                    .attr('data-user', error.customer_enroll);

                $(".customer").text(error.customer_name || 'N/A');
                $(".course").text(error.course || 'N/A');
                $(".start").text(error.enroll_start || 'N/A');
                $(".expire").text(error.enroll_expire || 'N/A');

                // Mostrar el modal con un pequeño retraso para asegurar que se abra correctamente
                setTimeout(() => {
                    $("#error-modal").modal("show");
                }, 300);
            }


            $('#enterprise').on('change', function() {
                var enterpriseId = $(this).val();

                $.ajax({
                    url: '{{ route('support.distributors.inscriptions.massives.get.users') }}',
                    type: 'POST',
                    data: {
                        enterprise: enterpriseId
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        $('#users').empty().select2({
                            data: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            placeholder: "Seleccionar usuarios"
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                    }
                });

                $.ajax({
                    url: '{{ route('support.distributors.inscriptions.massives.get.courses') }}',
                    type: 'POST',
                    data: {
                        enterprise: enterpriseId
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        $('#courses').empty().select2({
                            data: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            placeholder: "Seleccionar un curso"
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                    }
                });

            });


            $('#user').on('change', function(e) {
                var $submitButton = $('button[type="submit"]');
                if ($submitButton.prop('disabled')) {
                    $submitButton.prop('disabled', false);
                }
            });


            $("#formInscriptions").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    'course': {
                        required: true,
                    },
                    'enterprises': {
                        required: true,
                    },
                    'users': {
                        required: true,
                    },
                },
                messages: {
                    'course': {
                        required: "Es necesario una opción.",
                    },
                    'enterprises': {
                        required: "Es necesario una opción.",
                    },
                    'users': {
                        required: "Es necesario una opción.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formInscriptions');
                    var formData = new FormData($form[0]);
                    var distributor = $("#distributor").val();
                    var enterprise = $("#enterprise").val();
                    var courses = $("#courses").val();
                    var users = $("#users").val();

                    formData.append('distributor', distributor);
                    formData.append('enterprise', enterprise);
                    formData.append('courses', courses);
                    formData.append('users', users);

                    var $submitButton = $('button[type="submit"]');
                    $submitButton.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('support.distributors.inscriptions.massives.store') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {

                            if(response.success == true){

                                toastr.success(response.message, "Operación exitosa", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-bottom-right"
                                });

                                $('#users').val(0).trigger('change');
                                $('#courses').val(0).trigger('change');
                                $('#enterprise').val(0).trigger('change');

                                $submitButton.prop('disabled', false);

                            }else if (response.errors && response.errors.length > 0) {
                                errorQueue = [...response.errors]; // Cargar todos los errores en la cola
                                showNextError(); // Mostrar el primer error
                            }


                        }
                    });

                }

            });




        });

    </script>



@endpush
