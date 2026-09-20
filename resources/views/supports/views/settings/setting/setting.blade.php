





@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formNotifications" enctype="multipart/form-data" role="form"
              data-update-url="{{ route('support.settings.notifications.update') }}"
              data-redirect-url="{{ route('support.dashboard') }}">

          <input type="hidden" name="slack"  id="slack" value="{{ $user->slack }}">
          {{ csrf_field() }}

          <div class="card-body border-top mb-20">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Configuración de notificaciones</h5>
            </div>

            <div class="row mt-20">

              <div class="col-12 ">
                <div class="mb-4 mt-3">
                  <div class="row align-items-center">
                    <div class=" col-sm-11 ">
                      <label  class="control-label col-form-label ">Notificaciones de email general</label>
                      <p class="card-subtitle mb-3 mt-0">(Si desactiva esta configuración no te llegan notificaciones sobre inscripciones que se le hagan a empresas.</p>
                    </div>
                    <div class="col-sm-1 justify-content-end d-flex align-items">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="mail_notification" id="mail_notification"   @if($user->email_notification==1 ) checked @endif/>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 border-top">
                <div class="mb-4 mt-3">
                  <div class="row align-items-center">
                    <div class=" col-sm-11 ">
                      <label  class="control-label col-form-label ">Notificaciones de inscripción</label>
                      <p class="card-subtitle mb-3 mt-0">(Si desactiva esta configuración no te llegan notificaciones sobre inscripciones que se le hagan a empresas.</p>
                    </div>
                    <div class="col-sm-1 justify-content-end d-flex align-items">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="inscription_notification" id="inscription_notification"   @if($user->status_notification==1 ) checked @endif/>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 border-top">
                <div class="mb-4 mt-3">
                  <div class="row align-items-center">
                    <div class=" col-sm-11 ">
                      <label  class="control-label col-form-label ">Notificaciones de facturación</label>
                      <p class="card-subtitle mb-3 mt-0">(Si desactiva esta configuración no te llegan notificaciones sobre inscripciones que se le hagan a empresas.</p>
                    </div>
                    <div class="col-sm-1 justify-content-end d-flex align-items">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="invoice_notification" id="invoice_notification"   @if($user->order_notification==1 ) checked @endif/>
                      </div>
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
<script src="{{ asset('supports/js/views/settings/notifications.js') }}"></script>
@endpush







