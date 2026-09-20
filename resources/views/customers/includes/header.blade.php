{{-- Ítems de navegación: se usan tanto en la franja .cx-navbar (desktop) como
     en el menú de usuario (móvil, reemplaza al burger). --}}
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
<header class="app-header">
    {{-- Fila superior: marca + cuenta. La navegación ya no comparte esta fila
         -- vive en su propia franja debajo (.cx-navbar), separada visualmente
         ("dos capas": quién soy arriba, dónde estoy abajo). El logo ahora se
         ve también en desktop, no solo en móvil. --}}
    <nav class="navbar navbar-expand-xl navbar-light container-fluid px-0 cx-topbar">

        <a href="{{ route('home') }}" class="cx-logo" aria-label="Inicio">
            <img class="logo" src="{{ getlogo() }}" alt="{{ setting('page_title') ?: 'INOQUALAB' }}" />
        </a>

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
                        <div class="cx-user-menu-id">
                            <span class="cx-avatar">{{ $userInitials }}</span>
                            <div>
                                <h5>{{ $userFullName }}</h5>
                                <span>{{ Auth::user()->email }}</span>
                            </div>
                        </div>
                        {{-- No es data-bs-dismiss (eso es de modal/alert/offcanvas, no de
                             dropdown) -- un botón plano alcanza: el autoClose por defecto
                             de Bootstrap ya cierra el dropdown con cualquier click dentro
                             que no sea sobre un control de formulario. --}}
                        <button type="button" class="cx-user-menu-close" aria-label="Cerrar menú">
                            @include('customers.includes.icon', ['name' => 'x'])
                        </button>
                    </div>

                    {{-- Contenedor scrollable: en móvil la cabecera queda fija arriba
                         (ver .cx-user-menu.content-dd.show a pantalla completa en
                         portal.css) y esto es lo único que hace scroll si la lista
                         no entra completa. --}}
                    <div class="cx-user-menu-scroll">
                        {{-- Móvil: navegación completa (reemplaza al burger muerto) --}}
                        <div class="cx-user-menu-links d-xl-none">
                            <div class="cx-user-menu-label">Navegación</div>
                            @foreach ($navItems as $item)
                                <a href="{{ $item['href'] }}" class="cx-user-menu-item {{ $item['active'] ? 'is-active' : '' }}">
                                    <span class="cx-umi-ic">@include('customers.includes.icon', ['name' => $item['icon']])</span>
                                    <span class="cx-umi-t">{{ $item['label'] }}</span>
                                    <span class="cx-umi-chev">@include('customers.includes.icon', ['name' => 'arrow-right'])</span>
                                </a>
                            @endforeach
                        </div>

                        {{-- Escritorio: la navegación ya está en la fila del header,
                             aquí solo quedan los accesos directos de la cuenta. --}}
                        <div class="cx-user-menu-links d-none d-xl-block">
                            <a href="{{ route('home') }}" class="cx-user-menu-item">
                                <span class="cx-umi-ic">@include('customers.includes.icon', ['name' => 'home'])</span>
                                <span class="cx-umi-t">Mi dashboard</span>
                                <span class="cx-umi-chev">@include('customers.includes.icon', ['name' => 'arrow-right'])</span>
                            </a>
                            @if (Auth::user()->role == 'customer')
                                <a href="{{ route('customers.courses') }}" class="cx-user-menu-item">
                                    <span class="cx-umi-ic">@include('customers.includes.icon', ['name' => 'cap'])</span>
                                    <span class="cx-umi-t">Mis cursos</span>
                                    <span class="cx-umi-chev">@include('customers.includes.icon', ['name' => 'arrow-right'])</span>
                                </a>
                            @endif
                        </div>

                        <div class="cx-user-menu-divider"></div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="cx-user-menu-item cx-user-menu-logout">
                                <span class="cx-umi-ic">@include('customers.includes.icon', ['name' => 'logout'])</span>
                                <span class="cx-umi-t">Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </nav>

    {{-- Franja de navegación: solo en desktop -- en móvil los mismos ítems
         viven dentro del menú de usuario (.cx-user-menu-links.d-xl-none más
         arriba), no hay burger aparte. Layout "vertical" sigue usando el
         sidebar de customers.includes.nav en vez de esto. --}}
    @if (($navLayout ?? 'horizontal') === 'horizontal')
        <nav class="cx-navbar d-none d-xl-block">
            <div class="container-fluid px-0">
                <ul class="cx-nav">
                    @foreach ($navItems as $item)
                        <li>
                            <a href="{{ $item['href'] }}" class="cx-nav-item {{ $item['active'] ? 'active' : '' }}">
                                @include('customers.includes.icon', ['name' => $item['icon'], 'size' => 13])
                                {{ Str::upper($item['label']) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>
    @endif

    {{-- Banda de contexto: parte del header, no una tarjeta del contenido --
         ancho completo y pegada, sin el padding lateral de .container-fluid. --}}
    @include('customers.includes.context-band')
</header>
