{{--
    Rail de iconos + panel de pestañas, portado de
    modules/Theme/resources/views/theme/includes/nav.blade.php (webadmin) y
    estilizado con managers/css/nav.css (copia literal del nav.css de
    webadmin). Compartido por los 5 portales admin (managers/supports/
    distributors/enterprises/accountings) vía sus respectivos
    {Portal}NavService (App\Services\Concerns\BaseNavService).

    A diferencia de webadmin (donde TODO mini-item tiene un sidebar
    registrado, aunque sea de un solo item), aquí un mini-item puede ser un
    link directo puro sin ningún sidebar asociado (ej. Dashboard). Por eso
    se itera sobre $miniItems (ya ordenado por 'order'), no sobre $sidebars.
--}}
@php
    ['miniItems' => $miniItems, 'sidebars' => $allSidebars, 'activeSidebarId' => $activeSidebarId, 'activeMiniId' => $activeMiniId] = $navData;

    $activeMiniItem = $miniItems->firstWhere('sidebar_id', $activeSidebarId);
    $panelIsOpen = $activeSidebarId !== null && empty($activeMiniItem['url'] ?? null);
@endphp

<!-- begin::Sidebar Menu -->
<aside class="app-menubar-tabs{{ !$panelIsOpen ? ' no-sidebar-open' : '' }}" id="appMenubar">

    <div class="app-navbar-tabs" data-simplebar>
        <ul class="nav" id="appMenubarTabs" role="list" aria-orientation="vertical">

            @foreach($miniItems as $miniItem)
                @if($miniItem['sidebar_id'] === 'settings')
                    <li class="nav-item-hr" role="presentation"></li>
                @endif
                @php
                    $sidebarId = $miniItem['sidebar_id'];
                    $iconKey = $miniItem['icon'] ?? 'dot';
                    $label = $miniItem['tooltip'] ?? ucfirst(str_replace(['-', '_'], ' ', $sidebarId));
                    $isDirect = !empty($miniItem['url']);
                    $isActive = $activeMiniId === $sidebarId;
                @endphp
                <li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ $label }}">
                    @if($isDirect)
                        <a class="menu-link{{ $isActive ? ' active' : '' }}" href="{{ route($miniItem['url']) }}" aria-label="{{ $label }}">
                            <span class="nav-icon">{!! \App\Html\NavIconHelper::render($iconKey) !!}</span>
                        </a>
                    @else
                        <a class="menu-link{{ $isActive ? ' active' : '' }}"
                           href="#tab-{{ $sidebarId }}"
                           aria-controls="tab-{{ $sidebarId }}"
                           aria-label="{{ $label }}"
                           data-bs-toggle="tab">
                            <span class="nav-icon">{!! \App\Html\NavIconHelper::render($iconKey) !!}</span>
                        </a>
                    @endif
                </li>
            @endforeach

        </ul>
    </div>

    <div class="app-tab-content">
        <div class="app-content-inner">
            <div class="tab-content" id="appMenubarTabsContent">

                @foreach($miniItems as $miniItem)
                    @continue(!empty($miniItem['url']))
                    @php
                        $sidebarId = $miniItem['sidebar_id'];
                        $sidebar = $allSidebars[$sidebarId] ?? null;
                    @endphp
                    @continue(!$sidebar)
                    @php $isPaneActive = $activeSidebarId === $sidebarId; @endphp
                    <div class="tab-pane{{ $isPaneActive ? ' show active' : '' }}"
                         id="tab-{{ $sidebarId }}"
                         role="tabpanel"
                         tabindex="0">
                        <nav class="app-navbar" data-simplebar>
                            <ul class="side-menubar">
                                @foreach($sidebar['sections'] ?? [] as $section)
                                    @php
                                        $visibleItems = collect($section['items'] ?? [])
                                            ->filter(fn ($item) => $navServiceClass::userCanAccessItem($item, auth()->user()));
                                    @endphp
                                    @continue($visibleItems->isEmpty())
                                    @if(!empty($section['title']))
                                        <li class="menu-heading">
                                            <span class="menu-label">{{ $section['title'] }}</span>
                                        </li>
                                    @endif
                                    @foreach($visibleItems as $item)
                                        @php
                                            $itemRoute = $item['route'] ?? '';
                                            $itemUrl = $itemRoute ? route($itemRoute) : '#';
                                            $itemActive = $itemRoute && request()->routeIs($itemRoute.'*');
                                        @endphp
                                        <li class="menu-item">
                                            <a class="menu-link{{ $itemActive ? ' active' : '' }}" href="{{ $itemUrl }}" role="button">
                                                <span class="menu-label">{{ $item['label'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

</aside>
<!-- end::Sidebar Menu -->

@push('scripts')
<script src="{{ asset('managers/js/includes/icon-rail-nav.js') }}" type="text/javascript"></script>
@endpush
