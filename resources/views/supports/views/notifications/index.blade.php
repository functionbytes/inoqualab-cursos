@extends('layouts.managers')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Notificaciones</h5>
                        <p class="mb-0 text-muted">Avisos y eventos recientes de tu cuenta</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('support.notifications.markasread') }}" class="btn btn-primary" id="btn-mark-all">
                            Marcar todas como leídas
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Notificaciones</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">No leídas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['unread']) }}</h4>
                                <span class="text-muted">Pendientes de revisar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Listado --}}
            <div class="card-body">
                @forelse($notifications as $date => $group)
                    <div class="mb-4">
                        <h6 class="fw-semibold text-muted mb-3 border-bottom pb-2">{{ \Carbon\Carbon::parse($date)->translatedFormat('d \d\e F, Y') }}</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($group as $notification)
                                <li class="d-flex align-items-start gap-3 py-2 border-bottom">
                                    <div class="flex-shrink-0 mt-1">
                                        <span class="rounded-circle d-flex align-items-center justify-content-center bg-light-primary notification-icon">
                                            <i class="fas fa-bell text-primary"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="mb-1 fw-semibold">{{ $notification->data['title'] ?? 'Notificación' }}</h6>
                                            @if(is_null($notification->read_at))
                                                <span class="badge bg-primary-subtle text-primary">Nueva</span>
                                            @endif
                                        </div>
                                        <p class="mb-1 text-muted">{{ $notification->data['message'] ?? '' }}</p>
                                        <small class="text-muted">{{ $notification->created_at->format('H:i') }}</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay notificaciones</h5>
                        <p class="text-muted mb-0">Aún no tienes notificaciones registradas.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/notifications/index.css') }}">
@endpush
