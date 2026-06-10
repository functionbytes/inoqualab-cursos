<?php

namespace App\Http\Controllers\Accountings\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {

        $user = User::auth();

        return view('accountings.views.settings.profile.setting')->with([
            'user' => $user,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = User::slack($request->slack);

        if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (User::where('identification', $request->identification)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
        }

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->available = $user->available;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->update();

        return response()->json(['success' => true, 'message' => '']);
    }
}
