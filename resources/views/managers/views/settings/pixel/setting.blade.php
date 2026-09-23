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

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Estado del servicio</h6>
                        <p class="text-muted small mb-0">
                            Habilita o deshabilita el Meta Pixel en el sitio.</p>
                    </div>

                    <div class="card-body">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="metaPixelEnable"
                                   name="meta_pixel_enable" value="1"
                                   {{ $metaPixelEnable ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="metaPixelEnable">
                                Habilitar Meta Pixel
                            </label>
                        </div>
                        <small class="text-muted d-block">El pixel se carga en todas las páginas públicas del sitio.</small>
                    </div>

                    <div id="metaPixelFields" class="{{ $metaPixelEnable ? '' : 'd-none' }}">

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Meta Pixel (Facebook / Instagram)</h6>
                        <p class="text-muted mb-3">ID de rastreo del Pixel de Meta Ads Manager.
                        </p>

                        <div class="row g-3 align-items-center">
                            <div class="col-12">
                                <label for="meta_pixel_id" class="form-label fw-semibold">ID de rastreo</label>
                                <input type="text" class="form-control" id="meta_pixel_id" name="meta_pixel_id" value="{{ $metaPixelId }}">
                                <small class="text-muted d-block mt-1">ID numérico del Pixel de Meta Ads Manager</small>
                                <label class="error d-none errors"></label>
                            </div>
                        </div>
                    </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar configuración de Meta Pixel
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre Meta Pixel</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">El Meta Pixel permite medir conversiones, optimizar anuncios y construir audiencias a partir de la actividad de los visitantes del sitio, en Facebook e Instagram.</p>

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
