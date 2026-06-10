<?php

namespace App\Html;

class HtmlBuilder
{
    public function favicon(string $url): string
    {
        return '<link rel="shortcut icon" href="'.e($url).'">';
    }
}
