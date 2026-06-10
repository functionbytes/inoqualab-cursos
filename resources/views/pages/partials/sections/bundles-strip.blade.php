{{-- Franja de paquetes destacados — requiere $bundles y estar dentro de un wrapper .cursos-page --}}
@if (isset($bundles) && $bundles->isNotEmpty())
    <div class="bundles-strip">
        <div class="bs-head">
            <div>
                <span class="bs-kicker">Ahorra más</span>
                <h2 class="bs-title">Paquetes de cursos</h2>
            </div>
            <a class="bs-all" href="{{ route('bundles') }}">Ver todos los paquetes <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="cat-grid">
            @foreach ($bundles as $bundle)
                <a class="ccard" href="{{ route('bundles.view', [$bundle->slug ?? $bundle->slack]) }}">
                    <div class="ccard-media">
                        <div class="ph"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg></div>
                        <span class="ccard-badge premium">Paquete</span>
                    </div>
                    <div class="ccard-body">
                        <div class="ccard-cat">{{ $bundle->courses_count }} {{ $bundle->courses_count == 1 ? 'curso' : 'cursos' }}</div>
                        <div class="ccard-title">{{ $bundle->title }}</div>
                        <div class="ccard-foot">
                            <span class="ccard-price">$ {{ number_format($bundle->price, 0, ',', '.') }} <small>COP</small></span>
                            <span class="ccard-cta">Ver paquete <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
