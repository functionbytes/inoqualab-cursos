<aside class="left-sidebar">
  <div>
    <nav class="sidebar-nav scroll-sidebar container-fluid">
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="fas fa-ellipsis nav-small-cap-icon fs-4"></i>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{  route('home') }}" aria-expanded="false" >
            <span>
               <i class="fa-duotone fa-house"></i>
            </span>
            <span class="hide-menu">Inicio</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{  route('customers.courses') }}" aria-expanded="false" >
            <span>
              <i class="fa-duotone fa-ballot-check"></i>
            </span>
            <span class="hide-menu">Cursos</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{  route('customers.certificates') }}" aria-expanded="false" >
            <span>
              <i class="fa-duotone fa-award"></i>
            </span>
            <span class="hide-menu">Certificados</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{  route('customers.orders') }}" aria-expanded="false" >
            <span>
              <i class="fa-duotone fa-receipt"></i>
            </span>
            <span class="hide-menu">Mis pedidos</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{  route('customers.documents') }}" aria-expanded="false" >
            <span>
              <i class="fa-duotone fa-envelope"></i>
            </span>
            <span class="hide-menu">Documentos</span>
          </a>
        </li>

        <li class="sidebar-item" style="display: none;">
          <button type="button" class="sidebar-link border-0 bg-transparent w-100 text-start">
            <span>
              <i class="fa-duotone fa-headset" aria-hidden="true"></i>
            </span>
            <span class="hide-menu">Soporte</span>
          </button>
        </li>
        <!-- Enlace a la sección de soporte para los clientes (oculto) -->
        <li class="sidebar-item">
          <a class="sidebar-link " href="{{  route('customers.settings') }}" aria-expanded="false" >
            <span>
              <i class="fa-duotone fa-gear-code"></i>
            </span>
            <span class="hide-menu">Configuración</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
 
</aside>
