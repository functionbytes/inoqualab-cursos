@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formImport" enctype="multipart/form-data" role="form" onSubmit="return false">
                    {{ csrf_field() }}
                    <input type="hidden" name="enterprise" value="{{ $enterprise->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Importar usuarios — {{ $enterprise->title }}</h5>
                            <div class="ms-auto">
                                <a href="{{ route('manager.enterprises.users', $enterprise->slack) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Volver
                                </a>
                            </div>
                        </div>
                        <p class="card-subtitle mb-4">
                            Sube un archivo Excel (.xlsx) con la lista de usuarios a importar para la empresa <strong>{{ $enterprise->title }}</strong>.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Archivo Excel</label>
                                    <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv">
                                    <label id="file-error" class="error d-none text-danger small" for="file"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="action-form border-top mt-4">
                            <div class="text-center p-3">
                                <button type="submit" class="btn btn-primary px-4 w-100">
                                    <i class="fas fa-upload me-1"></i> Importar usuarios
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
    $("#formImport").validate({
        rules: { file: { required: true } },
        messages: { file: { required: "Selecciona un archivo." } },
        submitHandler: function(form) {
            var formData = new FormData(form);
            $.ajax({
                url: "{{ route('manager.enterprises.users.importation') }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function(d) { window.location.href = d; },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(k, v) {
                            toastr.error(v[0]);
                        });
                    } else {
                        toastr.error('Error al importar el archivo.');
                    }
                }
            });
        }
    });
});
</script>
@endpush
