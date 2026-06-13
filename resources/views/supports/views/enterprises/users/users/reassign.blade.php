@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formReassign" role="form" onSubmit="return false">
                    {{ csrf_field() }}
                    <input type="hidden" name="slack" value="{{ $user->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Reasignar usuario — {{ $user->firstname }} {{ $user->lastname }}</h5>
                            <div class="ms-auto">
                                <a href="{{ route('support.enterprises.users.view', $user->slack) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Volver
                                </a>
                            </div>
                        </div>
                        <p class="card-subtitle mb-4">
                            Selecciona la empresa destino para reasignar a este usuario. La empresa actual es <strong>{{ $enterprise->title ?? '-' }}</strong>.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Empresa destino</label>
                                    {!! Form::select('enterprise', $enterprises, null, ['class' => 'select2 form-control', 'id' => 'enterprise_select']) !!}
                                    <label id="enterprise_select-error" class="error d-none" for="enterprise_select"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="action-form border-top mt-4">
                            <div class="text-center p-3">
                                <button type="submit" class="btn btn-primary px-4 w-100">Reasignar</button>
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
    $("#formReassign").validate({
        rules: { enterprise: { required: true } },
        messages: { enterprise: { required: "Selecciona una empresa." } },
        submitHandler: function(form) {
            var formData = $(form).serialize();
            $.ajax({
                url: "{{ route('support.enterprises.users.reassign.single') }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: "POST",
                data: formData,
                success: function() {
                    toastr.success('Usuario reasignado correctamente.');
                    setTimeout(function() { window.location.href = "{{ route('support.enterprises.users', $enterprise->slack ?? '') }}"; }, 1000);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(k, v) { toastr.error(v[0]); });
                    } else {
                        toastr.error('Error al reasignar el usuario.');
                    }
                }
            });
        }
    });
});
</script>
@endpush
