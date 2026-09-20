@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100" id="courses-create"
                 data-config='@php $__jsonInline1 = [
                    "routes" => [
                        "store" => route("manager.courses.store"),
                        "index" => route("manager.courses"),
                        "thumbnails" => route("manager.courses.thumbnails"),
                        "thumbnailDelete" => route("manager.courses.thumbnails.delete", ":id"),
                    ],
                 ]; @endphp@json($__jsonInline1)'>

                <form id="formCourses" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <textarea class="d-none" id="who" name="who"></textarea>
                    <textarea class="d-none" id="learn" name="learn"></textarea>
                    <textarea class="d-none" id="short" name="short"></textarea>
                    <textarea class="d-none" id="requirement" name="requirement"></textarea>
                    <textarea class="d-none" id="detail" name="detail"></textarea>
                    <input type="hidden" id="status" name="status" value="false">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="thumbnail" name="thumbnail">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Sube la imagen de portada del curso. Se mostrará en el catálogo y en la página del curso, así que se recomienda una imagen clara y de buena calidad.
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
                            <h5 class="mb-0">Crear curso</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos del curso. Los campos marcados como obligatorios deben diligenciarse para poder publicarlo.
                        </p>
                        <div class="row">

                            <div class="col-12">
                                <div class="form-section-title">Información general</div>
                                <p class="form-section-desc">Datos básicos que identifican el curso: título, video de presentación y duración.</p>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title"  name="title" value=""  placeholder="Ingresar titulo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Video</label>
                                        <input type="text" class="form-control" id="film"  name="film" value=""  placeholder="Ej: https://www.youtube.com/watch?v=...">
                                        <small class="form-text text-muted">Enlace de YouTube o Vimeo para el video de vista previa del curso (opcional)</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Duración <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="duration"  name="duration" value=""  placeholder="Ingresar duración">
                                        <small class="form-text text-muted">Horas totales del curso</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Dias <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="day"  name="day" value=""  placeholder="Ingresar dias">
                                        <small class="form-text text-muted">Días de acceso al contenido</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-section-title">Precio y promoción</div>
                                <p class="form-section-desc">Define el precio del curso y el descuento promocional aplicable.</p>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Precio <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="price"  name="price" value=""  placeholder="Ej: 75000">
                                        <small class="form-text text-muted">Precio en COP</small>
                                </div>
                            </div>

                            <div class="col-6 d-none divDiscount">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Descuento</label>
                                        <input type="text" class="form-control" id="discount"  name="discount" value=""  placeholder="Ingresar descuento">
                                        <small class="form-text text-muted">Porcentaje de descuento (1-100)</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-section-title">Certificación y categoría</div>
                                <p class="form-section-desc">Configura el certificador, la entidad certificadora y la categoría del curso.</p>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Certificador <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('certification', $certifications, null , ['class' => 'select2 form-control','id' => 'certification']) !!}
                                    </div>
                                    <label id="certification-error" class="error d-none" for="certification"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Entidad certificadora <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('certifier', $certifiers, null , ['class' => 'select2 form-control','id' => 'certifier']) !!}
                                    </div>
                                    <label id="certifier-error" class="error d-none" for="certifier"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Categoria <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('categorie', $categories, null , ['class' => 'select2 form-control','id' => 'categorie']) !!}
                                    </div>
                                    <label id="categorie-error" class="error d-none" for="categorie"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Certificación <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('certificate', $conditions, null  , ['class' => 'select2 form-control','id' => 'certificate']) !!}
                                    </div>
                                    <label id="certificate-error" class="error d-none" for="certificate"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-section-title">Visibilidad y configuración</div>
                                <p class="form-section-desc">Controla dónde aparece el curso y sus opciones de examen, pago y nivel.</p>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Destacado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('featured', $conditions, null , ['class' => 'select2 form-control','id' => 'featured']) !!}
                                    </div>
                                    <label id="featured-error" class="error d-none" for="featured"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Nivel</label>
                                    <div class="input-group">
                                        <select class="select2 form-control" id="level" name="level">
                                            <option value="">Sin definir</option>
                                            <option value="Principiante">Principiante</option>
                                            <option value="Intermedio">Intermedio</option>
                                            <option value="Avanzado">Avanzado</option>
                                        </select>
                                    </div>
                                    <label id="level-error" class="error d-none" for="level"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Calificación (0 a 5)</label>
                                    <input type="number" class="form-control" id="rating" name="rating" min="0" max="5" step="0.1" value="0" placeholder="Ej: 4.5">
                                    <label id="rating-error" class="error d-none" for="rating"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Website <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('website', $conditions, null, ['class' => 'select2 form-control','id' => 'website']) !!}
                                    </div>
                                    <label id="website-error" class="error d-none" for="website"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Pago <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('payment', $conditions, null , ['class' => 'select2 form-control','id' => 'payment']) !!}
                                    </div>
                                    <label id="payment-error" class="error d-none" for="payment"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Examen <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('exam', $conditions, null , ['class' => 'select2 form-control','id' => 'exam']) !!}
                                    </div>
                                    <label id="exam-error" class="error d-none" for="exam"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Promocion <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('promotion', $conditions,null, ['class' => 'select2 form-control','id' => 'promotion']) !!}
                                    </div>
                                    <label id="promotion-error" class="error d-none" for="promotion"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>


                    <div class="col-12">
                        <div class="form-section-title">Contenido descriptivo</div>
                        <p class="form-section-desc">Descripción, requisitos y contenido que verán los alumnos antes de inscribirse.</p>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Descripcion</label>
                            <div class="quill-wrapper">
                                <div  id="shorts"></div>
                            </div>
                            <label id="short-error" class="error d-none" for="short"></label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Detalle</label>
                            <div class="quill-wrapper">
                                <div  id="details"></div>
                            </div>
                            <label id="detail-error" class="error d-none" for="detail"></label>
                        </div>
                    </div>

                    <div class="col-12">
                            <div class="mb-3">
                                <label class="col-form-label">Lo que aprenderas</label>
                                <div class="quill-wrapper">
                                    <div  id="learns"></div>
                                </div>
                                <label id="learn-error" class="error d-none" for="learn"></label>
                            </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Para quien es el curso</label>
                            <div class="quill-wrapper">
                                <div  id="whos"></div>
                            </div>
                            <label id="who-error" class="error d-none" for="who"></label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="col-form-label">Requerimientos</label>
                            <div class="quill-wrapper">
                                <div  id="requirements"></div>
                            </div>
                            <label id="requirement-error" class="error d-none" for="requirement"></label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="action-form border-top mt-4">
                            <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                Guardar
                            </button>
                            <a href="{{ route('manager.courses') }}" class="btn btn-light px-4 waves-effect mt-2 w-100 text-center">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/courses/courses/_quill-editors.js') }}"></script>
<script src="{{ asset('managers/js/views/courses/courses/create.js') }}"></script>
@endpush
