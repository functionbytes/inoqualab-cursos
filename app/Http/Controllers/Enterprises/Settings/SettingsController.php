<?php

namespace App\Http\Controllers\Enterprises\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {

        $user = User::auth();

        return view('enterprises.views.settings.edit')->with([
            'user' => $user,
        ]);

    }

    public function update(Request $request)
    {

        $user = User::auth();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->identification = $request->identification;
        $user->address = $request->address;

        if ($request->password != null) {
            $user->password = $request->password;
        }

        $user->update();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);

    }
}
