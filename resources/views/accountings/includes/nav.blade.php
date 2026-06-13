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
          <a class="sidebar-link " href="{{ route('accounting.distributors') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-gear-code"></i>
            </span>
            <span class="hide-menu">Distribuidores</span>
          </a>
        </li>


        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('accounting.invoices') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-gear-code"></i>
            </span>
            <span class="hide-menu">Facturas</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('accounting.orders') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-gear-code"></i>
            </span>
            <span class="hide-menu">Ordenes</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('accounting.profile') }}" aria-expanded="false">
            <span>
              <i class="fa-duotone fa-gear-code"></i>
            </span>
            <span class="hide-menu">Configuración</span>
          </a>
        </li>
      </ul>
    </nav>
    <!-- End Sidebar navigation -->
  </div>
 
</aside>

<!-- Sidebar End -->

