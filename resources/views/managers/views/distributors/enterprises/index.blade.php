@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formEnterprises" enctype="multipart/form-data" role="form"
                      data-update-url="{{ route('manager.distributors.enterprises.update') }}"
                      data-navegation-url="{{ route('manager.distributors.navegation', ':slack') }}">

                    {{ csrf_field() }}

                    <input  id="slack" name="slack" type="hidden" value="{{ $distributor->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Asignacion empresas</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona las empresas que deseas asignar a este distribuidor. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Empresas</label>
                                    <div class="input-group">
                                        {!! Form::select('enterprises', $enterprises, $enterprise , ['class' => 'select2 form-control' , 'multiple' => 'multiple' ,'name' => 'enterprises', 'id' => 'enterprises' ]) !!}
                                    </div>
                                    <label id="enterprises-error" class="error d-none" for="enterprises"></label>
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
<script src="{{ asset('managers/js/views/distributors/enterprises/index.js') }}"></script>
@endpush



