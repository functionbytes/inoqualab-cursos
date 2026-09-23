@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Reasignar usuario — ' . $user->firstname . ' ' . $user->lastname])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReassign" role="form" onSubmit="return false"
                  data-reassign-url="{{ route('distributor.enterprises.users.reassign.single') }}"
                  data-redirect-url="{{ route('distributor.enterprises.users', $enterprise->slack ?? '') }}">
                {{ csrf_field() }}
                <input type="hidden" name="slack" value="{{ $user->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reasignar usuario — {{ $user->firstname }} {{ $user->lastname }}</h6>
                        <p class="text-muted small mb-0">
                            Selecciona la empresa destino. La empresa actual es <strong>{{ $enterprise->title ?? '-' }}</strong>.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Empresa destino</label>
                                {!! Form::select('enterprise', $enterprises, null, ['class' => 'select2 form-control', 'id' => 'enterprise_select']) !!}
                                <label id="enterprise_select-error" class="error d-none" for="enterprise_select"></label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary w-100">Reasignar</button>
                        <a href="{{ route('distributor.enterprises.users.view', $user->slack) }}" class="btn btn-light border w-100">
                            Volver
                        </a>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre esta reasignación</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">El usuario se traslada de la empresa actual a la empresa destino que selecciones.</p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/users/users/reassign.js') }}"></script>
@endpush
