@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Configuración de tickets'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">
      <form id="formDistributors" enctype="multipart/form-data" role="form" onSubmit="return false"
            data-update-url="{{ route('distributor.settings.notifications.update') }}"
            data-redirect-url="{{ route('distributor.dashboard') }}">

        <input type="hidden" name="slack" id="slack" value="{{ $distributor->slack }}">
        {{ csrf_field() }}

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Configuración de tickets</h6>
              <p class="text-muted small mb-0">
                  Activa o desactiva los correos de notificación que recibes como distribuidor.
              </p>
          </div>

          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-sm-11">
                <label class="form-label fw-semibold mb-0">Notificaciones de inscripciones</label>
                <p class="text-muted mb-0 small">(Si desactiva esta configuración no te llegan notificaciones sobre inscripciones que se le hagan a empresas.</p>
              </div>
              <div class="col-sm-1 d-flex justify-content-end">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="inscription_notification" id="inscription_notification" @if($distributor->inscription_notification==1) checked @endif>
                </div>
              </div>
            </div>

            <hr class="my-3">

            <div class="row align-items-center">
              <div class="col-sm-11">
                <label class="form-label fw-semibold mb-0">Notificaciones de email general</label>
                <p class="text-muted mb-0 small">(Si desactiva esta configuración no te llegan notificaciones sobre inscripciones que se le hagan a empresas.</p>
              </div>
              <div class="col-sm-1 d-flex justify-content-end">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="mail_notification" id="mail_notification" @if($distributor->mail_notification==1) checked @endif>
                </div>
              </div>
            </div>

            <hr class="my-3">

            <div class="row align-items-center">
              <div class="col-sm-11">
                <label class="form-label fw-semibold mb-0">Notificaciones de facturación</label>
                <p class="text-muted mb-0 small">(Si desactiva esta configuración no te llegan notificaciones sobre inscripciones que se le hagan a empresas.</p>
              </div>
              <div class="col-sm-1 d-flex justify-content-end">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="invoice_notification" id="invoice_notification" @if($distributor->invoice_notification==1) checked @endif>
                </div>
              </div>
            </div>

            <div class="errors d-none"></div>
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
          <h6 class="mb-0 fw-bold">Sobre las notificaciones</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">
            Estas notificaciones llegan al correo registrado en tu cuenta de distribuidor.
            Puedes activarlas o desactivarlas de forma independiente.
          </p>
        </div>
      </div>
    </div>

  </div>

@endsection

@push('scripts')
    <script src="{{ asset('distributors/js/settings/setting/setting.js') }}"></script>
@endpush
