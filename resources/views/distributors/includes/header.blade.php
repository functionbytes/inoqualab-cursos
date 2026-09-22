{{--
    Header portado de modules/Theme/resources/views/theme/includes/header.blade.php
    (webadmin), estilizado con managers/css/nav.css + managers/css/includes/header.css.
    Sin campana de notificaciones: el portal distributor no tiene ese sistema
    (no existe distributor.notifications.*).
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
            <a href="{{ route('home') }}" class="d-flex align-items-center">
                <img class="logo" src="{{ getlogo() }}" alt="Logo" />
            </a>
        </div>

        <div class="app-header-end">
            <div class="dock-wrap" id="user-dock-wrap">
                <button class="dock-anchor" id="user-dock-trigger" type="button">
                    <div class="av">
                        <img src="/managers/images/profile/profile.jpg" alt="Foto de perfil" />
                        <span class="pres"></span>
                    </div>
                </button>

                <div class="dock" id="user-dock">
                    <div class="dock-head">
                        <div class="av">
                            <img src="/managers/images/profile/profile.jpg" alt="Foto de perfil" />
                        </div>
                        <div class="body">
                            <span class="nm">{{ Str::words(Auth::user()->firstname, 1, '') }} {{ Str::words(Auth::user()->lastname, 1, '') }}</span>
                            <span class="em">{{ Auth::user()->email }}</span>
                        </div>
                    </div>
                    <div class="dock-list">
                        <a href="{{ route('distributor.settings.profile') }}" class="dock-item">
                            <i class="fas fa-user"></i> Ver perfil
                        </a>
                        <a href="{{ route('distributor.settings.profile') }}" class="dock-item">
                            <i class="fas fa-gear"></i> Configuración
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
@endpush
