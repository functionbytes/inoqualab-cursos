<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class ValidationController extends Controller
{
    protected $redirectTo = '/home';

    public function validation()
    {
        seo()->setTitle('Verificación de cuenta')->noindex(true);

        return view('auth.validation');
    }
}
