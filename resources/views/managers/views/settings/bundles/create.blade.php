@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear paquete'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formBundles" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "store" => route("manager.bundles.store"),
                      "index" => route("manager.bundles"),
                      "thumbnails" => route("manager.bundles.thumbnails"),
                      "thumbnailDelete" => route("manager.bundles.thumbnails.delete", ":id"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="">
                <input type="hidden" id="slack" name="slack" value="">
                <textarea class="d-none" id="description" name="description"></textarea>
                <textarea class="d-none" id="meta_description" name="meta_description"></textarea>
                <input type="hidden" id="status" name="status" value="false">
                <input type="hidden" id="edit" name="edit" value="true">
                <input type="hidden" id="thumbnail" name="thumbnail">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Imagen</h6>
                        <p class="text-muted small mb-0">
                            Portada del paquete. Se muestra en el catálogo público y en las tarjetas de paquetes.
                        </p>
                         </div>

                         <div class="card-body">
                             <div class="dropzone dz-clickable" id="thumbnail">
                                <div class="fallback">
                                       <input type="file" hidden name="file">
                                </div>
                         </div>
                         <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Crear paquete</h6>
                        <p class="text-muted mb-3">
                            Completa los datos del paquete. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                        </p>

                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title"  name="title" value=""  placeholder="Ingresar titulo">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Precio</label>
                                <input type="text" class="form-control" id="price"  name="price" value=""  placeholder="Ingresar precio">
                                <small class="form-text text-muted">Monto en COP</small>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha inicio</label>
                                <div class="input-group">
                                    <input type="date" id="start_date" name="start_date" class="form-control daterange" />
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha final</label>
                                <div class="input-group">
                                    <input type="date" id="expire_at" name="expire_at" class="form-control daterange" />
                                </div>
                                <small class="text-muted d-block mt-1">Opcional. Vacío = el paquete no vence.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Cursos</label>
                                <div class="input-group">
                                    {!! Form::select('courses[]', $courses, null, ['class' => 'select2 form-control', 'multiple' => 'multiple', 'id' => 'courses', 'data-placeholder' => 'Seleccionar cursos...']) !!}
                                </div>
                                <label id="bundles-error" class="error d-none" for="bundles"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <div class="quill-wrapper">
                                    <div  id="descriptions"></div>
                                </div>
                                <label id="description-error" class="error d-none" for="description"></label>
                            </div>

                        </div>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Información SEO</h6>
                        <p class="text-muted mb-3">Datos opcionales para posicionamiento en buscadores.</p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título SEO</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" value="" placeholder="Ingresar título SEO">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Palabras clave</label>
                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Guardar
                        </button>
                        <a href="{{ route('manager.bundles') }}" class="btn btn-light w-100 text-center">
                            Cancelar
                        </a>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre los paquetes</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Un paquete agrupa varios cursos a un precio combinado. Deja las fechas vacías si el paquete no debe vencer nunca.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/bundles/create.js') }}"></script>
@endpush
