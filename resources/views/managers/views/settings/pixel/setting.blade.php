@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formPixel" enctype="multipart/form-data" role="form"
                      data-urls='@json([
                          "update" => route("manager.settings.pixel.update"),
                          "dashboard" => route("manager.dashboard"),
                      ])'>

                    {{ csrf_field() }}

                    <div class="card-body border-top">

                        <div class="row mt-50">

                            <div class="col-12 ">
                                <div class="mb-4 mt-3">
                                    <div class="mb-4 row align-items-center">
                                        <div class=" col-sm-11 ">
                                            <h5 class="mb-3">Habilitar facebook</h5>
                                            <p class="card-subtitle mb-3 mt-0">(Si "habilita" esta configuración, los clientes solo podrán ver el nombre que proporcione en el campo de entrada a continuación. No podrán ver el nombre ni la función de los empleados).</p>
                                        </div>
                                        <div class="col-sm-1 justify-content-end d-flex align-items">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="fb_pixel_enable" id="fb_pixel_enable"   @if(setting('fb_pixel_enable')=='true' ) checked @endif/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4 row align-items-center">
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" id="fb_pixel"  name="fb_pixel" value="{{ setting('fb_pixel') }}">
                                        </div>
                                        <label for="userreopentime" class="form-label fw-semibold col-sm-9 col-form-label">ID de rastreo</label>
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
<script src="{{ asset('managers/js/views/settings/pixel/setting.js') }}"></script>
@endpush
