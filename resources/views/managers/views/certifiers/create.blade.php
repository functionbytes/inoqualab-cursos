@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formCertifier" enctype="multipart/form-data" role="form"
                      data-store-url="{{ route('manager.certifiers.store') }}"
                      data-thumbnail-url="{{ route('manager.certifiers.thumbnails') }}"
                      data-signature-url="{{ route('manager.certifiers.signatures') }}"
                      data-thumbnail-delete-url="{{ route('manager.certifiers.thumbnails.delete', ':id') }}"
                      data-signature-delete-url="{{ route('manager.certifiers.signatures.delete', ':id') }}"
                      data-redirect-url="{{ route('manager.certifiers') }}">

                    {{ csrf_field() }}

                    <input type="hidden" id="description" name="description" value="">
                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <input type="hidden" id="statuSignatures" name="statuSignatures" value="false">
                    <input type="hidden" id="statuThumbnails" name="statuThumbnails" value="false">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="thumbnail" name="thumbnail">
                    <input type="hidden" id="signature" name="signature">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Foto</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="thumbnail">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Firma</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la firma de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="signature">
                            <div class="fallback">
                                <input type="file" hidden name="signature">
                            </div>
                        </div>
                        <label id="signature-error" class="error d-none" for="signature"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear capacitador</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos del capacitador. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Nombres</label>
                                        <input type="text" class="form-control" id="firstname"  name="firstname" value="" placeholder="Ingresar nombres">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="lastname"  name="lastname" value="" placeholder="Ingresar apellido">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Identificación</label>
                                        <input type="text" class="form-control" id="identification"  name="identification" value="" placeholder="Ingresar identificación">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Profesión</label>
                                        <input type="text" class="form-control" id="profession"  name="profession" value="" placeholder="Ingresar profesión">
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Descripción</label>
                                    <div class="quill-wrapper">
                                        <div id="descriptions"></div>
                                    </div>
                                    <label id="description-error" class="error d-none" for="description"></label>
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
<script src="{{ asset('managers/js/views/certifiers/create.js') }}"></script>
<script src="{{ asset('managers/js/views/certifiers/quill-description.js') }}"></script>
@endpush



