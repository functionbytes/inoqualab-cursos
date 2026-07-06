@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input  id="old" name="old" type="hidden" value="{{ $course->slack }}">
                    <input  id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Reasignar usuarios</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona el curso destino y los usuarios que deseas reasignar.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Curso</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"  value="{{ $course->title }}" placeholder="Ingresar celular" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Curso a reacionar</label>
                                    <div class="input-group">
                                        {!! Form::select('course', $courses, null , ['class' => 'select2 form-control' ,'name' => 'course', 'id' => 'course' ]) !!}
                                    </div>
                                    <label id="course-error" class="error d-none" for="course"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Usuarios</label>
                                    <div class="input-group">
                                        {!! Form::select('user[]', $users, null , ['class' => 'select2 form-control' ,'name' => 'user', 'id' => 'user' ]) !!}
                                    </div>
                                    <label id="user-error" class="error d-none" for="user"></label>
                                </div>
                            </div>
                        </div>

                    </div>

                     <div class="col-12"><div class="action-form border-top mt-4">
                        <div class="text-center">
                            <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                Guardar
                            </button>
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

            $("#formReport").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    course: {
                        required: true,
                    },
                    'user[]': {
                        required: true,
                    },
                },
                messages: {
                    course: {
                        required: "Es necesario una opción.",
                    },
                    'user[]': {
                        required: "Es necesario una opción.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formReport');
                    var formData = new FormData($form[0]);
                    var slack = $("#slack").val();
                    var old = $("#old").val();
                    var enterprise = $("#enterprises").val();
                    var user = $("#user").val();

                    formData.append('slack', slack);
                    formData.append('old', old);
                    formData.append('enterprises', enterprise);
                    formData.append('user', user);

                    $.ajax({
                        url: "/manager/enterprises/courses/action/reasign",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(d) {
                            window.location.href = "{{ route('manager.enterprises.courses.view',  [$enterprise->slack, $course->slack] ) }}";
                        }
                    });

                }

            });




        });

    </script>



@endpush



