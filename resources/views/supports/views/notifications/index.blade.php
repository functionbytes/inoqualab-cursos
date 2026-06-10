@extends('layouts.supports')

@section('content')

    @include('supports.includes.card', ['title' => 'Notificaciones'])

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0">Notificaciones</h6>
                <a href="{{ route('support.notifications.markasread') }}" class="btn btn-primary btn-sm" id="btn-mark-all">
                    Marcar todas como leídas
                </a>
            </div>
        </div>

        @forelse($notifications as $date => $group)
            <div class="card card-body mb-2">
                <h6 class="fw-semibold text-muted mb-3 border-bottom pb-2">{{ \Carbon\Carbon::parse($date)->translatedFormat('d \d\e F, Y') }}</h6>
                <ul class="list-unstyled mb-0">
                    @foreach($group as $notification)
                        <li class="d-flex align-items-start gap-3 py-2 border-bottom">
                            <div class="flex-shrink-0 mt-1">
                                <span class="rounded-circle d-flex align-items-center justify-content-center bg-light-primary" style="width:36px;height:36px">
                                    <i class="fas fa-bell text-primary"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-1 fw-semibold">{{ $notification->data['title'] ?? 'Notificación' }}</h6>
                                    @if(is_null($notification->read_at))
                                        <span class="badge bg-primary rounded-pill">Nueva</span>
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
            <div class="card card-body text-center py-5">
                <i class="fas fa-bell-slash fs-1 text-muted mb-3"></i>
                <p class="text-muted mb-0">No hay notificaciones</p>
            </div>
        @endforelse

    </div>

@endsection
