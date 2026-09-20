@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formReport" enctype="multipart/form-data" role="form"
                      data-generate-url="{{ route('manager.enterprises.users.generate') }}">

                    {{ csrf_field() }}

                    <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->id }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Reporte usuarios</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona la modalidad para generar el reporte de usuarios de la empresa.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="input-group">
                                        {!! Form::select('modalitie', $modalities, null , ['class' => 'select2 form-control' ,'name' => 'modalitie', 'id' => 'modalitie' ]) !!}
                                    </div>
                                    <label id="modalitie-error" class="error d-none" for="modalitie"></label>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="border-top pt-1 mt-4">
                                    <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
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
<script src="{{ asset('managers/js/views/enterprises/users/report.js') }}"></script>
@endpush



