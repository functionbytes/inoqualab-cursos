<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewslettersController extends Controller
{
    public function store(Request $request)
    {
        if (setting('newsletter_enabled') === 0 || setting('newsletter_enabled') === '0') {
            return response()->json('disabled');
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
        ]);

        $exists = Newsletter::validate($request->email)->exists();

        if (! $exists) {
            $newsletter = new Newsletter;
            $newsletter->email = $request->email;
            $newsletter->save();

            return response()->json('success');
        }

        return response()->json('failed');
    }
}
