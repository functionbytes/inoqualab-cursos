@forelse($instructions as $instruction)
    <a class="ins-card" href="{{ route('customers.instructions.view', $instruction->slack) }}">
        <span class="ins-badge">{{ $instruction->categorie->title ?? 'General' }}</span>
        <h3>{{ $instruction->title }}</h3>
        @if($instruction->short)
            <p>{{ Str::limit(strip_tags($instruction->short), 140) }}</p>
        @endif
        <span class="ins-more">Leer más</span>
    </a>
@empty
    <div class="pnl-empty">
        <span class="ic">@include('customers.includes.icon', ['name' => 'book'])</span>
        <h3>No hay guías en esta categoría</h3>
        <p>Elige otra categoría del panel de la izquierda.</p>
    </div>
@endforelse
