@extends('layouts.managers')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        
        <div class="card w-100">

            <form id="formInscriptions" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-redirect-url-template="{{ route('support.distributors.navegation', ':slack') }}">

                {{ csrf_field() }}


                <input id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center">
                        <h5 class="mb-0"> Inscribir usuarios a un curso</h5>

                    </div>
                    <p class="card-subtitle mb-3 mt-3">
                        Este espacio está diseñado para que puedas actualizar y modificar la información de manera
                        eficiente y segura. A continuación, encontrarás diversos campos que
                        corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier
                        información que consideres necesario actualizar para mantener tus datos al día.
                    </p>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Cursos</label>
                                <div class="input-group">
                                    {!! Form::select('course', $courses, null , ['class' => 'select2 form-control' ,'name'
                                    => 'course', 'id' => 'course']) !!}
                                </div>
                                <label id="courses-error" class="error d-none" for="courses"></label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Usuarios</label>
                                <div class="input-group">
                                    {!! Form::select('user[]', $users, null , ['class' => 'select2 form-control' ,'name'
                                    => 'users', 'id' => 'users' , 'multiple' => 'multiple']) !!}
                                </div>
                                <label id="users-error" class="error d-none" for="users"></label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="action-form border-top mt-4">
                    <div class="text-center">
                        <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

</div>

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/distributors/inscription.js') }}"></script>
@endpush