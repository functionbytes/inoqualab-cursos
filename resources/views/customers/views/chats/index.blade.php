@extends('layouts.customers')

@section('content')

    @include('customers.includes.card', ['title' => 'Notificaciones'])

    <div class="widget-content searchable-container list">

        @forelse($notifications as $date => $group)
            <div class="card card-body mb-2">
                <h6 class="fw-semibold text-muted mb-3 border-bottom pb-2">{{ \Carbon\Carbon::parse($date)->translatedFormat('d \d\e F, Y') }}</h6>
                <ul class="list-unstyled mb-0">
                    @foreach($group as $notification)
                        <li class="d-flex align-items-start gap-3 py-2 border-bottom notification-item" data-id="{{ $notification->id }}">
                            <div class="flex-shrink-0 mt-1">
                                <span class="rounded-circle d-flex align-items-center justify-content-center bg-light-primary" style="width:36px;height:36px">
                                    <i class="fas fa-bell text-primary"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">{{ $notification->data['title'] ?? 'Notificación' }}</h6>
                                        <p class="mb-1 text-muted">{{ $notification->data['message'] ?? '' }}</p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(is_null($notification->read_at))
                                            <span class="badge bg-primary rounded-pill unread-badge">Nueva</span>
                                            <button class="btn btn-sm btn-outline-primary btn-mark-read" data-id="{{ $notification->id }}">
                                                Marcar como leída
                                            </button>
                                        @endif
                                    </div>
                                </div>
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

@push('scripts')
<script>
$(document).ready(function () {
    $(document).on('click', '.btn-mark-read', function () {
        var btn = $(this);
        var id = btn.data('id');
        var item = btn.closest('.notification-item');

        $.ajax({
            url: '{{ route('customers.notifications.mark') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { id: id },
            success: function () {
                toastr.success('Notificación marcada como leída');
                item.find('.unread-badge').remove();
                btn.remove();
            },
            error: function () {
                toastr.error('Error al marcar la notificación');
            }
        });
    });
});
</script>
@endpush
