<div class="widget widget-search wow fadeInUp delay-0-2s animated" style="visibility: visible; animation-name: fadeInUp;">
    {!! Form::open(['route' => ['blogs.filters'], 'class' => 'rbt-search-style-1', 'id' => 'formFilter', 'method' => 'POST', 'files' => true, 'enctype' => 'multipart/form-data']) !!}
    {{ csrf_field() }}
        <input type="text"  id="search" name="search" autocomplete="off" placeholder="Buscar..." required="">
        <button type="submit" class="searchbutton fa fa-search"></button>
    {!! Form::close() !!}
</div>

@push('scripts')

    <script>
        $(document).ready(function() {

            $("#search").keypress(function(e) {
                if (e.which == 13) {

                    $('#formFilter').submit();
                }
            });


            $("#searchButton").click(function() {
                $('#formFilter').submit();
            });

        });
    </script>

@endpush
