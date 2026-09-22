@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Visualizar contactenos'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">


                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Visualizar contactenos</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para permitirte  introducir nueva información de manera sencilla y estructurada. A continuación, se presentan varios campos que deberás completar con los datos requeridos.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Nombres</label>
                                        <input type="text" class="form-control" id="firstname"  name="firstname" disabled  value="{{ $contact->firstname }}" placeholder="Ingresar nombres">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="lastname"  name="lastname" disabled value="{{ $contact->lastname }}" placeholder="Ingresar apellido">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Celular</label>
                                        <input type="text" class="form-control" id="identification"  name="identification" disabled value="{{ $contact->cellphone }}" placeholder="Ingresar identificación">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha</label>
                                    <input type="text" class="form-control"   value="{{ $contact->created_at }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('reviewed', $revieweds, $contact->reviewed , ['class' => 'select2 form-control','id' => 'reviewed' , 'disabled' => 'disabled']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Correo electronico</label>
                                        <input type="text" class="form-control" id="email"  name="email" disabled value="{{ $contact->email }}" placeholder="Ingresar profección">
                                    </div>
                                </div>
                            </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="col-form-label">Mensaje</label>
                                <div class="quill-wrapper disabled">
                                    <div id="messages">{{ $contact->message }}</div>
                                </div>
                                <label id="message-error" class="error d-none" for="message"></label>
                            </div>
                        </div>


                    </div>

            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('supports/js/views/contacts/view.js') }}"></script>
@endpush



