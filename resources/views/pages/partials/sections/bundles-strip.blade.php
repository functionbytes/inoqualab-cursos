{{-- Franja de paquetes destacados — requiere $bundles (con la relación courses cargada,
     para calcular el ahorro) y estar dentro de un wrapper .cursos-page --}}
@if (isset($bundles) && $bundles->isNotEmpty())
    <div class="bundles-strip">
        <div class="bs-head">
            <div>
                <span class="bs-kicker">Ahorra más</span>
                <h2 class="bs-title">Paquetes de cursos</h2>
            </div>
            <a class="bs-all" href="{{ route('bundles') }}">Ver todos los paquetes <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="row">
            @foreach ($bundles as $bundle)
                @php
                    // Mismo cálculo que pages.views.bundles.index: solo mostramos el
                    // ahorro si todos los cursos del paquete tienen precio y la suma
                    // supera el precio del paquete.
                    $coursesSum = 0;
                    $allPriced = $bundle->courses->isNotEmpty();
                    foreach ($bundle->courses as $bc) {
                        if ($bc->price && $bc->price > 0) {
                            $coursesSum += (float) $bc->price;
                        } else {
                            $allPriced = false;
                        }
                    }
                    $showSave = $allPriced && $coursesSum > (float) $bundle->price && $bundle->price > 0;
                    $offPct = $showSave ? (int) round(($coursesSum - $bundle->price) / $coursesSum * 100) : 0;
                @endphp
                <div class="col-lg-4 col-md-6">
                    <a class="ccard" href="{{ route('bundles.view', [$bundle->slug ?? $bundle->slack]) }}">
                        <div class="ccard-media">
                            <div class="ph"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg></div>
                            <span class="ccard-badge premium">Paquete</span>
                        </div>
                        <div class="ccard-body">
                            <div class="ccard-cat">{{ $bundle->courses_count }} {{ $bundle->courses_count == 1 ? 'curso' : 'cursos' }}</div>
                            <div class="ccard-title">{{ $bundle->title }}</div>

                            @if ($bundle->courses->isNotEmpty())
                                <div class="ccard-courses">
                                    @foreach ($bundle->courses->take(4) as $course)
                                        <span class="ccard-course-chip" title="{{ $course->title }}">{{ \Illuminate\Support\Str::limit($course->title, 40) }}</span>
                                    @endforeach
                                    @if ($bundle->courses_count > 4)
                                        <span class="ccard-course-chip">+{{ $bundle->courses_count - 4 }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="ccard-pricing">
                                @if ($showSave)
                                    <div class="ccard-was">Antes $ {{ number_format($coursesSum, 0, ',', '.') }}</div>
                                @endif
                                <div class="ccard-now">
                                    <span class="ccard-now-price">$ {{ number_format($bundle->price, 0, ',', '.') }}</span>
                                    <span class="ccard-now-cur">COP</span>
                                    @if ($showSave)
                                        <span class="ccard-off-pill">-{{ $offPct }}%</span>
                                    @endif
                                </div>
                            </div>

                            <span class="ccard-buy">Ver paquete <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
