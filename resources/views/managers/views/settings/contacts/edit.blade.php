@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formContacs" enctype="multipart/form-data" role="form"
                      data-urls='@json([
                          "update" => route("manager.contacts.update"),
                          "index" => route("manager.contacts"),
                      ])'>

                    {{ csrf_field() }}

                    {{-- Dentro de un atributo hay que escapar: con {!! !!} cualquier
                         comilla doble de la descripción cortaba el value y volcaba
                         el resto del HTML como atributos del input. --}}
                    <input type="hidden" id="description" name="description" value="{{ $contact->description }}">
                    <input type="hidden" id="id" name="id" value="{{ $contact->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $contact->slack }}">
                    <input type="hidden" id="statuSignatures" name="statuSignatures" value="true">
                    <input type="hidden" id="statuThumbnails" name="statuThumbnails" value="true">
                    <input type="hidden" id="edit" name="edit" value="true">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar contáctenos</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza la información de contacto de la empresa. Los cambios se guardarán al hacer clic en Guardar.
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
                                        {!! Form::select('reviewed', $revieweds, $contact->reviewed , ['class' => 'select2 form-control','id' => 'reviewed']) !!}
                                    </div>
                                    <label id="reviewed-error" class="error d-none" for="reviewed"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Correo electronico</label>
                                        <input type="text" class="form-control" id="email"  name="email" disabled value="{{ $contact->email }}" placeholder="Ingresar correo electrónico">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Mensaje</label>
                                    <div class="quill-wrapper">
                                        <div id="messages">{{ strip_tags($contact->message) }}</div>
                                    </div>
                                    <label id="message-error" class="error d-none" for="message"></label>
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

                </form>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/contacts/edit.js') }}"></script>
@endpush
