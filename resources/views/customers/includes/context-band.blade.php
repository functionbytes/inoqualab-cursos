{{-- Banda de contexto: breadcrumb + título + (opcional) dato destacado de la
     sección, y opcionalmente una fila de tabs debajo (@section('context-tabs'),
     usado por configuración). Solo se muestra si la vista define
     @section('context-title') -- el dashboard no la usa. --}}
@hasSection('context-title')
    <div class="cx-band">
        <div class="cx-band-glow"></div>
        {{-- Contenedor centrado (mismo max-width que el resto del contenido):
             sin él, en pantallas muy anchas el título quedaría pegado al
             borde real de la ventana en vez de alinearse con las tarjetas
             de abajo (que sí respetan el ancho máximo del portal). --}}
        <div class="cx-band-container">
            <div class="cx-band-crumb">
                <a href="{{ route('home') }}">Inicio</a>
                <span>·</span>
                <span class="current">@yield('context-title')</span>
            </div>
            <div class="cx-band-row">
                <div class="cx-band-main">
                    @hasSection('context-icon')
                        <span class="cx-band-icon">@yield('context-icon')</span>
                    @endif
                    <div>
                        <div class="cx-band-heading">@yield('context-title')</div>
                        @hasSection('context-subtitle')
                            <div class="cx-band-sub">@yield('context-subtitle')</div>
                        @endif
                    </div>
                </div>
                {{-- @hasSection('context-stat-number') no sirve aquí: usa empty()
                     por debajo y PHP considera "0" vacío -- justo el valor que
                     legítimamente puede traer un conteo en 0. El label (texto,
                     nunca "0") es el gate seguro para decidir si mostrar el dato. --}}
                @hasSection('context-stat-label')
                    <div class="cx-band-stat">
                        <div class="num">@yield('context-stat-number')</div>
                        <div class="label">@yield('context-stat-label')</div>
                    </div>
                @endif
            </div>
            @hasSection('context-tabs')
                <div class="cx-band-tabs" role="tablist">
                    @yield('context-tabs')
                </div>
            @endif
        </div>
    </div>
@endif
