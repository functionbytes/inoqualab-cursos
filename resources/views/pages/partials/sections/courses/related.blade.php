@if(count($relateds) > 0)
<section class="related">
    <div class="container">
        <h2>Cursos relacionados</h2>
        <div class="related-grid">
            @foreach ($relateds as $related)
                @include('pages.partials.components.course-card', ['course' => $related])
            @endforeach
        </div>
    </div>
</section>
@endif
