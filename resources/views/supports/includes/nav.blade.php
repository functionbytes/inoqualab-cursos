@php
    $navServiceClass = \App\Services\SupportNavService::class;
    $navData = $navServiceClass::getNavDataForUser();
@endphp

@include('managers.includes.icon-rail-nav', ['navData' => $navData, 'navServiceClass' => $navServiceClass])
