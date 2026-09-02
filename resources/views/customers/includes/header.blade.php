<header class="app-header">
    <nav class="navbar navbar-expand-xl navbar-light container-fluid px-0 cx-topbar">

        {{-- Logo -- solo en móvil, donde no hay navegación visible junto al
             avatar y el header necesita algo de identidad de marca. --}}
        <a href="{{ route('home') }}" class="cx-logo d-flex d-xl-none" aria-label="Inicio">
            <img class="logo" src="{{ getlogo() }}" alt="{{ setting('page_title') ?: 'INOQUALAB' }}" />
        </a>

        {{-- Navegación -- fusionada en la misma fila del header cuando el
             portal usa layout "horizontal" (el nav vertical clásico, aparte,
             sigue viviendo en customers.includes.nav para layout "vertical"). --}}
        @php
            $navItems = [
                ['href' => route('home'), 'icon' => 'home', 'label' => 'Inicio',
                    'active' => request()->routeIs('customers.dashboard')],
                ['href' => route('customers.courses'), 'icon' => 'cap', 'label' => 'Cursos',
                    'active' => request()->routeIs('customers.courses*') || request()->routeIs('customers.quiz*') || request()->routeIs('customers.exam*')],
                ['href' => route('customers.certificates'), 'icon' => 'award', 'label' => 'Certificados',
                    'active' => request()->routeIs('customers.certificate*')],
                ['href' => route('customers.orders'), 'icon' => 'receipt', 'label' => 'Mis pedidos',
                    'active' => request()->routeIs('customers.orders*')],
                ['href' => route('customers.documents'), 'icon' => 'folder', 'label' => 'Documentos',
                    'active' => request()->routeIs('customers.documents*')],
                ['href' => route('customers.settings'), 'icon' => 'gear', 'label' => 'Configuración',
                    'active' => request()->routeIs('customers.settings*')],
            ];
        @endphp
        @if (($navLayout ?? 'horizontal') === 'horizontal')
            <ul class="cx-nav d-none d-xl-flex">
                @foreach ($navItems as $item)
                    <li>
                        <a href="{{ $item['href'] }}" class="cx-nav-item {{ $item['active'] ? 'active' : '' }}">
                            @include('customers.includes.icon', ['name' => $item['icon'], 'size' => 14])
                            {{ Str::upper($item['label']) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Avatar + menú de usuario (siempre visible, a la derecha).
             En móvil es el único trigger de navegación: no hay burger aparte
             (el que había, `#sidebarCollapse`, no tenía la clase
             `sidebartoggler` que el tema necesita para funcionar -- estaba
             muerto). Aquí el mismo menú trae la navegación completa. --}}
        @php
            $userInitials = strtoupper(
                Str::substr(Auth::user()->firstname, 0, 1)
                . Str::substr(Auth::user()->lastname, 0, 1)
            );
            $userFullName = Str::words(Auth::user()->firstname, 1, '') . ' ' . Str::words(Auth::user()->lastname, 1, '');
        @endphp
        <ul class="navbar-nav flex-row ms-auto align-items-center">
            <li class="nav-item dropdown">
                <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Menú de usuario">
                    <span class="cx-avatar">{{ $userInitials }}</span>
                </a>
                <div class="dropdown-menu cx-user-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                    <div class="cx-user-menu-head">
                        <span class="cx-avatar">{{ $userInitials }}</span>
                        <div>
                            <h5>{{ $userFullName }}</h5>
                            <span>{{ Auth::user()->email }}</span>
                        </div>
                    </div>

                    {{-- Móvil: navegación completa (reemplaza al burger muerto) --}}
                    <div class="cx-user-menu-links d-xl-none">
                        @foreach ($navItems as $item)
                            <a href="{{ $item['href'] }}" class="cx-user-menu-item {{ $item['active'] ? 'is-active' : '' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>

                    {{-- Escritorio: la navegación ya está en la fila del header,
                         aquí solo quedan los accesos directos de la cuenta. --}}
                    <div class="cx-user-menu-links d-none d-xl-block">
                        <a href="{{ route('home') }}" class="cx-user-menu-item">Mi dashboard</a>
                        @if (Auth::user()->role == 'customer')
                            <a href="{{ route('customers.courses') }}" class="cx-user-menu-item">Mis cursos</a>
                        @endif
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="cx-user-menu-logout">Cerrar sesión</button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    {{-- Banda de contexto: parte del header, no una tarjeta del contenido --
         ancho completo y pegada, sin el padding lateral de .container-fluid. --}}
    @include('customers.includes.context-band')
</header>
