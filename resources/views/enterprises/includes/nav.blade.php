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
              <i class="fa-solid fa-house"></i>
            </span>
            <span class="hide-menu">Inicio</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('enterprise.courses') }}" aria-expanded="false">
            <span>
              <i class="fa-solid fa-graduation-cap"></i>
            </span>
            <span class="hide-menu">Cursos</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('enterprise.documents') }}" aria-expanded="false">
            <span>
             <i class="fa-solid fa-folder-open"></i>
            </span>
            <span class="hide-menu">Documentos</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('enterprise.users') }}" aria-expanded="false">
            <span>
              <i class="fa-solid fa-users"></i>
            </span>
            <span class="hide-menu">Usuarios</span>
          </a>
        </li><li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('enterprise.enterprises') }}" aria-expanded="false">
            <span>
              <i class="fa-solid fa-building"></i>
            </span>
            <span class="hide-menu">Empresa</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{ route('enterprise.profile') }}" aria-expanded="false">
            <span>
              <i class="fa-solid fa-gear"></i>
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

