{{--
    Partial compartido del rail de iconos + panel lateral flotante, usado por
    todos los portales (managers, supports, distributors, enterprises,
    accountings). Cada portal solo aporta su propio NavService (que extiende
    App\Services\Concerns\BaseNavService) vía la variable $navServiceClass, y
    los datos ya resueltos en $navData (return de {NavService}::getNavDataForUser()).
--}}
@php
    ['miniItems' => $miniItems, 'sidebars' => $allSidebars, 'activeSidebarId' => $activeSidebarId, 'activeMiniId' => $activeMiniId, 'activeItemRoute' => $activeItemRoute] = $navData;
@endphp

<aside class="side-mini-panel {{ $activeSidebarId ? 'with-vertical' : '' }}">
    <div class="iconbar">
        <div>
            <!-- Mini Navigation Icons -->
            <div class="mini-nav">
                <div class="brand-logo d-flex align-items-center justify-content-center">
                    <a class="nav-link sidebartoggler" id="iconRailToggler" href="javascript:void(0)">
                        <i class="fa-duotone fa-bars fs-6"></i>
                    </a>
                </div>
                <ul class="mini-nav-ul" data-simplebar>
                    @forelse($miniItems as $miniItem)
                        @php
                            $isActive = $activeMiniId === $miniItem['sidebar_id'];
                        @endphp
                        <li class="mini-nav-item {{ $isActive ? 'selected' : '' }}"
                            id="mini-{{ $miniItem['id'] }}"
                            data-sidebar-id="{{ $miniItem['sidebar_id'] }}"
                            @if(!empty($miniItem['url'])) data-direct-url="{{ route($miniItem['url']) }}" @endif>
                            <a href="{{ !empty($miniItem['url']) ? route($miniItem['url']) : 'javascript:void(0)' }}"
                               data-bs-toggle="tooltip"
                               data-bs-custom-class="custom-tooltip"
                               data-bs-placement="right"
                               data-bs-title="{{ $miniItem['tooltip'] }}">
                                <i class="{{ $miniItem['icon'] }} fs-5"></i>
                            </a>
                        </li>
                    @empty
                        <li class="text-muted text-center p-3">
                            <small>No hay menús disponibles</small>
                        </li>
                    @endforelse
                </ul>
            </div>

            <!-- Sidebar Menus (panel lateral flotante) -->
            <div class="sidebarmenu {{ $activeSidebarId ? '' : 'd-none' }}">
                @forelse($allSidebars as $sidebarId => $sidebar)
                    @php
                        $sidebarIsActive = $activeSidebarId === $sidebarId;
                    @endphp
                    <nav class="sidebar-nav scroll-sidebar {{ $sidebarIsActive ? 'd-block' : 'd-none' }}"
                         id="menu-right-{{ $sidebarId }}"
                         data-simplebar>
                        <ul class="sidebar-menu" id="sidebarnav-{{ $sidebarId }}">
                            @foreach($sidebar['sections'] as $section)
                                <li class="nav-small-cap">
                                    <span class="hide-menu">{{ $section['title'] }}</span>
                                </li>

                                @foreach($section['items'] as $item)
                                    @php
                                        $itemRoute = $item['route'] ?? '';
                                        $hasChildren = ! empty($item['children']);
                                        $isItemActive = $itemRoute
                                            ? (request()->routeIs($itemRoute.'*') || $itemRoute === $activeItemRoute)
                                            // Sin ruta propia (item padre con children): activo si alguno de sus
                                            // hijos coincide con la ruta actual, para que el acordeón se abra solo.
                                            : ($hasChildren && collect($item['children'])->contains(fn ($child) => ! empty($child['route']) && (request()->routeIs($child['route'].'*') || $child['route'] === $activeItemRoute)));
                                        $canAccessItem = $navServiceClass::userCanAccessItem($item, auth()->user());
                                    @endphp

                                    @if($canAccessItem)
                                        <li class="sidebar-item {{ $isItemActive ? 'selected' : '' }}">
                                            @if($hasChildren)
                                                <a href="javascript:void(0)"
                                                   class="sidebar-link has-arrow {{ $isItemActive ? 'active' : '' }}"
                                                   aria-expanded="{{ $isItemActive ? 'true' : 'false' }}">
                                                    @if(!empty($item['icon']))
                                                        <span class="d-flex"><i class="{{ $item['icon'] }}"></i></span>
                                                    @endif
                                                    <span class="hide-menu">{{ $item['label'] }}</span>
                                                </a>
                                                <ul aria-expanded="{{ $isItemActive ? 'true' : 'false' }}"
                                                    class="collapse first-level {{ $isItemActive ? 'in' : '' }}">
                                                    @foreach($item['children'] as $child)
                                                        @php
                                                            $childRoute = $child['route'] ?? '';
                                                            $childIsActive = $childRoute && request()->routeIs($childRoute.'*');
                                                        @endphp
                                                        <li class="sidebar-item">
                                                            <a class="sidebar-link {{ $childIsActive ? 'active' : '' }}" href="{{ $childRoute ? route($childRoute) : 'javascript:void(0)' }}" aria-expanded="false">
                                                                <span><i class="fas fa-circle"></i></span>
                                                                <span class="hide-menu">{{ $child['label'] }}</span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <a href="{{ $itemRoute ? route($itemRoute) : 'javascript:void(0)' }}"
                                                   class="sidebar-link {{ $isItemActive ? 'active' : '' }}"
                                                   aria-expanded="false">
                                                    @if(!empty($item['icon']))
                                                        <span class="d-flex"><i class="{{ $item['icon'] }}"></i></span>
                                                    @endif
                                                    <span class="hide-menu">{{ $item['label'] }}</span>
                                                </a>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            @endforeach
                        </ul>
                    </nav>
                @empty
                    <div class="p-3 text-center text-muted">
                        <small>No hay menús configurados</small>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</aside>

@push('scripts')
<script src="{{ asset('managers/js/includes/icon-rail-nav.js') }}" type="text/javascript"></script>
@endpush
