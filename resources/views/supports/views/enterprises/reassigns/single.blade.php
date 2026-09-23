@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Reasignar a otra empresa'])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formCourses" enctype="multipart/form-data" role="form"
                  data-reassign-url="{{ route('support.enterprises.users.reassign.single') }}"
                  data-redirect-url="{{ route('support.enterprises.users', ':slack') }}">

                {{ csrf_field() }}

                <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reasignar a otra empresa</h6>
                        <p class="text-muted small mb-0">
                            Traslada a este usuario de su empresa actual a otra empresa registrada.
                            Selecciona la empresa destino y confirma para completar el traslado.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresa</label>
                                <input type="text" class="form-control" value="{{ $enterprise->title }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Usuario</label>
                                <input type="text" class="form-control" id="slack" name="slack" value="{{ $user->firstname }} {{ $user->lastname }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresa a reasignar</label>
                                <div class="input-group">
                                    {!! Form::select('enterprise', $enterprises, null , ['class' => 'select2 form-control'  ,'name' => 'enterprise', 'id' => 'enterprise' ]) !!}
                                </div>
                                <label id="enterprise-error" class="error d-none" for="enterprise"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre el traslado</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        El usuario dejará de pertenecer a la empresa actual una vez confirmes el traslado
                        a la nueva empresa.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/reassigns/single.js') }}"></script>
@endpush
