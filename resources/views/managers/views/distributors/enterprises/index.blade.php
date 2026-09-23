@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Asignacion empresas'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formEnterprises" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('manager.distributors.enterprises.update') }}"
                  data-navegation-url="{{ route('manager.distributors.navegation', ':slack') }}">

                {{ csrf_field() }}

                <input id="slack" name="slack" type="hidden" value="{{ $distributor->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Asignacion empresas</h6>
                        <p class="text-muted small mb-0">
                            Selecciona las empresas que deseas asignar a este distribuidor. Los cambios se guardarán al hacer clic en Guardar.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresas</label>
                                {!! Form::select('enterprises', $enterprises, $enterprise , ['class' => 'select2 form-control' , 'multiple' => 'multiple' ,'name' => 'enterprises', 'id' => 'enterprises' ]) !!}
                                <label id="enterprises-error" class="error d-none" for="enterprises"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre esta asignación</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Solo las empresas asignadas aquí estarán disponibles para este distribuidor.</p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/distributors/enterprises/index.js') }}"></script>
@endpush



