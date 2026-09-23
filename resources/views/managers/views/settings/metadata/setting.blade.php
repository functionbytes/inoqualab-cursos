@extends('layouts.managers')



@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar metadata'])
@endsection
@section('content')
    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formMetadata" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "update" => route("manager.settings.metadata.update"),
                      "store" => route("manager.settings.metadata.store"),
                      "get" => route("manager.settings.metadata.get", ":item"),
                      "delete" => route("manager.settings.metadata.delete", ":id"),
                      "dashboard" => route("manager.dashboard"),
                  ]; @endphp@json($__jsonInline1)'>
                {{ csrf_field() }}

                <input type="hidden" id="meta_description" name="meta_description" value="{{ setting('meta_description') }}">
                <input type="hidden" id="slack" name="slack" value="{{ setting('meta_image') }}">
                <input type="hidden" id="statuMetas" name="statuMetas" value="{{ $metadata }}">
                <input type="hidden" id="statuEdit" name="statuEdit" value="true">
                <input type="hidden" id="metadata" name="metadata">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Imagen por defecto</h6>
                        <p class="text-muted small mb-0">
                            Se usa como miniatura social (og:image) cuando un curso, blog o paquete no tiene portada propia.
                        </p>
                        </div>

                        <div class="card-body">
                            <div class="dropzone dz-clickable" id="metadata">
                            <div class="fallback">
                                <input type="file" hidden name="metadata">
                            </div>
                        </div>
                        <label id="metadata-error" class="error d-none" for="metadata"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Metadatos SEO por defecto</h6>
                        <p class="text-muted mb-3">
                            Título, palabras clave y descripción que usan las páginas que no definen sus propios metadatos.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="meta_title" class="form-label fw-semibold">Título</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ setting('meta_title') }}" placeholder="Ingresar título">
                            </div>
                            <div class="col-12">
                                <label for="meta_keywords" class="form-label fw-semibold">Palabras clave</label>
                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ setting('meta_keywords') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <div id="descriptions">{!! clean(setting('meta_description'), 'content') !!}</div>
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
                    <h6 class="mb-0 fw-bold">Sobre estos metadatos</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Estos valores son el respaldo general del sitio: se usan solo cuando una página (curso, blog, sección) no define su propio título, descripción o imagen.</p>

                    <hr class="my-3">

                    <p class="text-muted mb-0">Para editar el SEO de una página específica, usa <strong>Analytics y SEO → Plantillas</strong> o el SEO propio de cada curso/blog.</p>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/metadata/setting.js') }}"></script>
@endpush
