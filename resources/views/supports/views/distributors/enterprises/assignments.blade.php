@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formEnterprises" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-update-url="{{ route('support.distributors.enterprises.assignments.update') }}"
                      data-redirect-url-template="{{ route('support.distributors.enterprises', ':slack') }}">

                    {{ csrf_field() }}

                    <input  id="slack" name="slack" type="hidden" value="{{ $distributor->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Asignacion empresas</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
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
    <script src="{{ asset('supports/js/views/distributors/enterprises/assignments.js') }}"></script>
@endpush



