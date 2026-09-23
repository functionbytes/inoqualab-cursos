@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.enterprises.users.certificate.broad', $user->slack) }}" class="btn btn-primary btn-icon" title="Certificado global" aria-label="Certificado global">
    <i class="fa-solid fa-award"></i>
</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Certificados',
        'description' => 'Certificados de ' . $user->firstname . ' ' . $user->lastname,
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.enterprises.users.certificates._table')
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/users/certificates/index.js') }}"></script>
@endpush
