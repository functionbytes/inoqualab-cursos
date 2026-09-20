@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formSetting" enctype="multipart/form-data" role="form"
                      data-urls='@php $__jsonInline1 = [
                          "update" => route("manager.settings.update"),
                          "index" => route("manager.settings"),
                          "logo" => route("manager.settings.logo"),
                          "logoGet" => route("manager.settings.logo.get", ":item"),
                          "logoDelete" => route("manager.settings.logo.delete", ":id"),
                          "favicon" => route("manager.settings.favicon"),
                          "faviconGet" => route("manager.settings.favicon.get", ":item"),
                          "faviconDelete" => route("manager.settings.favicon.delete", ":id"),
                      ]; @endphp@json($__jsonInline1)'>

                    {{ csrf_field() }}


                    <textarea class="d-none" type="hidden"  id="page_description" name="page_description">{!! clean(setting('page_description'), 'content') !!}</textarea>
                    <textarea class="d-none" type="hidden"  id="page_politic" name="page_politic">{!! clean(setting('page_politic'), 'content') !!}</textarea>
                    <textarea class="d-none" type="hidden"  id="page_term" name="page_term">{!! clean(setting('page_term'), 'content') !!}</textarea>
                    <input  type="hidden" id="page_logo" name="page_logo" value="{{ setting('page_logo') }}">
                    <input  type="hidden" id="page_favicon" name="page_favicon" value="{{ setting('page_favicon') }}">
                    <input  type="hidden" id="id" name="id" value="{{ $setting->id }}">
                    <input  type="hidden" id="statuLogo" name="statuLogo" value="{{ $logo }}">
                    <input  type="hidden" id="statuFavicon" name="statuFavicon" value="{{ $favicon}}">
                    <input  type="hidden" id="statuEdit" name="statuEdit" value="false">
                    <input  type="hidden" id="favicon" name="favicon">
                    <input  type="hidden" id="logo" name="logo">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Logo</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="logo">
                            <div class="fallback">
                                <input type="file" hidden name="logo">
                            </div>
                        </div>
                        <label id="logo-error" class="error d-none" for="logo"></label>
                    </div>


                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Favicon</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="favicon">
                            <div class="fallback">
                                <input type="file" hidden name="favicon">
                            </div>
                        </div>
                        <label id="favicon-error" class="error d-none" for="favicon"></label>
                    </div>
                    

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar certificador</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Configura los datos generales del sitio: información de contacto, redes sociales, políticas, términos y preferencias del portal.
                        </p>

                        <div class="row">

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Nombres</label>
                                                <input type="text" class="form-control" id="page_title"  name="page_title" value="{{ setting('page_title') }}" placeholder="Ingresar titulo">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Copyright</label>
                                                <input type="text" class="form-control" id="page_copyright"  name="page_copyright" value="{{ setting('meta_title')  }}" placeholder="Ingresar copyright">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Correo electronico</label>
                                                <input type="text" class="form-control" id="page_email"  name="page_email" value="{{ setting('page_email')  }}" placeholder="Ingresar correo electronico">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Dirección</label>
                                                <input type="text" class="form-control" id="page_address"  name="page_address" value="{{ setting('page_address')  }}" placeholder="Ingresar nombres">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Telefono</label>
                                                <input type="text" class="form-control" id="page_phone"  name="page_phone" value="{{ setting('page_phone')  }}" placeholder="Ingresar telefono">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Celular</label>
                                                <input type="text" class="form-control" id="page_cellphone"  name="page_cellphone" value="{{ setting('page_cellphone')  }}" placeholder="Ingresar celular">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Whatsapp</label>
                                                <input type="text" class="form-control" id="page_whatsapp"  name="page_whatsapp" value="{{ setting('page_whatsapp')  }}" placeholder="Ingresar whatsapp">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Facebook</label>
                                                <input type="text" class="form-control" id="social_media_facebook"  name="social_media_facebook" value="{{ setting('social_media_facebook')  }}" placeholder="Ingresar facebook">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Instagram</label>
                                                <input type="text" class="form-control" id="social_media_instagram"  name="social_media_instagram" value="{{ setting('social_media_instagram')  }}" placeholder="Ingresar instragram">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Linkedin</label>
                                                <input type="text" class="form-control" id="social_media_linkedin"  name="social_media_linkedin" value="{{ setting('social_media_linkedin')  }}" placeholder="Ingresar linkedin">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Twitter</label>
                                                <input type="text" class="form-control" id="social_media_twitter"  name="social_media_twitter" value="{{ setting('social_media_twitter')  }}" placeholder="Ingresar twitter">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Youtube</label>
                                                <input type="text" class="form-control" id="social_media_youtube"  name="social_media_youtube" value="{{ setting('social_media_youtube')  }}" placeholder="Ingresar youtube">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Mapa</label>
                                                <input type="text" class="form-control" id="page_map"  name="page_map" value="{{ setting('page_map')  }}" placeholder="Ingresar mapa">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Horario entre semana</label>
                                                <input type="text" class="form-control" id="page_hour_weekend" name="page_hour_weekend"
                                                    value="{{ setting('page_hour_weekend')  }}" placeholder="Ingresar youtube">
                                        </div>
                                    </div>


                                    <div class="col-6">
                                        <div class="mb-3">
                                                <label  class="control-label col-form-label">Horario fines de semana</label>
                                                <input type="text" class="form-control" id="page_hour_weekends" name="page_hour_weekends"
                                                    value="{{ setting('page_hour_weekends')  }}" placeholder="Ingresar youtube">
                                        </div>
                                    </div>



                                    <div class="col-12 mt-3">
                                        <label class="control-label col-form-label">Politicas de privacidad</label>
                                        <div class="">
                                            <div class="quill-wrapper">
                                                <div  id="politics">{!! clean(setting('page_politic'), 'content') !!}</div>
                                            </div>
                                            <label id="politic-error" class="error d-none" for="politic"></label>
                                        </div>
                                    </div>

                                     <div class="col-12 mt-3">
                                        <label class="control-label col-form-label">Terminos y condiciones</label>
                                        <div class="">
                                            <div class="quill-wrapper">
                                                <div  id="terms">{!! clean(setting('page_term'), 'content') !!}</div>
                                            </div>
                                            <label id="term-error" class="error d-none" for="term"></label>
                                        </div>
                                    </div>

                                     <div class="col-12 mt-3">
                                        <label class="control-label col-form-label">Descripción</label>
                                        <div class="">
                                            <div class="quill-wrapper">
                                                <div  id="descriptions">{!! clean(setting('page_description'), 'content') !!}</div>
                                            </div>
                                            <label id="description-error" class="error d-none" for="description"></label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Reseñas de estudiantes</label>
                                            <select class="form-select" id="reviews_enabled" name="reviews_enabled">
                                                <option value="1" {{ setting('reviews_enabled') == 1 ? 'selected' : '' }}>Habilitadas — los alumnos pueden calificar</option>
                                                <option value="0" {{ setting('reviews_enabled') == 1 ? '' : 'selected' }}>Deshabilitadas</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Cuando está habilitada, al aprobar el examen de un curso el estudiante podrá calificarlo con estrellas y dejar un comentario que se mostrará en la página del curso.</small>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Notificaciones del formulario de contacto</label>
                                            <select class="form-select" id="contact_notifications" name="contact_notifications">
                                                <option value="1" {{ setting('contact_notifications') == '1' ? 'selected' : '' }}>Habilitadas — enviar email al recibir un mensaje</option>
                                                <option value="0" {{ setting('contact_notifications') != '1' ? 'selected' : '' }}>Deshabilitadas</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Cuando está habilitado, cada mensaje del formulario de contacto dispara un email de alerta al correo configurado en "Correo electrónico".</small>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Registro público de usuarios</label>
                                            <select class="form-select" id="registration_enabled" name="registration_enabled">
                                                <option value="1" {{ settingEnabled('registration_enabled', true) ? 'selected' : '' }}>Habilitado — cualquier visitante puede registrarse</option>
                                                <option value="0" {{ settingEnabled('registration_enabled', true) ? '' : 'selected' }}>Deshabilitado — solo el administrador crea cuentas</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Cuando está deshabilitado, el formulario de registro en la web pública rechaza nuevas inscripciones.</small>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label class="control-label col-form-label">Diseño del aula (portal del estudiante)</label>
                                            <select class="form-select" id="aula_version" name="aula_version">
                                                <option value="1" {{ setting('aula_version') == '2' ? '' : 'selected' }}>Versión 1 — contenido a la izquierda, temario a la derecha</option>
                                                <option value="2" {{ setting('aula_version') == '2' ? 'selected' : '' }}>Versión 2 — temario lateral izquierdo, contenido a la derecha</option>
                                            </select>
                                            <small class="text-muted d-block mt-1">Define cómo ven los alumnos el aula y las clases de cada curso.</small>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="action-form border-top mt-4">
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                                    Guardar
                                                </button>
                                            </div>
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
<script src="{{ asset('managers/js/views/settings/settings/setting.js') }}"></script>
@endpush
