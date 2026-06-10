
<!-- Sidebar Start -->

<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <ul id="sidebarnav">
                <li class="nav-small-cap">
                    
                    <span class="hide-menu">Inicio</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.dashboard') }}" aria-expanded="false">
                  <span>
                    <i class="fa-duotone fa-house"></i>
                  </span>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Contenido</span>
                </li>


                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow " href="#" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-ballot-check"></i>
                          </span>
                        <span class="hide-menu">Cursos</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.courses') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Cursos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.categories.courses') }}" aria-expanded="false">
                                  <span>
                                    <i class="ti ti-circle"></i>
                                  </span>
                                <span class="hide-menu">Categorias</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.coupons') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-ballot-check"></i>
                          </span>
                        <span class="hide-menu">Cupones</span>
                    </a>
                </li>

                <li class="sidebar-item">
                  <a class="sidebar-link" href="{{ route('manager.bundles') }}" aria-expanded="false">
                        <span class="d-flex">
                          <i class="fa-duotone fa-ballot-check"></i>
                        </span>
                      <span class="hide-menu">Paquetes</span>
                  </a>
              </li>


                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.orders') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-memo-pad"></i>
                          </span>
                        <span class="hide-menu">Ordenes</span>
                    </a>
                </li>
                @php
                    $pendingMailsCount = \App\Models\Mail\IncomingMail::query()
                        ->whereIn('status', ['pending_review', 'failed'])
                        ->count();
                @endphp
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.mails.index') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-envelope-open-text"></i>
                          </span>
                        <span class="hide-menu d-flex align-items-center justify-content-between">
                            Correos entrantes
                            <span id="pending-mails-badge" class="badge bg-danger rounded-pill ms-2"
                                  style="font-size:10px{{ $pendingMailsCount > 0 ? '' : ';display:none' }}"
                                  data-poll-url="{{ route('manager.mails.pending-count') }}">{{ $pendingMailsCount > 99 ? '99+' : $pendingMailsCount }}</span>
                        </span>
                    </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link" href="{{ route('manager.invoices') }}" aria-expanded="false">
                        <span class="d-flex">
                          <i class="fa-duotone fa-memo-pad"></i>
                        </span>
                      <span class="hide-menu">Facturas</span>
                  </a>
              </li>

                <li class="sidebar-item">
                  <a class="sidebar-link" href="{{ route('manager.departments') }}" aria-expanded="false">
                        <span class="d-flex">
                         <i class="fa-duotone fa-message-smile"></i>
                        </span>
                      <span class="hide-menu">Departamentos</span>
                  </a>
               </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.documents') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-ballot-check"></i>
                          </span>
                        <span class="hide-menu">Documentos</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.contacts') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-envelope"></i>
                          </span>
                        <span class="hide-menu">Contactenos</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.reviews') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-star"></i>
                          </span>
                        <span class="hide-menu">Reseñas</span>
                    </a>
                </li>



                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Plataforma</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.certifications') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-file-certificate"></i>
                          </span>
                        <span class="hide-menu">Certificados</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.certifiers') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-user-doctor-message"></i>
                          </span>
                        <span class="hide-menu">Capacitadores</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.enterprises') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-house-medical-circle-check"></i>
                          </span>
                        <span class="hide-menu">Empresas</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.distributors') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-building-circle-arrow-right"></i>
                          </span>
                        <span class="hide-menu">Distribuidores</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.users') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-user-vneck-hair"></i>
                          </span>
                        <span class="hide-menu">Usuarios</span>
                    </a>
                </li>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Configuración</span>
                </li>


{{--                <li class="sidebar-item">--}}
{{--                    <a class="sidebar-link has-arrow " href="#" aria-expanded="false">--}}
{{--                          <span class="d-flex">--}}
{{--                            <i class="fa-duotone fa-note"></i>--}}
{{--                          </span>--}}
{{--                        <span class="hide-menu">Ticket</span>--}}
{{--                    </a>--}}
{{--                    <ul aria-expanded="false" class="collapse first-level">--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="{{ route('manager.tickets') }}" aria-expanded="false">--}}
{{--                                <span>--}}
{{--                                  <i class="ti ti-circle"></i>--}}
{{--                                </span>--}}
{{--                                <span class="hide-menu">Tickets</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link" href="{{ route('manager.tickets.categories') }}" aria-expanded="false">--}}
{{--                                  <span>--}}
{{--                                    <i class="ti ti-circle"></i>--}}
{{--                                  </span>--}}
{{--                                <span class="hide-menu">Categoria</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link" href="{{ route('manager.tickets.status') }}" aria-expanded="false">--}}
{{--                                  <span>--}}
{{--                                    <i class="ti ti-circle"></i>--}}
{{--                                  </span>--}}
{{--                                <span class="hide-menu">Estado</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="{{ route('manager.tickets.priorities') }}" aria-expanded="false">--}}
{{--                              <span>--}}
{{--                                <i class="ti ti-circle"></i>--}}
{{--                              </span>--}}
{{--                                <span class="hide-menu">Prioridad</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="{{ route('manager.tickets.groups') }}" aria-expanded="false">--}}
{{--                              <span>--}}
{{--                                <i class="ti ti-circle"></i>--}}
{{--                              </span>--}}
{{--                                <span class="hide-menu">Grupos</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                          <a class="sidebar-link" href="{{ route('manager.tickets.canneds') }}" aria-expanded="false">--}}
{{--                            <span>--}}
{{--                              <i class="ti ti-circle"></i>--}}
{{--                            </span>--}}
{{--                            <span class="hide-menu">Respuestas</span>--}}
{{--                          </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}



                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow " href="#" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-circle-exclamation"></i>
                          </span>
                        <span class="hide-menu">Preguntas</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.faqs') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Preguntas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.faqs.categories') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Categorias</span>
                            </a>
                        </li>
                    </ul>
                </li>



                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow " href="#" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-circle-exclamation"></i>
                          </span>
                        <span class="hide-menu">Instrucciones</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.instructions') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Instrucciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.instructions.categories') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Categorias</span>
                            </a>
                        </li>
                    </ul>
                </li>


                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow " href="#" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-gear-code"></i>
                          </span>
                        <span class="hide-menu">Configuración</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Configuración</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.analytics') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Google Analytics</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.pixel') }}" aria-expanded="false">
                                  <span>
                                    <i class="ti ti-circle"></i>
                                  </span>
                                <span class="hide-menu">Pixel Analytics</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.emails') }}" aria-expanded="false">
                                  <span>
                                    <i class="ti ti-circle"></i>
                                  </span>
                                <span class="hide-menu">Smtp</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.metadata') }}" aria-expanded="false">
                                  <span>
                                    <i class="ti ti-circle"></i>
                                  </span>
                                <span class="hide-menu">Seo</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.invoices') }}" aria-expanded="false">
                                <span>
                                  <i class="ti ti-circle"></i>
                                </span>
                                <span class="hide-menu">Facturación</span>
                            </a>
                        </li>

{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="#" aria-expanded="false">--}}
{{--                                  <span><i class="ti ti-circle"></i></span>--}}
{{--                                <span class="hide-menu">Ticket</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="#" aria-expanded="false">--}}
{{--                                  <span><i class="ti ti-circle"></i></span>--}}
{{--                                <span class="hide-menu">Chat</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.hours') }}" aria-expanded="false">
                                  <span>
                                    <i class="ti ti-circle"></i>
                                  </span>
                                <span class="hide-menu">Horario</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.maintenance') }}" aria-expanded="false">
                              <span>
                                <i class="ti ti-circle"></i>
                              </span>
                                <span class="hide-menu">Mantenimiento</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.payments') }}" aria-expanded="false">
                              <span>
                                <i class="ti ti-circle"></i>
                              </span>
                                <span class="hide-menu">Pagos / Wompi</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.settings.incoming-mail') }}" aria-expanded="false">
                              <span>
                                <i class="ti ti-circle"></i>
                              </span>
                                <span class="hide-menu">Correos entrantes</span>
                            </a>
                        </li>
                    </ul>
                </li>


                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.analytics') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-square-poll-vertical"></i>
                          </span>
                        <span class="hide-menu">Estadisticas</span>
                    </a>
                </li>

            </ul>

        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>

<!-- Sidebar End -->

@push('scripts')
<script>
(function () {
    var $badge = $('#pending-mails-badge');
    if (!$badge.length) return;
    var url = $badge.data('poll-url');
    setInterval(function () {
        $.getJSON(url, function (r) {
            if (r.count > 0) {
                $badge.text(r.count > 99 ? '99+' : r.count).show();
            } else {
                $badge.hide();
            }
        });
    }, 60000);
}());
</script>
@endpush


