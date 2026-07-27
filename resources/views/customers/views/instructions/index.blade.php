
@extends('layouts.customers')

@section('title', 'Cursos')

@section('content')



<div class="row">

    <div class="col-lg-3">
        <div class="instructions-category">
            <ul class="category-items">
                <li class="active">
                    <a href="javascript:void(0)" data-category-id="all">
                       Todas
                    </a>
                </li>
                @foreach ($categories as $categorie)
                    <li>
                        <a href="javascript:void(0)" data-category-id="{{ $categorie->id }}">
                            {{ $categorie->title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="col-lg-9">
        
        <div class="instructions-list">
            @foreach ($instructions as $instruction)
                <div class="card data-shadow rounded-3 mb-7">
                    <div class="row">
                    <div class="col-lg-12">
                        <div class="py-2 d-flex flex-column">
                            <div class="d-flex">
                            <p class="badge text-bg-light fs-2 rounded-4 py-1 px-2 lh-sm  mt-0">{{ $instruction->categorie->title }}</p>
                            </div>
                            <h2 class="fw-bolder fs-14 mb-0 mt-1 mb-2">
                                <a href="{{ route('customers.instructions.view', $instruction->slack) }}">{{ $instruction->title }}</a>
                            </h2>
                            {!! clean($instruction->short, 'content') !!}
                            @isset($instruction->tags)
                                <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex ">
                                            <div class="tags">
                                            </div>
                                        </div>
                                </div>
                            @endisset
                        </div>
                    </div>
                    </div>
                </div>
          @endforeach
        </div>
    </div>
  </div>




@endsection



@push('scripts')

    <script type="text/javascript">

        $(document).ready(function() {
            // Agregar clase active al hacer clic en una categoría
            $('.category-items li a').on('click', function() {
                // Eliminar la clase 'active' de todos los elementos li
                $('.category-items li').removeClass('active');

                // Agregar la clase 'active' al li del enlace clickeado
                $(this).parent().addClass('active');

                // Obtener el ID de la categoría clickeada
                const categoryId = $(this).data('category-id');

                // Enviar la solicitud AJAX para filtrar las instrucciones por categoría
                $.ajax({
                    url: '{{ route('customers.instructions.filter') }}',
                    type: 'GET',
                    data: { category_id: categoryId },  // 'category_id' puede ser 'all' si se selecciona Todos
                    success: function(data) {
                        let content = '';
                        data.forEach(instruction => {
                            content += `
                            <div class="card data-shadow rounded-3 mb-7">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="py-2 d-flex flex-column">
                                            <div class="d-flex">
                                                <p class="badge text-bg-light fs-2 rounded-4 py-1 px-2 lh-sm mt-0">${instruction.categorie.title}</p>
                                            </div>
                                            <h2 class="fw-bolder fs-14 mb-0 mt-1 mb-2">
                                                <a href="/customer/instructions/view/${instruction.slack}">${instruction.title}</a>
                                            </h2>
                                            <p class="mb-0">${instruction.short}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        });
                        $('.instructions-list').html(content);
                    }
                });
            });
        });

    </script>


@endpush




