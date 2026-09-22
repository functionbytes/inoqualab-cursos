@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear certificación'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formCertification" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "store" => route("manager.certifications.store"),
                      "index" => route("manager.certifications"),
                      "thumbnails" => route("manager.certifications.thumbnails"),
                      "thumbnailDelete" => route("manager.certifications.thumbnails.delete", ":id"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <input type="hidden" id="description" name="description" value="">
                <input type="hidden" id="id" name="id" value="">
                <input type="hidden" id="slack" name="slack" value="">
                <input type="hidden" id="status" name="status" value="false">
                <input type="hidden" id="edit" name="edit" value="true">
                <input type="hidden" id="thumbnail" name="thumbnail">

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Cerificado</h6>
                        <p class="text-muted mb-3">
                            Sube la imagen del certificado. El formato recomendado es JPG o PNG.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Crear certificación</h6>
                        <p class="text-muted mb-3">
                            Completa los datos de la certificación. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
                        </p>
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <div class="quill-wrapper">
                                    <div id="descriptions"></div>
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
                    <h6 class="mb-0 fw-bold">Sobre las certificaciones</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Solo las certificaciones con estado <strong>activo</strong> quedan disponibles para asignación en el resto del sistema.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/certifications/create.js') }}"></script>
@endpush
