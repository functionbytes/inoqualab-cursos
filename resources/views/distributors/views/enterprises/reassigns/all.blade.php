@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Reasignar a otra empresa'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formCourses" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-update-url="{{ route('distributor.enterprises.users.reassign.all') }}"
                      data-redirect-url-template="{{ route('distributor.enterprises.users', ':slack') }}">

                    {{ csrf_field() }}

                    <input type="hidden" id="slack" name="slack" value="{{ $enterprise->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Reasignar a otra empresa</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Empresa</label>
                                        <input type="text" class="form-control" value="{{ $enterprise->title }}" disabled>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Empresa a reasignar</label>
                                    <div class="input-group">
                                        {!! Form::select('enterprise', $enterprises, null , ['class' => 'select2 form-control'  ,'name' => 'enterprise', 'id' => 'enterprise' ]) !!}
                                    </div>
                                    <label id="enterprise-error" class="error d-none" for="enterprise"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Usuarios a reasignar</label>
                                    <div class="input-group">
                                        {!! Form::select('users[]',$users, null , ['class' => 'select2 form-control' ,'name' => 'users', 'id' => 'users', 'multiple' => 'multiple']) !!}
                                    </div>
                                    <label id="users-error" class="error d-none" for="users"></label>
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
    <script src="{{ asset('distributors/js/enterprises/reassigns/all.js') }}"></script>
@endpush



