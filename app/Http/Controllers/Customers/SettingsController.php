<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Citie;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $user = app('customer');
        $citie = $user->citie_id;
        $cities = $user->citie_id
            ? Citie::where('id', $user->citie_id)->pluck('title', 'id')
            : collect();

        return view('customers.views.settings.index', compact('user', 'cities', 'citie'));
    }

    public function update(Request $request)
    {
        $user = app('customer');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
            'cellphone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ese correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($request->filled('password')) {
            $user->password = bcrypt($validated['password']);
        }

        $user->cellphone = $validated['cellphone'] ?? $user->cellphone;
        $user->email = $validated['email'];
        $user->address = $validated['address'] ?? $user->address;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente',
        ]);
    }
}
