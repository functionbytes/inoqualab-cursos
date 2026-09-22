@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Pixel Analytics'])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formPixel" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "update" => route("manager.settings.pixel.update"),
                      "dashboard" => route("manager.dashboard"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Facebook Pixel</h6>
                        <p class="text-muted mb-3">Habilita el seguimiento de eventos de Facebook Pixel en el sitio público e ingresa el ID de rastreo.</p>

                        <div class="row g-3 align-items-center">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="fb_pixel_enable" id="fb_pixel_enable" @if(setting('fb_pixel_enable') == 'true') checked @endif>
                                    <label class="form-check-label fw-semibold" for="fb_pixel_enable">Habilitar Facebook Pixel</label>
                                </div>
                                <small class="text-muted d-block mt-1">Si se habilita, el pixel se carga en todas las páginas públicas del sitio.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="fb_pixel" class="form-label fw-semibold">ID de rastreo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fb_pixel" name="fb_pixel" value="{{ setting('fb_pixel') }}">
                                <small class="text-muted d-block mt-1">ID numérico del Pixel de Facebook Ads Manager</small>
                                <label class="error d-none errors"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre Facebook Pixel</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">El Pixel de Facebook permite medir conversiones, optimizar anuncios y construir audiencias a partir de la actividad de los visitantes del sitio.</p>

                    <hr class="my-3">

                    <h6 class="fw-semibold mb-2">Cómo obtener el ID</h6>
                    <p class="text-muted mb-0">Se encuentra en Meta Events Manager, dentro de la configuración del origen de datos del Pixel.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/pixel/setting.js') }}"></script>
@endpush
