@php
    $navServiceClass = \App\Services\DistributorNavService::class;
    $navData = $navServiceClass::getNavDataForUser();
@endphp

@include('managers.includes.icon-rail-nav', ['navData' => $navData, 'navServiceClass' => $navServiceClass])
