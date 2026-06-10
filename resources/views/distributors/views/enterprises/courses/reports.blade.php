@extends('layouts.distributors')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false">
                    {{ csrf_field() }}
                    <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->id }}">
                    <input type="hidden" id="course" name="course" value="{{ $course->id }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Reporte de usuarios — {{ $course->title }}</h5>
                        </div>
                        <p class="card-subtitle mb-3">
                            Selecciona la modalidad y descarga el reporte en Excel.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Modalidad</label>
                                    {!! Form::select('modalitie', $modalities, null, ['class' => 'select2 form-control', 'id' => 'modalitie']) !!}
                                    <label id="modalitie-error" class="error d-none" for="modalitie"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="action-form border-top mt-4">
                            <div class="text-center p-3">
                                <button type="submit" class="btn btn-primary px-4 w-100">
                                    <i class="fas fa-download me-1"></i> Descargar reporte
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
<script>
$(document).ready(function() {
    $("#formReport").validate({
        rules: { modalitie: { required: true } },
        messages: { modalitie: { required: "Selecciona una modalidad." } },
        submitHandler: function() {
            var query = {
                modalitie: $("#modalitie").val(),
                enterprise: $("#enterprise").val(),
                course: $("#course").val(),
            };
            window.location = "{{ route('distributor.enterprises.courses.generate') }}?" + $.param(query);
        }
    });
});
</script>
@endpush
