<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpgradeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|min:2|max:200',
            'lastname' => 'required|string|min:2|max:200',
            'cellphone' => 'required|numeric|digits_between:8,20',
            'citie' => 'required',
            'address' => 'required|string|min:10|max:500',
            'identification' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        $data = $request->only(['firstname', 'lastname', 'cellphone', 'citie', 'address', 'identification', 'email']);
        $data['password'] = Hash::make($request->password);
        $data['validation'] = 1;

        $user->update($data);

        return response()->json(['status' => 'success']);
    }
}
