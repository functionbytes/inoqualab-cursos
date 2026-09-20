@extends('layouts.managers')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        
        <div class="card w-100">

            <form id="formInscriptions" enctype="multipart/form-data" role="form"
                  data-enroll-url="{{ route('support.enterprises.inscriptions.enroll') }}"
                  data-store-url="{{ route('support.enterprises.inscriptions.store') }}">

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
                                    {!! Form::select('course',$courses, null , ['class' => 'select2 form-control' ,'name' => 'course', 'id' => 'course']) !!}
                                </div>
                                <label id="course-error" class="error d-none" for="course"></label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Usuarios</label>
                                <div class="input-group">
                                    {!! Form::select('user',$users, null , ['class' => 'select2 form-control' ,'name' => 'user', 'id' => 'user']) !!}
                                </div>
                                <label id="user-error" class="error d-none" for="user"></label>
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



<div id="error-modal" class="modal fade">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                <h4 class="my-0">Este usuario ya esta inscrito a este curso</h4>
                <p><span class="course"> <span> con fecha <span class="start"> <span></span>
                <div class="row justify-content-center mt-20  ">
                    <div class="col-sm-12 col-md-5 w-100">
                        <a class="btn btn-primary w-100 registration" data-course="" data-user="" data-enterprise="" >Inscribir nuevamente</a>
                        <button type="button" class="btn btn-primary w-100 mt-1 registration-close" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/enterprises/enterprises/enterprises/inscription.js') }}"></script>
@endpush