





@extends('layouts.managers')

@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formNotifications" enctype="multipart/form-data" role="form"
            data-update-url="{{ route('support.settings.notifications.update') }}"
            data-redirect-url="{{ route('support.dashboard') }}">

        <input type="hidden" name="slack" id="slack" value="{{ $user->slack }}">
        {{ csrf_field() }}

        <div class="card">

          <div class="card-header border-bottom">
            <h6 class="mb-0 fw-bold">Configuración de notificaciones</h6>
          </div>

          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-sm-11">
                <label class="form-label fw-semibold">Notificaciones de email general</label>
                <p class="text-muted small mb-0">Si desactiva esta configuración no te llegarán notificaciones generales por correo electrónico.</p>
              </div>
              <div class="col-sm-1 justify-content-end d-flex align-items">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="mail_notification" id="mail_notification" @if($user->email_notification==1) checked @endif>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-0">

          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-sm-11">
                <label class="form-label fw-semibold">Notificaciones de inscripción</label>
                <p class="text-muted small mb-0">Si desactiva esta configuración no te llegan notificaciones sobre inscripciones que se le hagan a empresas.</p>
              </div>
              <div class="col-sm-1 justify-content-end d-flex align-items">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="inscription_notification" id="inscription_notification" @if($user->status_notification==1) checked @endif>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-0">

          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-sm-11">
                <label class="form-label fw-semibold">Notificaciones de facturación</label>
                <p class="text-muted small mb-0">Si desactiva esta configuración no te llegarán notificaciones sobre órdenes y facturación.</p>
              </div>
              <div class="col-sm-1 justify-content-end d-flex align-items">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="invoice_notification" id="invoice_notification" @if($user->order_notification==1) checked @endif>
                </div>
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
          <h6 class="mb-0 fw-bold">Sobre estas notificaciones</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">Estas preferencias solo aplican a tu propia cuenta de soporte, no afectan las notificaciones de otros agentes.</p>
        </div>
      </div>

    </div>

  </div>

@endsection

@push('scripts')
<script src="{{ asset('supports/js/views/settings/notifications.js') }}"></script>
@endpush







