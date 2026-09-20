@php
    $navServiceClass = \App\Services\ManagerNavService::class;
    $navData = $navServiceClass::getNavDataForUser();
@endphp

@include('managers.includes.icon-rail-nav', ['navData' => $navData, 'navServiceClass' => $navServiceClass])

@push('scripts')
<script src="{{ asset('managers/js/includes/nav.js') }}" type="text/javascript"></script>
@endpush
