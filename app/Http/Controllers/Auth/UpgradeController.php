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

        $data = $request->only(['firstname', 'lastname', 'cellphone', 'citie', 'address', 'identification', 'email']);
        $data['password'] = $request->password;
        $data['validation'] = 1;

        $user->update($data);

        return response()->json(['status' => 'success']);
    }
}
