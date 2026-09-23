@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Visualizar contactenos'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">


                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Visualizar contactenos</h6>
                        <p class="text-muted small mb-0">
                            Este espacio te permite consultar el detalle del mensaje enviado a través del formulario de contacto.
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
                                {!! Form::select('reviewed', $revieweds, $contact->reviewed , ['class' => 'select2 form-control','id' => 'reviewed' , 'disabled' => 'disabled']) !!}
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Correo electronico</label>
                                <input type="text" class="form-control" id="email" name="email" disabled value="{{ $contact->email }}" placeholder="Ingresar profección">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Mensaje</label>
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



