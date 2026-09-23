@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Reporte usuarios'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" enctype="multipart/form-data" role="form"
                  data-generate-url="{{ route('manager.enterprises.users.generate') }}">

                {{ csrf_field() }}

                <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->id }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reporte usuarios</h6>
                        <p class="text-muted small mb-0">
                            Selecciona la modalidad para generar el reporte de usuarios de la empresa.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Modalidad</label>
                                {!! Form::select('modalitie', $modalities, null , ['class' => 'select2 form-control' ,'name' => 'modalitie', 'id' => 'modalitie' ]) !!}
                                <label id="modalitie-error" class="error d-none" for="modalitie"></label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
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
                    <p class="text-muted mb-0">El reporte se genera con los usuarios de la empresa según la modalidad seleccionada.</p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/users/report.js') }}"></script>
@endpush



