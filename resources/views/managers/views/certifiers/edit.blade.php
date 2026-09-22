@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar capacitador'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formCertifier" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('manager.certifiers.update') }}"
                  data-thumbnail-url="{{ route('manager.certifiers.thumbnails') }}"
                  data-signature-url="{{ route('manager.certifiers.signatures') }}"
                  data-thumbnail-get-url="{{ route('manager.certifiers.thumbnails.get', ':item') }}"
                  data-signature-get-url="{{ route('manager.certifiers.signatures.get', ':item') }}"
                  data-thumbnail-delete-url="{{ route('manager.certifiers.thumbnails.delete', ':id') }}"
                  data-signature-delete-url="{{ route('manager.certifiers.signatures.delete', ':id') }}"
                  data-redirect-url="{{ route('manager.certifiers') }}">

                {{ csrf_field() }}

                <input type="hidden" id="description" name="description" value="{{ $certifier->description }}">
                <input type="hidden" id="id" name="id" value="{{ $certifier->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $certifier->slack }}">
                <input type="hidden" id="statuSignatures" name="statuSignatures" value="{{ $signature }}">
                <input type="hidden" id="statuThumbnails" name="statuThumbnails" value="{{ $thumbnail }}">
                <input type="hidden" id="edit" name="edit" value="true">
                <input type="hidden" id="thumbnail" name="thumbnail">
                <input type="hidden" id="signature" name="signature">

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Foto</h6>
                        <p class="text-muted mb-3">
                            Foto del capacitador. Se muestra en el certificado emitido y en su perfil público.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="thumbnail">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Firma</h6>
                        <p class="text-muted mb-3">
                            Firma del capacitador. Se imprime en los certificados que emite.
                        </p>
                        <div class="dropzone dz-clickable" id="signature">
                            <div class="fallback">
                                <input type="file" hidden name="signature">
                            </div>
                        </div>
                        <label id="signature-error" class="error d-none" for="signature"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Editar capacitador</h6>
                        <p class="text-muted mb-3">
                            Actualiza los datos del capacitador. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $certifier->firstname }}" placeholder="Ingresar nombres">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $certifier->lastname }}" placeholder="Ingresar apellido">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Identificación</label>
                                <input type="text" class="form-control" id="identification"  name="identification" value="{{ $certifier->identification }}" placeholder="Ingresar identificación">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Profesión</label>
                                <input type="text" class="form-control" id="profession"  name="profession" value="{{ $certifier->profession }}" placeholder="Ingresar profesión">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, $certifier->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <div class="quill-wrapper">
                                    <div id="descriptions">{!! clean($certifier->description, 'content') !!}</div>
                                </div>
                                <label id="description-error" class="error d-none" for="description"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre los capacitadores</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">La foto y la firma se usan en los certificados emitidos para los cursos donde este capacitador figura como entidad certificadora.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/certifiers/edit.js') }}"></script>
<script src="{{ asset('managers/js/views/certifiers/quill-description.js') }}"></script>
@endpush


