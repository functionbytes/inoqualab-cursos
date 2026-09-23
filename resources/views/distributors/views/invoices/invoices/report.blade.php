@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Reporte de facturas'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" role="form" onSubmit="return false"
                  data-generate-url="{{ route('distributor.invoices.generate') }}">
                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reporte de facturas</h6>
                        <p class="text-muted small mb-0">
                            Filtra y descarga el reporte de facturas en Excel.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Método de pago</label>
                                {!! Form::select('method', $methods, null, ['class' => 'select2 form-control', 'id' => 'method']) !!}
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Condición</label>
                                {!! Form::select('condition', $conditions, null, ['class' => 'select2 form-control', 'id' => 'condition']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Descargar reporte
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre este reporte</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">El reporte se descarga en Excel con las facturas que coincidan con los filtros seleccionados.</p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('distributors/js/invoices/invoices/report.js') }}"></script>
@endpush
