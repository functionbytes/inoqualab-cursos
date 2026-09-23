{{--
    Header portado de modules/Theme/resources/views/theme/includes/header.blade.php
    (webadmin), estilizado con managers/css/nav.css + managers/css/includes/header.css
    (compartidos por los 5 portales admin). Mismo patrón que
    managers/includes/header.blade.php, con las rutas propias de support.
--}}
<header class="app-header">
    <div class="app-header-inner">
        <button class="app-toggler" type="button"
                aria-controls="appMenubar"
                aria-expanded="true"
                aria-label="Contraer menú lateral">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <path d="M7.66699 12.6668L3.66699 8.00016L7.66699 3.3335" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                <path opacity="0.5" d="M12.667 12.6668L8.66699 8.00016L12.667 3.3335" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="app-header-start d-none d-md-flex">
            <div class="gs-wrap">
                <button type="button" id="gs-trigger" class="gs-trigger">
                    <i class="fas fa-search"></i>
                    <span>Buscar en el menú…</span>
                    <kbd class="gs-kbd">⌘K</kbd>
                </button>
                <div class="gs-dropdown" id="gs-dropdown">
                    <div class="gs-search-field">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" id="gs-input" placeholder="Buscar en el menú…" autocomplete="off">
                    </div>
                    <div id="gs-results" class="gs-results"></div>
                </div>
            </div>
        </div>

        <div class="app-header-end">
            @php
                $unreadCount = Auth::user()->unreadNotifications()->count();
                $recentNotifs = Auth::user()->unreadNotifications()->latest()->take(4)->get();
                $extraCount = max(0, $unreadCount - $recentNotifs->count());
            @endphp

            <div class="vr my-3"></div>
            <div class="d-flex align-items-center gap-sm-2 gap-0 px-lg-4 px-sm-2 px-1">
                <div class="dropdown text-end">
                    <button type="button"
                            class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative"
                            id="dropNotif"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            aria-label="Notificaciones">
                        <span class="notif-trigger-ico">{!! \App\Html\IconHelper::render('bell', 20) !!}</span>
                        <div id="notification-badge" class="{{ $unreadCount > 0 ? 'badge-pulse' : '' }}"></div>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end notif-panel-dd" aria-labelledby="dropNotif">
                        <div class="notif-panel-head">
                            <div class="notif-panel-icon">{!! \App\Html\IconHelper::render('bell', 18) !!}</div>
                            <div class="notif-panel-title-wrap">
                                <div class="notif-panel-label">CUENTA</div>
                                <div class="notif-panel-title">
                                    Notificaciones
                                    @if($unreadCount > 0)
                                        <span class="notif-chip">{{ $unreadCount }}</span>
                                    @endif
                                </div>
                            </div>
                            <button class="notif-panel-close" id="btn-notif-close" type="button" aria-label="Cerrar">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>

                        <div class="notif-panel-body">
                            @forelse($recentNotifs as $notif)
                                <a href="{{ route('support.notifications') }}" class="notif-item unread">
                                    <div class="ico">{!! \App\Html\IconHelper::render('bell', 18) !!}</div>
                                    <div class="body">
                                        <div class="head">
                                            <div class="title">{{ $notif->data['title'] ?? 'Notificación' }}</div>
                                            <span class="badge-new">Nuevo</span>
                                        </div>
                                        <div class="desc">{{ $notif->data['message'] ?? '' }}</div>
                                        <div class="meta"><i class="far fa-clock"></i>{{ $notif->created_at->diffForHumans() }}</div>
                                    </div>
                                </a>
                            @empty
                                <div class="notif-panel-empty">
                                    <span class="notif-empty-ico">{!! \App\Html\IconHelper::render('bell-slash', 32) !!}</span>
                                    <h6 class="fw-semibold mb-1">Sin notificaciones</h6>
                                    <p class="text-muted mb-0 small">No tienes notificaciones nuevas</p>
                                </div>
                            @endforelse
                        </div>

                        @if($extraCount > 0)
                            <div class="notif-extra">+{{ $extraCount }} notificaciones adicionales</div>
                        @endif

                        <div class="notif-panel-foot">
                            <a href="{{ route('support.notifications') }}" class="btn btn-primary">Ver todas las notificaciones</a>
                            @if($unreadCount > 0)
                                <button class="btn btn-outline-secondary" id="markAllReadHeader" type="button">Marcar todas como leídas</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="vr my-3"></div>

            <div class="dock-wrap" id="user-dock-wrap">
                <button class="dock-anchor" id="user-dock-trigger" type="button">
                    <div class="av">
                        <img src="{{ Auth::user()->image ? url(Auth::user()->image) : url('managers/images/profile/profile.jpg') }}" alt="Foto de perfil" data-fallback-src="{{ url('managers/images/profile/profile.jpg') }}" />
                        <span class="pres"></span>
                    </div>
                </button>

                <div class="dock" id="user-dock">
                    <div class="dock-head">
                        <div class="av">
                            <img src="{{ Auth::user()->image ? url(Auth::user()->image) : url('managers/images/profile/profile.jpg') }}" alt="Foto de perfil" data-fallback-src="{{ url('managers/images/profile/profile.jpg') }}" />
                        </div>
                        <div class="body">
                            <span class="nm">{{ Str::words(Auth::user()->firstname, 1, '') }} {{ Str::words(Auth::user()->lastname, 1, '') }}</span>
                            <span class="em">{{ Auth::user()->email }}</span>
                        </div>
                    </div>
                    <div class="dock-list">
                        <a href="{{ route('support.settings.profile') }}" class="dock-item">
                            <i class="fas fa-user"></i> Ver perfil
                        </a>
                        <a href="{{ route('support.settings.profile') }}" class="dock-item">
                            <i class="fas fa-gear"></i> Configuración
                        </a>
                        <a href="{{ route('support.notifications') }}" class="dock-item">
                            <i class="fas fa-bell"></i> Notificaciones
                        </a>
                        <div class="dock-divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dock-item danger">
                                <i class="fas fa-arrow-right-from-bracket"></i> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script src="{{ asset('managers/js/includes/header.js') }}" type="text/javascript"></script>
<script src="{{ asset('managers/js/includes/app-toggler.js') }}" type="text/javascript"></script>
<script src="{{ asset('managers/js/includes/app-search.js') }}" type="text/javascript"></script>
@endpush
