@if(auth()->user())
@php
    // Si el controller pasa $notifications (búsqueda), respetarlo; si no, las del usuario.
    $notifyItems = $notifications ?? auth()->user()->notifications;
    $notifyTz = auth()->user()->timezone ?? config('app.timezone');
@endphp
<li class="nav-item dropdown">
    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
       aria-expanded="false">
        <i class="fas fa-bell"></i>
        <div class="notification bg-primary rounded-circle"></div>
    </a>
    <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
        <div class="d-flex align-items-center justify-content-between py-3 px-7">
            <h5 class="mb-0 fs-5 fw-semibold">Notificaciones</h5>
            <span class="badge bg-primary rounded-4 px-3 py-1 lh-sm">{{ count($notifyItems) }}</span>
        </div>
        <div class="message-body" data-simplebar>
            @forelse( collect($notifyItems)->groupBy(fn ($n) => $n->created_at->format('Y-m-d')) as $created_at => $group)

            @php
            $today = \Carbon\Carbon::parse(now());
            $yesterday = \Carbon\Carbon::yesterday();
            $createdat = \Carbon\Carbon::parse($created_at);
            @endphp

            @if($createdat->format('Y-m-d') == $today->format('Y-m-d'))
            <div class="badge badge-light-1 p-2 px-3 fs-16 ms-0 mt-0 mb-3">
                Hoy
            </div>
            @elseif($createdat->format('Y-m-d') == $yesterday->format('Y-m-d'))
            <div class="badge badge-light-1 p-2 px-3 fs-16 ms-0 mt-3 mb-3">
                Ayer
            </div>
            @else
            <div class="badge badge-light-1 p-2 px-3 fs-16 ms-0 mt-3 mb-3">
                {{$createdat->format(setting('date_format'))}}
            </div>
            @endif

            @foreach($group as $notification)

            @if(($notification->data['status'] ?? null) != 'mail')
            @if($notification->read_at != null)
            <a  class="py-6 px-7 d-flex align-items-center dropdown-item notify-read"   href="{{ $notification->data['link'] ?? route('customers.notifications') }}" data-id="{{$notification->id}}">
                                                            <span class="me-3">
                                                                <img src="/managers/images/profile/profile.jpg" alt="user" class="rounded-circle" width="48" height="48" />
                                                            </span>
                <div class="w-75 d-inline-block v-middle">
                    <h6 class="mb-1 fw-semibold">{{Str::limit($notification->data['title'] ?? 'Notificación', '50', '...')}}</h6>
                    <span class="d-block">{{$notification->created_at->timezone($notifyTz)->format(setting('time_format'))}}</span>
                </div>
            </a>
            @else
            <a class="py-6 px-7 d-flex align-items-center dropdown-item" href="{{ $notification->data['link'] ?? route('customers.notifications') }}"
               data-id="{{$notification->id}}">
                                                            <span class="me-3">
                                                                <img src="/managers/images/profile/profile.jpg" alt="user" class="rounded-circle" width="48" height="48" />
                                                            </span>
                <div class="w-75 d-inline-block v-middle">
                    <h6 class="mb-1 fw-semibold">{{Str::limit($notification->data['title'] ?? 'Notificación', '50', '...')}}</h6>
                    <span
                            class="d-block">{{$notification->created_at->timezone($notifyTz)->format(setting('time_format'))}}</span>
                </div>
            </a>
            @endif
            @endif

            @endforeach

            @empty
            <div class="card">
                <div class="card-body h-100 w-100">
                    <div class="main-content text-center">
                        <div class="notification-icon-container p-4">
                            <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24">
                                <path
                                        d="M21.9,21.1l-19-19C2.7,2,2.4,2,2.2,2.1C2,2.3,2,2.7,2.1,2.9l4.5,4.5C6.2,8.2,6,9.1,6,10v4.1c-1.1,0.2-2,1.2-2,2.4v2C4,18.8,4.2,19,4.5,19h3.7c0.5,1.7,2,3,3.8,3c1.8,0,3.4-1.3,3.8-3h2.5l2.9,2.9c0.1,0.1,0.2,0.1,0.4,0.1c0.1,0,0.3-0.1,0.4-0.1C22,21.7,22,21.3,21.9,21.1z M7,10c0-0.7,0.1-1.3,0.4-1.9l5.9,5.9H7V10z M13,20.8c-1.6,0.5-3.3-0.3-3.8-1.8h5.6C14.5,19.9,13.8,20.5,13,20.8z M5,18v-1.5C5,15.7,5.7,15,6.5,15h7.8l3,3H5z M9.6,5.6c1.9-1,4.3-0.7,5.9,0.9C16.5,7.4,17,8.7,17,10v3.3c0,0.3,0.2,0.5,0.5,0.5h0c0.3,0,0.5-0.2,0.5-0.5V10c0-3.1-2.4-5.7-5.5-6V2.5C12.5,2.2,12.3,2,12,2s-0.5,0.2-0.5,0.5V4c-0.8,0.1-1.6,0.3-2.3,0.7c0,0,0,0,0,0C8.9,4.8,8.8,5.1,9,5.4C9.1,5.6,9.4,5.7,9.6,5.6z" />
                            </svg>
                        </div>
                        <h4 class="mb-1">No hay nuevas notificaciones para mostrar</h4>
                        <p class="text-muted">No hay notificaciones. Te avisaremos cuando llegue la nueva notificación.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
        <div class="py-6 px-7 mb-1">
            <a href="{{ route('customers.notifications') }}" class="btn btn-outline-primary w-100"> Ver todas las notificaciones </a>
        </div>
    </div>
</li>
@endif

