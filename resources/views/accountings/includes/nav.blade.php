@php
    $navServiceClass = \App\Services\AccountingNavService::class;
    $navData = $navServiceClass::getNavDataForUser();
@endphp

@include('managers.includes.icon-rail-nav', ['navData' => $navData, 'navServiceClass' => $navServiceClass])
