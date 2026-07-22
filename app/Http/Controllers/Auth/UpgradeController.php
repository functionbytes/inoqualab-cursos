<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpgradeUserRequest;
use Illuminate\Support\Facades\Auth;

class UpgradeController extends Controller
{
    public function store(UpgradeUserRequest $request)
    {
        $user = Auth::user();

        $data = $request->only(['firstname', 'lastname', 'cellphone', 'address', 'identification', 'email']);
        // El form envía 'citie' (id de ciudad) pero la columna es citie_id: sin este
        // mapeo, update() descartaba la ciudad en silencio (no está en $fillable).
        $data['citie_id'] = $request->citie;
        $data['password'] = $request->password;
        $data['validation'] = 1;

        $user->update($data);

        return response()->json(['status' => 'success']);
    }
}
