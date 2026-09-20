@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formReassign" role="form" onSubmit="return false"
                      data-reassign-url="{{ route('distributor.enterprises.users.reassign.single') }}"
                      data-redirect-url="{{ route('distributor.enterprises.users', $enterprise->slack ?? '') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="slack" value="{{ $user->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center mb-3">
                            <h5 class="mb-0">Reasignar usuario — {{ $user->firstname }} {{ $user->lastname }}</h5>
                            <div class="ms-auto">
                                <a href="{{ route('distributor.enterprises.users.view', $user->slack) }}" class="btn btn-light btn-sm">
                                    Volver
                                </a>
                            </div>
                        </div>
                        <p class="card-subtitle mb-4">
                            Selecciona la empresa destino. La empresa actual es <strong>{{ $enterprise->title ?? '-' }}</strong>.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Empresa destino</label>
                                    {!! Form::select('enterprise', $enterprises, null, ['class' => 'select2 form-control', 'id' => 'enterprise_select']) !!}
                                    <label id="enterprise_select-error" class="error d-none" for="enterprise_select"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="action-form border-top mt-4">
                            <div class="text-center p-3">
                                <button type="submit" class="btn btn-primary px-4 w-100">Reasignar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/users/users/reassign.js') }}"></script>
@endpush
