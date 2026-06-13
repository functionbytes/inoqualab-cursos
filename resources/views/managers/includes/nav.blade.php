
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
                    <i class="fas fa-ellipsis nav-small-cap-icon fs-4"></i>
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
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Cursos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.categories.courses') }}" aria-expanded="false">
                                  <span>
                                    <i class="fas fa-circle"></i>
                                  </span>
                                <span class="hide-menu">Categorias</span>
                            </a>
                        </li>
                    </ul>
                </li>

                @if(setting('module_coupons') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.coupons') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-ballot-check"></i>
                          </span>
                        <span class="hide-menu">Cupones</span>
                    </a>
                </li>
                @endif

                @if(setting('module_bundles') !== 0)
                <li class="sidebar-item">
                  <a class="sidebar-link" href="{{ route('manager.bundles') }}" aria-expanded="false">
                        <span class="d-flex">
                          <i class="fa-duotone fa-ballot-check"></i>
                        </span>
                      <span class="hide-menu">Paquetes</span>
                  </a>
              </li>
                @endif


                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.orders') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-memo-pad"></i>
                          </span>
                        <span class="hide-menu">Ordenes</span>
                    </a>
                </li>
                @if(setting('module_incoming_mail') !== 0)
                @php
                    $pendingMailsCount = \App\Models\Mail\IncomingMail::query()
                        ->whereIn('status', ['pending_review', 'failed'])
                        ->count();
                @endphp
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.mails.index') }}" aria-expanded="false">
                        <span class="d-flex">
                          <i class="fa-duotone fa-memo-pad"></i>
                        </span>
                        <span class="hide-menu">Correos entrantes</span>
                    </a>
                </li>
                @endif
                @if(setting('module_invoices') !== 0)
                <li class="sidebar-item">
                  <a class="sidebar-link" href="{{ route('manager.invoices') }}" aria-expanded="false">
                        <span class="d-flex">
                          <i class="fa-duotone fa-memo-pad"></i>
                        </span>
                      <span class="hide-menu">Facturas</span>
                  </a>
              </li>
                @endif

                @if(setting('module_departments') !== 0)
                <li class="sidebar-item">
                  <a class="sidebar-link" href="{{ route('manager.departments') }}" aria-expanded="false">
                        <span class="d-flex">
                         <i class="fa-duotone fa-message-smile"></i>
                        </span>
                      <span class="hide-menu">Departamentos</span>
                  </a>
               </li>
                @endif

                @if(setting('module_documents') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.documents') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-ballot-check"></i>
                          </span>
                        <span class="hide-menu">Documentos</span>
                    </a>
                </li>
                @endif
                @if(setting('module_contacts') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.contacts') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-envelope"></i>
                          </span>
                        <span class="hide-menu">Contactenos</span>
                    </a>
                </li>
                @endif
                @can('roles.view')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.roles.index') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-user-shield"></i>
                          </span>
                        <span class="hide-menu">Roles y permisos</span>
                    </a>
                </li>
                @endcan
                @if(setting('module_newsletter') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-paper-plane"></i>
                          </span>
                        <span class="hide-menu">Newsletter</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.newsletter.index') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Suscriptores</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.newsletter.campaigns.index') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Campañas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.settings.newsletter') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Configuración</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(setting('module_reviews') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.reviews') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-star"></i>
                          </span>
                        <span class="hide-menu">Reseñas</span>
                    </a>
                </li>
                @endif



                <li class="nav-small-cap">
                    <i class="fas fa-ellipsis nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Plataforma</span>
                </li>

                @if(setting('module_certifications') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.certifications') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-file-certificate"></i>
                          </span>
                        <span class="hide-menu">Certificados</span>
                    </a>
                </li>
                @endif
                @if(setting('module_certifiers') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.certifiers') }}" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-user-doctor-message"></i>
                          </span>
                        <span class="hide-menu">Capacitadores</span>
                    </a>
                </li>
                @endif
                @if(setting('module_enterprises') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.enterprises') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-house-medical-circle-check"></i>
                          </span>
                        <span class="hide-menu">Empresas</span>
                    </a>
                </li>
                @endif
                @if(setting('module_distributors') !== 0)
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.distributors') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-building-circle-arrow-right"></i>
                          </span>
                        <span class="hide-menu">Distribuidores</span>
                    </a>
                </li>
                @endif
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('manager.users') }}" aria-expanded="false">
                          <span class="d-flex">
                            <i class="fa-duotone fa-user-vneck-hair"></i>
                          </span>
                        <span class="hide-menu">Usuarios</span>
                    </a>
                </li>

                <li class="nav-small-cap">
                    <i class="fas fa-ellipsis nav-small-cap-icon fs-4"></i>
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
{{--                                  <i class="fas fa-circle"></i>--}}
{{--                                </span>--}}
{{--                                <span class="hide-menu">Tickets</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link" href="{{ route('manager.tickets.categories') }}" aria-expanded="false">--}}
{{--                                  <span>--}}
{{--                                    <i class="fas fa-circle"></i>--}}
{{--                                  </span>--}}
{{--                                <span class="hide-menu">Categoria</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link" href="{{ route('manager.tickets.status') }}" aria-expanded="false">--}}
{{--                                  <span>--}}
{{--                                    <i class="fas fa-circle"></i>--}}
{{--                                  </span>--}}
{{--                                <span class="hide-menu">Estado</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="{{ route('manager.tickets.priorities') }}" aria-expanded="false">--}}
{{--                              <span>--}}
{{--                                <i class="fas fa-circle"></i>--}}
{{--                              </span>--}}
{{--                                <span class="hide-menu">Prioridad</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="{{ route('manager.tickets.groups') }}" aria-expanded="false">--}}
{{--                              <span>--}}
{{--                                <i class="fas fa-circle"></i>--}}
{{--                              </span>--}}
{{--                                <span class="hide-menu">Grupos</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                          <a class="sidebar-link" href="{{ route('manager.tickets.canneds') }}" aria-expanded="false">--}}
{{--                            <span>--}}
{{--                              <i class="fas fa-circle"></i>--}}
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
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Preguntas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.faqs.categories') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
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
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Instrucciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.instructions.categories') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
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
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Configuración</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.analytics') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Google Analytics</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.pixel') }}" aria-expanded="false">
                                  <span>
                                    <i class="fas fa-circle"></i>
                                  </span>
                                <span class="hide-menu">Pixel Analytics</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.emails') }}" aria-expanded="false">
                                  <span>
                                    <i class="fas fa-circle"></i>
                                  </span>
                                <span class="hide-menu">Smtp</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.metadata') }}" aria-expanded="false">
                                  <span>
                                    <i class="fas fa-circle"></i>
                                  </span>
                                <span class="hide-menu">Seo</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.invoices') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Facturación</span>
                            </a>
                        </li>

{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="#" aria-expanded="false">--}}
{{--                                  <span><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="hide-menu">Ticket</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-item">--}}
{{--                            <a class="sidebar-link"  href="#" aria-expanded="false">--}}
{{--                                  <span><i class="fas fa-circle"></i></span>--}}
{{--                                <span class="hide-menu">Chat</span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.hours') }}" aria-expanded="false">
                                  <span>
                                    <i class="fas fa-circle"></i>
                                  </span>
                                <span class="hide-menu">Horario</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.maintenance') }}" aria-expanded="false">
                              <span>
                                <i class="fas fa-circle"></i>
                              </span>
                                <span class="hide-menu">Mantenimiento</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link"  href="{{ route('manager.settings.payments') }}" aria-expanded="false">
                              <span>
                                <i class="fas fa-circle"></i>
                              </span>
                                <span class="hide-menu">Pagos / Wompi</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.settings.newsletter') }}" aria-expanded="false">
                              <span>
                                <i class="fas fa-circle"></i>
                              </span>
                                <span class="hide-menu">Newsletter</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.settings.incoming-mail') }}" aria-expanded="false">
                              <span>
                                <i class="fas fa-circle"></i>
                              </span>
                                <span class="hide-menu">Correos entrantes</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.settings.modules') }}" aria-expanded="false">
                              <span>
                                <i class="fas fa-circle"></i>
                              </span>
                                <span class="hide-menu">Módulos</span>
                            </a>
                        </li>
                    </ul>
                </li>


                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
                          <span class="d-flex">
                           <i class="fa-duotone fa-square-poll-vertical"></i>
                          </span>
                        <span class="hide-menu">Analytics</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.analytics') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.settings.analytics.schedules.index') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Reportes programados</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('manager.settings.analytics') }}" aria-expanded="false">
                                <span>
                                  <i class="fas fa-circle"></i>
                                </span>
                                <span class="hide-menu">Configuración</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item">
    <a class="sidebar-link has-arrow" href="#" aria-expanded="false">
        <span class="d-flex"><i class="fas fa-magnifying-glass-chart"></i></span>
        <span class="hide-menu">SEO</span>
    </a>
    <ul aria-expanded="false" class="collapse first-level">
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.dashboard') }}">
                <span class="hide-menu">Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.metas.index') }}">
                <span class="hide-menu">Meta tags</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.page-urls.index') }}">
                <span class="hide-menu">URLs del sitio</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.orphans.index') }}">
                <span class="hide-menu">Sin SEO</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.redirects.index') }}">
                <span class="hide-menu">Redirecciones</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.logs.index') }}">
                <span class="hide-menu">Errores 404</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.static-urls.index') }}">
                <span class="hide-menu">URLs sitemap</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.sitemap.index') }}">
                <span class="hide-menu">Sitemap</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.robots.index') }}">
                <span class="hide-menu">robots.txt</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.llms.index') }}">
                <span class="hide-menu">llms.txt</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.seo.indexnow.index') }}">
                <span class="hide-menu">IndexNow</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="{{ route('manager.settings.seo.index') }}">
                <span class="hide-menu">Configuracion</span>
            </a>
        </li>
    </ul>
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


