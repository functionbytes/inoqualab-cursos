@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Resumen de ordenes'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-generate-url="{{ route('support.distributors.orders.resumen.generate') }}">

                {{ csrf_field() }}

                <input type="hidden" id="distributor" name="distributor" value="{{$distributor->id}}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Resumen de ordenes</h6>
                        <p class="text-muted small mb-0">
                            Genera el resumen de órdenes de este distribuidor filtrando por empresa
                            y rango de fechas.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresas</label>
                                {!! Form::select('enterprise', $enterprises, null , ['class' => 'select2 form-control' ,'name' => 'enterprise', 'id' => 'enterprise' ]) !!}
                                <label id="enterprise-error" class="error d-none" for="enterprise"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Fecha</label>
                                <div class="input-group">
                                    <input type="text" id="range" name="range" class="form-control daterange" />
                                    <span class="input-group-text">
                                          <i class="fas fa-calendar fs-5"></i>
                                        </span>
                                </div>
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
                    <h6 class="mb-0 fw-bold">Sobre este resumen</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">El resumen se genera con las órdenes de este distribuidor que coincidan con los filtros seleccionados.</p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('supports/js/views/distributors/orders/resumen/index.js') }}"></script>
@endpush



