{{-- Franja de paquetes destacados — requiere $bundles (con la relación courses cargada,
     para calcular el ahorro) y estar dentro de un wrapper .cursos-page.
     La tarjeta es pages.partials.components.bundle-card (la misma de /bundles). --}}
@if (isset($bundles) && $bundles->isNotEmpty())
    <div class="bundles-strip">
        <div class="bs-head">
            <div>
                <span class="bs-kicker">Ahorra más</span>
                <h2 class="bs-title">Paquetes de cursos</h2>
            </div>
            <a class="bs-all" href="{{ route('bundles') }}">Ver todos los paquetes <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="bundles-slider">
            @foreach ($bundles as $bundle)
                <div>
                    @include('pages.partials.components.bundle-card', ['bundle' => $bundle])
                </div>
            @endforeach
        </div>
    </div>
@endif
