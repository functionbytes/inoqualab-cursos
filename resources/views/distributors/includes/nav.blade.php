<!-- Sidebar Start -->

<aside class="left-sidebar">
  <!-- Sidebar scroll-->
  <div>
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav scroll-sidebar container-fluid">
      <ul id="sidebarnav">
        <!-- ============================= -->
        <!-- Home -->
        <!-- ============================= -->
        <li class="nav-small-cap">
          <i class="fas fa-ellipsis nav-small-cap-icon fs-4"></i>
        </li>
        <!-- =================== -->
        <!-- Dashboard -->
        <!-- =================== -->
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{  route('home') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-house"></i>
            </span>
            <span class="hide-menu">Inicio</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('distributor.enterprises') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-house-medical-circle-check"></i>
            </span>
            <span class="hide-menu">Empresas</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('distributor.registers') }}" aria-expanded="false">
          <span>
            <i class="fa-duotone fa-gear-code"></i>
          </span>
            <span class="hide-menu">Crear usuario</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
              <span>
                <i class="fa-duotone fa-note"></i>
              </span>
            <span class="hide-menu">Inscripciones</span>
          </a>
          <ul aria-expanded="false" class="collapse first-level">
            <li class="sidebar-item">
              <a href="{{ route('distributor.inscriptions') }}" class="sidebar-link">
                <span class="hide-menu">Inscripciones</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('distributor.inscriptions.massives') }}" class="sidebar-link">
                <span class="hide-menu">Inscripciones masiva</span>
              </a>
            </li>
          </ul>
        </li>


        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('distributor.invoices') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-gear-code"></i>
            </span>
            <span class="hide-menu">Facturas</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('distributor.orders') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-gear-code"></i>
            </span>
            <span class="hide-menu">Ordenes</span>
          </a>
        </li>
{{--        <li class="sidebar-item">--}}
{{--              <a class="sidebar-link " href="{{ route('distributor.supports') }}" aria-expanded="false">--}}
{{--            <span>--}}
{{--              <i class="fa-duotone fa-gear-code"></i>--}}
{{--            </span>--}}
{{--                  <span class="hide-menu">Soporte</span>--}}
{{--              </a>--}}
{{--          </li>--}}
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-note"></i>
            </span>
            <span class="hide-menu">Configuración</span>
          </a>
          <ul aria-expanded="false" class="collapse first-level">
            <li class="sidebar-item">
              <a href="{{ route('distributor.settings.distributor') }}" class="sidebar-link">
                <span class="hide-menu">Distribuidor </span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('distributor.settings.profile') }}" class="sidebar-link">
                <span class="hide-menu">Usuario</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('distributor.settings.notifications') }}" class="sidebar-link">
                <span class="hide-menu">Notificaciones</span>
              </a>
            </li>
          </ul>
        </li>


      </ul>
    </nav>
    <!-- End Sidebar navigation -->
  </div>
 
</aside>

<!-- Sidebar End -->

