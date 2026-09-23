@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Reporte de usuarios — ' . $enterprise->title])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" role="form"
                  data-generate-url="{{ route('support.enterprises.users.generate') }}">
                {{ csrf_field() }}
                <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->id }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reporte de usuarios — {{ $enterprise->title }}</h6>
                        <p class="text-muted small mb-0">
                            Selecciona el estado y descarga el reporte en Excel.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado</label>
                                {!! Form::select('modalitie', $modalities, null, ['class' => 'select2 form-control', 'id' => 'modalitie']) !!}
                                <label id="modalitie-error" class="error d-none" for="modalitie"></label>
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
                    <p class="text-muted mb-0">El reporte se descarga en Excel con los usuarios de esta empresa según el estado seleccionado.</p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/users/users/report.js') }}"></script>
@endpush
