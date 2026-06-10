@extends('layouts.distributors')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formReasign" enctype="multipart/form-data" role="form" onSubmit="return false">
                    {{ csrf_field() }}
                    <input id="old" name="old" type="hidden" value="{{ $course->slack }}">
                    <input id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Reasignar usuarios</h5>
                        </div>
                        <p class="card-subtitle mb-3">
                            Selecciona el nuevo curso y los usuarios a reasignar.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Curso actual</label>
                                    <input type="text" class="form-control" value="{{ $course->title }}" disabled>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Curso destino</label>
                                    {!! Form::select('course', $courses, null, ['class' => 'select2 form-control', 'id' => 'course']) !!}
                                    <label id="course-error" class="error d-none" for="course"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Usuarios</label>
                                    {!! Form::select('user[]', $users, null, ['class' => 'select2 form-control', 'id' => 'user']) !!}
                                    <label id="user-error" class="error d-none" for="user"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="action-form border-top mt-4">
                            <div class="text-center p-3">
                                <button type="submit" class="btn btn-primary px-4 w-100">Guardar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $("#formReasign").validate({
        rules: {
            course: { required: true },
            'user[]': { required: true },
        },
        messages: {
            course: { required: "Selecciona un curso." },
            'user[]': { required: "Selecciona al menos un usuario." },
        },
        submitHandler: function(form) {
            var formData = new FormData(form);
            $.ajax({
                url: "{{ route('distributor.enterprises.action.reasign') }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function() {
                    window.location.href = "{{ route('distributor.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}";
                },
                error: function() { toastr.error('Error al procesar la reasignación.'); }
            });
        }
    });
});
</script>
@endpush
