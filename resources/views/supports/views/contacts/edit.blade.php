@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Editar contactenos'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formContacs" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('support.contacts.update') }}"
                  data-redirect-url="{{ route('support.contacts') }}">

                {{ csrf_field() }}

                <input type="hidden" id="description" name="description" value="{{ $contact->description }}">
                <input type="hidden" id="id" name="id" value="{{ $contact->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $contact->slack }}">
                <input type="hidden" id="statuSignatures" name="statuSignatures" value="true">
                <input type="hidden" id="statuThumbnails" name="statuThumbnails" value="true">
                <input type="hidden" id="edit" name="edit" value="true">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Editar contáctenos</h6>
                        <p class="text-muted small mb-0">
                            Revisa la solicitud recibida y actualiza su estado de revisión.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" disabled value="{{ $contact->firstname }}" placeholder="Ingresar nombres">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" disabled value="{{ $contact->lastname }}" placeholder="Ingresar apellido">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Celular</label>
                                <input type="text" class="form-control" id="identification" name="identification" disabled value="{{ $contact->cellphone }}" placeholder="Ingresar identificación">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha</label>
                                <input type="text" class="form-control" value="{{ $contact->created_at }}" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('reviewed', $revieweds, $contact->reviewed , ['class' => 'select2 form-control','id' => 'reviewed']) !!}
                                </div>
                                <label id="reviewed-error" class="error d-none" for="reviewed"></label>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Correo electronico</label>
                                <input type="text" class="form-control" id="email" name="email" disabled value="{{ $contact->email }}" placeholder="Ingresar profección">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Mensaje</label>
                                <div class="quill-wrapper">
                                    <div id="messages">{{ strip_tags($contact->message) }}</div>
                                </div>
                                <label id="message-error" class="error d-none" for="message"></label>
                            </div>

                            <div class="col-12">
                                <div class="errors d-none"></div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre las solicitudes</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Los datos del remitente no se pueden modificar. Solo puedes actualizar el <strong>Estado</strong> para marcar la solicitud como revisada.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('supports/js/views/contacts/edit.js') }}"></script>
@endpush



