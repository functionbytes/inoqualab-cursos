@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar noticia'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <div id="blogs-edit"
                 data-config='@php $__jsonInline1 = [
                    "routes" => [
                        "update" => route("manager.blogs.update"),
                        "index" => route("manager.blogs"),
                        "thumbnails" => route("manager.blogs.thumbnails"),
                        "thumbnailsGet" => route("manager.blogs.thumbnails.get", ":item"),
                        "thumbnailDelete" => route("manager.blogs.thumbnails.delete", ":id"),
                    ],
                 ]; @endphp@json($__jsonInline1)'>

                <form id="formBlogs" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}


                    <input type="hidden" id="id" name="id" value="{{ $blog->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $blog->slack }}">
                    <input type="hidden" id="status" name="status" value="{{ $thumbnail }}">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <textarea class="d-none" id="content" name="content">{!! clean($blog->content, 'content') !!}</textarea>
                    <textarea class="d-none" id="description" name="description">{!! clean($blog->description, 'content') !!}</textarea>
                    <input type="hidden" id="thumbnail" name="thumbnail">

                    <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Imagen</h6>
                        <p class="text-muted mb-3">
                            Actualiza la imagen de portada de la noticia si deseas reemplazarla.
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

                        <h6 class="fw-bold text-dark mb-1">Editar noticia</h6>

                        <p class="text-muted mb-3">
                            Actualiza los datos de la noticia. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title"  name="title"  value="{{ $blog->title }}" placeholder="Ingresar titulo">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Categoria</label>
                                <div class="input-group">
                                    {!! Form::select('categorie', $categories, $blog->categorie_id, ['class' => 'select2 form-control' , 'id' => 'categorie']) !!}
                                </div>
                                <label id="categorie-error" class="error d-none" for="categorie"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, $blog->available, ['class' => 'select2 form-control', 'id' => 'available' ]) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="date"  name="date"  value="{{ $blog->date_at }}" >
                                </div>
                                <label id="date-error" class="error d-none" for="date"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Tags</label>
                                <div class="input-group">
                                    {{-- Form::select espera IDs, no modelos: al pasarle la colección
                                         $blog->tags no preseleccionaba nada, y como el JS envía lo que
                                         haya en el select, guardar sin tocar las etiquetas las borraba
                                         todas (update() hace detach() cuando llega vacío). --}}
                                    {!! Form::select('tags[]', $tags, $selectedTags, ['class' => 'select2 form-control'  , 'multiple' => 'multiple' , 'id' => 'tags']) !!}
                                </div>
                                <label id="tags-error" class="error d-none" for="tags"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <div class="quill-wrapper">
                                    <div id="descriptions">{!! clean($blog->description, 'content') !!}</div>
                                </div>
                                <label id="description-error" class="error d-none" for="description"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Contenido</label>
                                <div class="quill-wrapper">
                                    <div id="contents">{!! clean($blog->content, 'content') !!}</div>
                                </div>
                                <label id="content-error" class="error d-none" for="content"></label>
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
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre las noticias</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Se muestran en el blog del sitio público, agrupadas por categoría y con las etiquetas seleccionadas.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ asset('managers/js/views/blogs/blogs/edit.js') }}"></script>
@endpush

