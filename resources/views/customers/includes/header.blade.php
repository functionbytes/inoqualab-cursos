<header class="app-header">
    <nav class="navbar navbar-expand-xl navbar-light container-fluid px-0 cx-topbar">

        {{-- Menú lateral (solo móvil) --}}
        <a class="cx-burger d-flex d-xl-none" id="sidebarCollapse" href="javascript:void(0)" aria-label="Abrir menú">
            @include('customers.includes.icon', ['name' => 'menu', 'size' => 22])
        </a>

        {{-- Logo (solo móvil, centrado) --}}
        <a href="{{ route('home') }}" class="cx-logo d-flex d-xl-none" aria-label="Inicio">
            <img class="logo" src="{{ getlogo() }}" alt="{{ setting('page_title') ?: 'INOQUALAB' }}" />
        </a>

        {{-- Avatar + menú de usuario (siempre visible, a la derecha) --}}
        <ul class="navbar-nav flex-row ms-auto align-items-center">
            <li class="nav-item dropdown">
                <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Menú de usuario">
                    <span class="cx-avatar">
                        <img src="/managers/images/profile/profile.jpg" alt="{{ Auth::user()->firstname }}" />
                    </span>
                </a>
                <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                    <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                        <img src="/managers/images/profile/profile.jpg" class="rounded-circle" width="50" height="50" alt="" />
                        <div class="ms-3">
                            <h5 class="mb-1 fs-3 text-uppercase">{{ Str::words(Auth::user()->firstname, 1, '') }} {{ Str::words(Auth::user()->lastname, 1, '') }}</h5>
                            <span class="mb-0 d-block text-dark">{{ Auth::user()->identification }}</span>
                            <span class="mb-0 d-block text-dark">
                                @if (Auth::user()->role == 'manager') Administrador @elseif (Auth::user()->role == 'customer') Cliente @endif
                            </span>
                        </div>
                    </div>
                    <div class="d-grid py-4 px-7 pt-8">
                        <form action="{{ route('logout') }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-info px-4 waves-effect waves-light w-100">Salir</button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</header>
