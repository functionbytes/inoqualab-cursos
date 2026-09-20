@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100" id="blogs-edit"
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

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                         <div class="dropzone dz-clickable" id="thumbnail">
                                <div class="fallback">
                                       <input type="file" hidden name="file">
                                </div>
                         </div> 
                         <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <div class="card-body border-top">
                        
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar noticia</h5>
                        </div>

                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos de la noticia. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title"  value="{{ $blog->title }}" placeholder="Ingresar titulo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Categoria</label>
                                    <div class="input-group">
                                        {!! Form::select('categorie', $categories, $blog->categorie_id, ['class' => 'select2 form-control' , 'id' => 'categorie']) !!}
                                    </div>
                                    <label id="categorie-error" class="error d-none" for="categorie"></label>   
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $blog->available, ['class' => 'select2 form-control', 'id' => 'available' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>   
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="date"  name="date"  value="{{ $blog->date_at }}" >
                                    </div>
                                    <label id="date-error" class="error d-none" for="date"></label>   
                            </div>    
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Tags</label>
                                    <div class="input-group">
                                        {{-- Form::select espera IDs, no modelos: al pasarle la colección
                                             $blog->tags no preseleccionaba nada, y como el JS envía lo que
                                             haya en el select, guardar sin tocar las etiquetas las borraba
                                             todas (update() hace detach() cuando llega vacío). --}}
                                        {!! Form::select('tags[]', $tags, $selectedTags, ['class' => 'select2 form-control'  , 'multiple' => 'multiple' , 'id' => 'tags']) !!}
                                    </div>
                                    <label id="tags-error" class="error d-none" for="tags"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Descripción</label>
                                    <div class="quill-wrapper">
                                        <div id="descriptions">{!! clean($blog->description, 'content') !!}</div>
                                        <label id="description-error" class="error d-none" for="description"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Contenido</label>
                                    <div class="quill-wrapper">
                                        <div id="contents">{!! clean($blog->content, 'content') !!}</div>
                                    </div>
                                    <label id="content-error" class="error d-none" for="content"></label>
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
    <script src="{{ asset('managers/js/views/blogs/blogs/edit.js') }}"></script>
@endpush

