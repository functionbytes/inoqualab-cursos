<!-- Header Start -->

<header class="app-header">
    <nav class="navbar navbar-expand-lg navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link sidebartoggler nav-icon-hover ms-n3" id="headerCollapse" href="javascript:void(0)">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <div class="d-block d-lg-none">
            @if(count($setting->getMedia('logo'))>0)
                <img src="{{ $setting->getfirstMedia('logo')->getfullUrl() }}" width="180" alt="" />
            @endif
        </div>
        <button class="navbar-toggler p-0 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="p-2">
                <i class="fas fa-ellipsis fs-7"></i>
              </span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="d-flex align-items-center justify-content-between">
                <a href="javascript:void(0)" class="nav-link d-flex d-lg-none align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar" aria-controls="offcanvasWithBothOptions">
                    <i class="fas fa-align-justify fs-7"></i>
                </a>
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">

                    @php
                        $unreadCount = Auth::user()->unreadNotifications()->count();
                        $recentNotifs = Auth::user()->unreadNotifications()->latest()->take(5)->get();
                    @endphp

                    {{-- Campana de notificaciones --}}
                    <li class="nav-item dropdown me-1">
                        <a class="nav-link nav-icon-hover position-relative" href="javascript:void(0)"
                           id="dropNotif" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fs-6"></i>
                            @if($unreadCount > 0)
                                <span class="badge rounded-pill bg-danger position-absolute header-notif-badge"
                                      id="notifBadge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end content-dd dropdown-menu-animate-up p-0 header-notif-dropdown"
                             aria-labelledby="dropNotif">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-light rounded-top">
                                <span class="fw-semibold small">Notificaciones</span>
                                @if($unreadCount > 0)
                                    <a href="javascript:void(0)" class="text-primary small" id="markAllReadHeader">
                                        Marcar todas como leídas
                                    </a>
                                @endif
                            </div>
                            <div class="header-notif-list">
                                @forelse($recentNotifs as $notif)
                                    <a href="{{ route('manager.notifications') }}"
                                       class="d-flex align-items-start gap-2 px-3 py-2 border-bottom text-dark text-decoration-none hover-bg-light notif-item"
                                       data-notif-id="{{ $notif->id }}">
                                        <div class="flex-shrink-0 mt-1">
                                            <span class="rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle header-notif-icon-circle">
                                                <i class="fas fa-bell fs-6 text-primary"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold small text-truncate">{{ $notif->data['title'] ?? 'Notificación' }}</div>
                                            <div class="text-muted small text-truncate">{{ $notif->data['message'] ?? '' }}</div>
                                            <div class="text-muted header-notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill flex-shrink-0 align-self-start mt-1 header-notif-dot"></span>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted small">
                                        <i class="fas fa-bell-slash fs-4 d-block mb-2"></i>
                                        Sin notificaciones nuevas
                                    </div>
                                @endforelse
                            </div>
                            <div class="px-3 py-2 border-top text-center">
                                <a href="{{ route('manager.notifications') }}" class="text-primary small">
                                    Ver todas las notificaciones
                                </a>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="user-profile-img">
                                    <img src="{{ Auth::user()->image ? url(Auth::user()->image) : url('managers/images/profile/profile.jpg') }}" class="rounded-circle object-fit-cover" width="35" height="35" alt="Foto de perfil" data-fallback-src="{{ url('managers/images/profile/profile.jpg') }}" />
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                            <div class="profile-dropdown position-relative" data-simplebar>

                                <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                                    <img src="{{ Auth::user()->image ? url(Auth::user()->image) : url('managers/images/profile/profile.jpg') }}" class="rounded-circle object-fit-cover" width="50" height="50" alt="Foto de perfil" data-fallback-src="{{ url('managers/images/profile/profile.jpg') }}" />
                                    <div class="ms-3">
                                        <h5 class="mb-1 fs-3 text-uppercase">{{ Str::words(Auth::user()->firstname ,1,'') }} {{ Str::words(Auth::user()->lastname,1,'') }} </h5>
                                        <span class="mb-1 d-block text-dark">
                                            @if (Auth::user()->role == 'manager')
                                                Administrador
                                            @elseif (Auth::user()->role == 'customer')
                                                Cliente
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="d-grid py-4 px-7 pt-8">
                                    <a href="{{ route('manager.profile.edit') }}" class="btn btn-outline-primary px-4 w-100 mb-2">Mi perfil</a>
                                    <form action="{{ route('logout') }}" method="POST" class="w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-info px-4 waves-effect waves-light w-100">Salir</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Header End -->

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/header.css') }}">
@endpush

