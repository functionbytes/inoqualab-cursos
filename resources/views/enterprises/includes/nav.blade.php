@php
    $navServiceClass = \App\Services\EnterpriseNavService::class;
    $navData = $navServiceClass::getNavDataForUser();
@endphp

@include('managers.includes.icon-rail-nav', ['navData' => $navData, 'navServiceClass' => $navServiceClass])
