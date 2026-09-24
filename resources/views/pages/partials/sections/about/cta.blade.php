{{-- Cierre común de las variantes de "Sobre nosotros" (a/b/c).
     Props: $tone = 'navy' | 'blue' (fondo del bloque). --}}
<section class="ab-cta ab-cta--{{ $tone ?? 'navy' }}">
    <div class="ab-wrap ab-cta-inner">
        <div class="ab-cta-copy">
            <h2 class="ab-cta-title">¿Tu equipo manipula alimentos?</h2>
            <p>Capacítalo con cursos virtuales certificados y sigue su avance desde el portal para empresas.</p>
        </div>
        <div class="ab-cta-side">
            <div class="ab-cta-actions">
                <a href="{{ route('courses') }}" class="ab-btn ab-btn--white">Ver cursos</a>
                <a href="{{ route('contacts') }}" class="ab-btn ab-btn--ghost-light">Hablar con un asesor</a>
            </div>
        </div>
    </div>
</section>
