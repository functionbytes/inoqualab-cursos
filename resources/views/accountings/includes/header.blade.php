<header class="app-header d-print-none">
    <nav class="navbar navbar-expand-xl navbar-light container-fluid px-0">
        <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
                <a class="nav-link sidebartoggler ms-n3" id="sidebarCollapse" href="javascript:void(0)">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-xl-block">

            </li>

        </ul>
        <div class="d-block d-xl-none">
            <a href="{{  route('home') }}" class="text-nowrap nav-link">
                <img class='logo' src="{{ getlogo() }}" alt="Logo" />
            </a>
        </div>
        <button class="navbar-toggler p-0 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="p-2">
                <i class="fas fa-ellipsis fs-7"></i>
            </span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="d-flex align-items-center justify-content-between px-0 px-xl-8">
                <a href="javascript:void(0)"
                    class="nav-link round-40 p-1 ps-0 d-flex d-xl-none align-items-center justify-content-center"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar"
                    aria-controls="offcanvasWithBothOptions">
                    <i class="fas fa-align-justify fs-7"></i>
                </a>
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">


                    <li class="nav-item dropdown">
                        <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="user-profile-img">
                                    <img src="/managers/images/profile/profile.jpg" class="rounded-circle" width="35"
                                        height="35" alt="Foto de perfil" />
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up"
                            aria-labelledby="drop1">
                            <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                                <img src="/managers/images/profile/profile.jpg" class="rounded-circle" width="50" alt="Foto de perfil" />
                                <div class="ms-3">
                                    <h5 class="mb-1 fs-3 text-uppercase">{{ Str::words(Auth::user()->firstname ,1,'') }} {{
                                        Str::words(Auth::user()->lastname,1,'') }} </h5>
                                    <span class="mb-1 d-block text-dark">
                                        Empresa
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
            </div>
        </div>
    </nav>
</header>