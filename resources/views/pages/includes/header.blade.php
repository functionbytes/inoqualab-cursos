
<!-- main header -->
<header class="main-header header-two">
    <!-- Header-Top -->
    <div class="header-top bg-dark-blue text-white">
        <div class="container">
            <div class="top-inner">
                <div class="top-left">
                    <div class=" d-flex align-items-center">
                        <div class="col-md-12 col-sm-12">
                            <p><i class="fas fa-clock"></i>  Lunes a viernes: {{ setting("page_hour_weekend") }} - Sabados: {{ setting("page_hour_weekends") }} </p>
                        </div>

                    </div>
                </div>
                <div class="top-right d-flex align-items-center">
                    <div class="social-style-two">
                        @if (setting('social_media_facebook')!=null)
                            <a href="{{ setting('social_media_facebook') }}"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if (setting('social_media_instagram')!=null)
                            <a href="{{ setting('social_media_instagram') }}"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if (setting('social_media_twitter')!=null)
                            <a href="{{ setting('social_media_twitter') }}"><i class="fab fa-twitter"></i></a>
                        @endif
                        @if (setting('social_media_linkedin')!=null)
                            <a href="{{ setting('social_media_linkedin') }}"><i class="fab fa-linkedin-in"></i></a>
                        @endif
                        @if (setting('social_media_youtube')!=null)
                            <a href="{{ setting('social_media_youtube') }}"><i class="fab fa-youtube-in"></i></a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header-Upper -->
    <div class="header-upper">
        <div class="container clearfix">

            <div class="header-inner d-flex align-items-center justify-content-between">
                <div class="logo-outer">
                    <div class="logo">
                        <a href="{{ route('index') }}">
                              <img src="{{ getlogo() }}" width="180" alt="" />
                         </a>
                    </div>
                </div>

                <div class="nav-outer clearfix">
                    
                    <!-- Main Menu -->
                    <nav class="main-menu navbar-expand-lg">
                        <div class="navbar-header">
                            <div class="mobile-logo br-10 p-15">
                                <a href="{{ route('index') }}">
                                   @if(count($setting->getMedia('logo'))>0)
                                     <img src="{{ $setting->getfirstMedia('logo')->getfullUrl() }}" width="160" alt="{{ setting('page_title') }}"
                                          onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block';" />
                                     <span class="mobile-logo-text" style="display:none;">{{ setting('page_title') }}</span>
                                   @else
                                     <span class="mobile-logo-text">{{ setting('page_title') }}</span>
                                   @endif
                                </a>
                            </div>

                            <div class="mobile-header-actions">
                                <a href="{{ route('cart.index') }}" class="mobile-cart-btn position-relative">
                                    <i class="fas fa-shopping-cart"></i>
                                    @php $cartCount = count(session('cart', [])); @endphp
                                    @if($cartCount > 0)
                                        <span class="cart-count-badge">{{ $cartCount }}</span>
                                    @endif
                                </a>
                                @if(Auth::check())
                                    <a href="{{ route('customers.dashboard') }}" class="mobile-user-btn">
                                        <i class="fas fa-user-circle"></i>
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="mobile-login-btn">Ingresar</a>
                                @endif
                            </div>

                            <!-- Toggle Button -->
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>
                        <div class="navbar-collapse collapse clearfix">
                            <ul class="navigation clearfix">
                                <li><a href="{{ route('index') }}">INICIO</a></li>
                                <li><a href="{{ route('about') }}">SOBRE NOSOTROS</a></li>
                                <li class="dropdown"><a href="{{ route('courses') }}">CURSOS</a>
                                    <ul>
                                        @foreach ($allcourses as $course)
                                            <li><a href="{{ route('courses.view', array($course->slack)) }}"> {{ $course->title }}</a></li>
                                        @endforeach
                                    </ul>
                                </li>
                                @if($hasActiveBundles)
                                <li><a href="{{ route('bundles') }}">PAQUETES</a></li>
                                @endif
                                <li><a href="{{ route('contacts') }}">CONTACTO</a></li>
                            </ul>
                        </div>
                    </nav>
                    <!-- Main Menu End-->
                </div>
                <div class="menu-btns d-lg-flex d-none align-items-center">
                    <a href="{{ route('cart.index') }}" class="cart-btn mr-3 position-relative" style="color:inherit;">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if($cartCount > 0)
                            <span class="cart-count-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @if(Auth::check())
                            <div class="menu-btn-sidebar d-flex align-items-center">
                                <div class=" navbar-collapse justify-content-end" id="navbarNav">
                                    <div class="d-flex align-items-center justify-content-between px-0 px-xl-8">
                                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                                
                                            <li class="nav-item dropdown">
                                                <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-profile-img">
                                                            <img src="/managers/images/profile/profile.jpg" class="rounded-circle" width="35"
                                                                height="35" alt="" />
                                                        </div>
                                                    </div>
                                                </a>
                                                <div class="dropdown-menu content-dd  dropdown-menu-end dropdown-menu-animate-up"aria-labelledby="drop1">
                                                    <div class="d-flex align-items-center py-9 mx-7 border-bottom content-user">
                                                        <img src="/managers/images/profile/profile.jpg" class="rounded-circle" width="50" alt="" />
                                                        <div class="ms-3">
                                                            <h5 class="mb-0 fs-3">{{ Str::words(Auth::user()->firstname ,1,'') }} {{Str::words(Auth::user()->lastname,1,'') }} </h5>
                                                            <span class="mb-0 d-block text-dark">
                                                                {{ Auth::user()->identification }}
                                                            </span>
                                                            <span class="mb-0 d-block text-dark">
                                                                @if (Auth::user()->role == 'manager')
                                                                    Administrador
                                                                @elseif (Auth::user()->role == 'customer')
                                                                    Cliente
                                                                @elseif (Auth::user()->role == 'customer')
                                                                    Empresa
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center pt-20 pb-20 mx-7 border-bottom">

                                                        @if (Auth::user()->role != 'customer')
                                                           <div class="message-body">
                                                            <a href="{{  route('home') }}" class="py-8 px-7 d-flex align-items-center ">
                                                                <span class="d-flex align-items-center justify-content-center bg-light rounded-1 p-6">
                                                                    <i class="fas fa-house"></i>
                                                                </span>
                                                                <div class="w-75 d-inline-block v-middle ps-3">
                                                                    <h6 class="mb-1 bg-hover-primary "> Mi dashboard</h6>
                                                                    <span class="d-block text-dark">Visualiza tu inicio</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        @elseif (Auth::user()->role == 'customer')
                                                            <div class="message-body">
                                                                <a href="{{  route('home') }}" class="py-8 px-7 d-flex align-items-center mb-2">
                                                                    <span class="d-flex align-items-center justify-content-center bg-light rounded-1 p-6">
                                                                        <i class="fas fa-house"></i>
                                                                    </span>
                                                                    <div class="w-75 d-inline-block v-middle ps-3">
                                                                        <h6 class="mb-1 bg-hover-primary text-normal "> Mi dashboard</h6>
                                                                        <span class="d-block text-dark">Visualiza tu inicio</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{  route('customers.courses') }}" class="py-8 px-7 d-flex align-items-center">
                                                                    <span class="d-flex align-items-center justify-content-center bg-light rounded-1 p-6">
                                                                        <i class="fas fa-list-check"></i>
                                                                    </span>
                                                                    <div class="w-75 d-inline-block v-middle ps-3">
                                                                        <h6 class="mb-1 bg-hover-primary text-normal">Mis cursos</h6>
                                                                        <span class="d-block text-dark">Visualiza tus cursos</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @elseif (Auth::user()->role == 'enterprise')
                                                        <div class="message-body">
                                                                <a href="{{  route('home') }}" class="py-8 px-7 d-flex align-items-center ">
                                                                    <span class="d-flex align-items-center justify-content-center bg-light rounded-1 p-6">
                                                                        <i class="fas fa-house"></i>
                                                                    </span>
                                                                    <div class="w-75 d-inline-block v-middle ps-3">
                                                                        <h6 class="mb-1 bg-hover-primary "> Mi dashboard</h6>
                                                                        <span class="d-block text-dark">Visualiza tu inicio</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endif
                                                        
                                                    </div>
                                                    <div class="d-flex align-items-center py-9 mx-7 ">
                                                        <form action="{{ route('logout') }}" method="POST" class="w-100">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-info px-4 waves-effect waves-light w-100">Salir</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                    @else
                    <a href="{{ route('login') }}" class="theme-btn">Ingresar</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!--End Header Upper-->
</header>
       