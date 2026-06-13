@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formReport" role="form" onSubmit="return false">
                    {{ csrf_field() }}

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Reporte de facturas</h5>
                        </div>
                        <p class="card-subtitle mb-4">
                            Filtra y descarga el reporte de facturas en Excel.
                        </p>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Distribuidor</label>
                                    {!! Form::select('distributor', $distributors, null, ['class' => 'select2 form-control', 'id' => 'distributor']) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Método de pago</label>
                                    {!! Form::select('method', $methods, null, ['class' => 'select2 form-control', 'id' => 'method']) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Condición</label>
                                    {!! Form::select('condition', $conditions, null, ['class' => 'select2 form-control', 'id' => 'condition']) !!}
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
    $("#formReport").submit(function(e) {
        e.preventDefault();
        var query = {
            distributor: $("#distributor").val(),
            method: $("#method").val(),
            condition: $("#condition").val(),
        };
        window.location = "{{ route('distributor.invoices.generate') }}?" + $.param(query);
    });
});
</script>
@endpush
