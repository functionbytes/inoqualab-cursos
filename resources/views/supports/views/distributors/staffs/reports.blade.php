@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Reporte de empleados'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-generate-url="{{ route('support.distributors.staffs.reports.generate') }}">

                    {{ csrf_field() }}

                    <input type="hidden" id="distributor" name="distributor" value="{{ $distributor->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Reporte de empleados</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona el estado y el rango de fechas de registro para generar el reporte de empleados de {{ Str::upper($distributor->title) }}.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', ['' => 'Todos', '1' => 'Activo', '0' => 'Inactivo'], null, ['class' => 'select2 form-control', 'name' => 'available', 'id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input type="text" id="range" name="range" class="form-control daterange" />
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar fs-5"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border-top pt-1 mt-4">
                                    <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('supports/js/views/distributors/staffs/reports.js') }}"></script>
@endpush
