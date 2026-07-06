@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->id }}">
                    <input type="hidden" id="course" name="course" value="{{ $course->id }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Reporte usuarios</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona la modalidad para generar el reporte de usuarios del curso.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="input-group">
                                        {!! Form::select('modalitie', $modalities, null , ['class' => 'select2 form-control' ,'name' => 'modalitie', 'id' => 'modalitie' ]) !!}
                                    </div>
                                </div>
                                <label id="modalitie-error" class="error d-none" for="modalitie"></label>
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
                    modalitie: {
                        required: true,
                    },
                },
                messages: {
                    modalitie: {
                        required: "Es necesario una opción.",
                    },
                },
                submitHandler: function(form) {

                    var query = {
                        modalitie: $("#modalitie").val(),
                        enterprise: $("#enterprise").val(),
                        course: $("#course").val(),
                    }

                    var url = "{{ route('manager.enterprises.courses.generate') }}?" + $.param(query);

                    window.location = url;

                }

            });




        });

    </script>



@endpush



