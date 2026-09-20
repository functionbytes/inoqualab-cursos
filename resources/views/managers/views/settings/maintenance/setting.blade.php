@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formMaintenance" enctype="multipart/form-data" role="form"
                      data-urls='@php $__jsonInline1 = [
                          "update" => route("manager.settings.maintenance.update"),
                          "secret" => route("manager.settings.maintenance.secret"),
                          "dashboard" => route("manager.dashboard"),
                      ]; @endphp@json($__jsonInline1)'>

                    {{ csrf_field() }}

                    <div class="card-body border-top">

                        <div class="row mt-50">

                            <div class="col-12 ">

                                    <div class="row align-items-center">
                                        <div class=" col-sm-11 ">
                                            <h5 class="mb-3">Habilitar modo mantenimiento</h5>
                                            <p class="card-subtitle mb-3 mt-0">(Si "habilita" esta configuración, los clientes solo podran ver la vista de mantenimiento hasta q no sea de nuevo desabiltiado).</p>
                                        </div>
                                        <div class="col-sm-1 justify-content-end d-flex align-items">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode"   @if(setting('maintenance_mode')=='true' ) checked @endif/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top mt-4 pt-4 row align-items-center maintenance_mode @if(setting('maintenance_mode')=='false' ) d-none @endif" >
                                        <div class="mb-3">
                                        <label class="form-label">Llave secreta</label>
                                        <div class="input-group">
                                            <input type="password" id="maintenance_mode_value" name="maintenance_mode_value" value="" class="form-control" readonly placeholder="•••••••• (guardada — usa 'Revelar' para verla)">
                                            <button type="button" id="btnToggleSecret" class="btn btn-outline-secondary">
                                                <i id="eyeIconSecret" class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" id="btnCopySecret" class="btn btn-outline-secondary">
                                                Copiar
                                            </button>
                                        </div>
                                        <div class="alert alert-light-warning note mt-4 mb-0">
                                            <p class="mb-0">
                                                <b class="pb-1 d-flex"> ¿Cómo utilizar la clave secreta? </b>
                                            <ol>
                                                <li>
                                                    La clave secreta se utiliza básicamente para acceder a su URL web. cuando esta en <b>en modo de mantenimiento.</b>
                                                </li>
                                                <li>Usa <b>Revelar</b> o <b>Copiar</b> para obtener tu llave secreta y péguela al final de su URL para acceder a su sitio en modo de mantenimiento
                                                    <b>Ej: {{ getUrl() }}/&lt;llave-secreta&gt;</b>
                                                </li>
                                                <li>Y también puede permitir que otras redes o IP accedan a su sitio web al <b>intercambio</b> Tu clave secreta con ellos.</li>
                                            </ol>
                                            </p>
                                        </div>
                                        </div>
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
<script src="{{ asset('managers/js/views/settings/maintenance/setting.js') }}"></script>
@endpush
