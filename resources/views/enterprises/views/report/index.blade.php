@extends('layouts.managers')


@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Reporte — ' . $course->title])
@endsection
@section('content')


    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formReport" role="form" onSubmit="return false"
                      data-generate-url="{{ route('enterprise.courses.generate') }}">
                    {{ csrf_field() }}
                    <input type="hidden" id="enterprise_id" name="enterprise" value="{{ $enterprises->id ?? '' }}">
                    <input type="hidden" id="course_id" name="course" value="{{ $course->id }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Reporte — {{ $course->title }}</h5>
                        </div>
                        <p class="card-subtitle mb-4">
                            Selecciona la modalidad y descarga el reporte de participantes en Excel.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Modalidad</label>
                                    {!! Form::select('modalitie', $listmodalities, null, ['class' => 'select2 form-control', 'id' => 'modalitie']) !!}
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
<script src="{{ asset('enterprises/js/views/report/index.js') }}"></script>
@endpush
