@extends('layouts.managers')

@section('title', 'Notificaciones')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Notificaciones</h5>
                        <p class="mb-0 text-muted">Historial de notificaciones recibidas</p>
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="select-all">
                            <label class="form-check-label text-muted" for="select-all">Seleccionar todo</label>
                        </div>
                        <button type="button" class="btn btn-primary" id="btn-mark-all"
                                data-mark-all-url="{{ route('manager.notifications.markasread') }}"
                                data-bulk-url="{{ route('manager.notifications.bulk-action') }}">
                            Marcar todas como leidas
                        </button>
                    </div>
                </div>
            </div>

            {{-- Lista de notificaciones --}}
            <div class="card-body">
                @forelse($notifications as $date => $group)
                    <h6 class="fw-semibold text-muted mb-3 border-bottom pb-2">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('d \d\e F, Y') }}
                    </h6>
                    <ul class="list-unstyled mb-4">
                        @foreach($group as $notification)
                            <li class="d-flex align-items-start gap-3 py-2 border-bottom">
                                <div class="flex-shrink-0 mt-1">
                                    <input type="checkbox" class="form-check-input bulk-checkbox"
                                           value="{{ $notification->id }}">
                                </div>
                                <div class="flex-shrink-0 mt-1">
                                    <span class="notification-icon rounded-circle d-flex align-items-center justify-content-center bg-light-primary">
                                        <i class="fas fa-bell text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <span class="fw-semibold">{{ $notification->data['title'] ?? 'Notificacion' }}</span>
                                        @if(is_null($notification->read_at))
                                            <span class="badge bg-primary rounded-pill">Nueva</span>
                                        @endif
                                    </div>
                                    <p class="mb-1 text-muted">{{ $notification->data['message'] ?? '' }}</p>
                                    <span class="text-muted">{{ $notification->created_at->format('H:i') }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay notificaciones</h5>
                        <p class="text-muted mb-0">Las notificaciones aparecerán aquí cuando se generen.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'notificación(es)',
        'bulkActions' => [
            ['value' => 'read', 'label' => 'Marcar como leídas'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/notifications/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/notifications/index.js') }}"></script>
@endpush
