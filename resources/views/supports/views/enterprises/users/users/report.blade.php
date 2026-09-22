@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Reporte de usuarios — ' . $enterprise->title])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formReport" role="form"
                      data-generate-url="{{ route('support.enterprises.users.generate') }}">
                    {{ csrf_field() }}
                    <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->id }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Reporte de usuarios — {{ $enterprise->title }}</h5>
                        </div>
                        <p class="card-subtitle mb-3">
                            Selecciona el estado y descarga el reporte en Excel.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
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
                                    Descargar reporte
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
    <script src="{{ asset('supports/js/enterprises/users/users/report.js') }}"></script>
@endpush
