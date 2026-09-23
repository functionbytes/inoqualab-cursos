@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Habilitar modo mantenimiento'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formMaintenance" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "update" => route("manager.settings.maintenance.update"),
                      "secret" => route("manager.settings.maintenance.secret"),
                      "dashboard" => route("manager.dashboard"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Modo mantenimiento</h6>
                        <p class="text-muted small mb-0">
                            Si se habilita, los clientes solo podrán ver la vista de mantenimiento hasta que se deshabilite de nuevo.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-sm-11">
                                <label class="form-label fw-semibold mb-0" for="maintenance_mode">Habilitar modo mantenimiento</label>
                            </div>
                            <div class="col-sm-1 justify-content-end d-flex">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode"   @if(setting('maintenance_mode')=='true' ) checked @endif/>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center maintenance_mode mt-3 @if(setting('maintenance_mode')=='false' ) d-none @endif" >
                            <div class="col-12">
                                <label class="form-label fw-semibold">Llave secreta</label>
                                <div class="input-group">
                                    <input type="password" id="maintenance_mode_value" name="maintenance_mode_value" value="" class="form-control" readonly placeholder="•••••••• (guardada — usa 'Revelar' para verla)">
                                    <button type="button" id="btnToggleSecret" class="btn btn-outline-secondary">
                                        <i id="eyeIconSecret" class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" id="btnCopySecret" class="btn btn-outline-secondary">
                                        Copiar
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">Se agrega al final de la URL del sitio para acceder mientras está en mantenimiento.</small>
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
                    <h6 class="mb-0 fw-bold">¿Cómo utilizar la clave secreta?</h6>
                </div>
                <div class="card-body">
                    <ol class="text-muted ps-3 mb-0">
                        <li class="mb-2">Se usa para acceder al sitio mientras está en modo de mantenimiento.</li>
                        <li class="mb-2">Usa <strong>Revelar</strong> o <strong>Copiar</strong> para obtenerla y agrégala al final de la URL del sitio: <br><code>{{ getUrl() }}/&lt;llave-secreta&gt;</code></li>
                        <li>También puedes compartir la clave con otras personas o equipos para que accedan al sitio durante el mantenimiento.</li>
                    </ol>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/maintenance/setting.js') }}"></script>
@endpush
