@extends('layouts.customers')

@section('content')

    @include('customers.includes.card', ['title' => 'Detalle notificación'])

    <div class="row g-3">

        <div class="col-12 col-lg-8">
            <div class="card card-body">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Información de la notificación</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <p class="mb-1 fs-2 text-muted">Título</p>
                        <h6 class="fw-semibold mb-0">{{ $notification->data['title'] ?? 'Notificación' }}</h6>
                    </div>
                    <div class="col-12">
                        <p class="mb-1 fs-2 text-muted">Mensaje</p>
                        <p class="mb-0">{{ $notification->data['message'] ?? '' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 fs-2 text-muted">Fecha</p>
                        <h6 class="fw-semibold mb-0">{{ $notification->created_at->format('d/m/Y H:i') }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 fs-2 text-muted">Estado</p>
                        @if(is_null($notification->read_at))
                            <span class="badge bg-primary rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">No leída</span>
                        @else
                            <span class="badge bg-light-success rounded-3 py-2 text-success fw-semibold fs-2 d-inline-flex align-items-center gap-1">Leída</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card card-body">
                <a href="{{ route('customers.notifications') }}" class="btn btn-light w-100">
                    @include('customers.includes.icon', ['name' => 'arrow-left'])Volver
                </a>
            </div>
        </div>

    </div>

@endsection
