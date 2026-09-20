<div class="widget widget-search wow fadeInUp delay-0-2s animated">
    {!! Form::open(['route' => ['blogs.filters'], 'class' => 'rbt-search-style-1', 'id' => 'formFilter', 'method' => 'POST', 'files' => true, 'enctype' => 'multipart/form-data']) !!}
    {{ csrf_field() }}
        <input type="text" id="search" name="search" autocomplete="off" placeholder="Buscar artículo..." aria-label="Buscar artículo" required="">
        <button type="submit" class="searchbutton fa fa-search"></button>
    {!! Form::close() !!}
</div>

@push('scripts')
    <script src="{{ asset('pages/js/partials/sections/blogs/search.js') }}"></script>
@endpush
