<?php

namespace App\Http\Controllers\Supports\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supports\UpdateSupportNotificationsRequest;
use App\Http\Requests\Supports\UpdateSupportProfileRequest;
use App\Models\User;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function profile()
    {
        $user = User::auth();

        return view('supports.views.settings.profile.setting')->with([
            'user' => $user,
        ]);
    }

    public function notifications()
    {
        $user = User::auth();

        return view('supports.views.settings.setting.setting')->with([
            'user' => $user,
        ]);
    }

    public function updateProfile(UpdateSupportProfileRequest $request)
    {
        // El perfil siempre opera sobre el usuario autenticado; nunca sobre
        // un identificador recibido en el request (previene IDOR).
        $user = User::auth();

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->support = $request->filled('support') ? Str::upper($request->support) : null;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        if ($user->isDirty()) {
            $user->save();

            $changes = $user->getChanges();
            unset($changes['password'], $changes['updated_at']);

            activity()
                ->performedOn($user)
                ->withProperties($changes)
                ->log('updated');
        }

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente.',
        ]);
    }

    public function updateNotifications(UpdateSupportNotificationsRequest $request)
    {
        $user = User::auth();

        $user->email_notification = $request->boolean('mail_notification') ? 1 : 0;
        $user->status_notification = $request->boolean('inscription_notification') ? 1 : 0;
        $user->order_notification = $request->boolean('invoice_notification') ? 1 : 0;

        if ($user->isDirty()) {
            $user->save();

            activity()
                ->performedOn($user)
                ->withProperties($user->getChanges())
                ->log('updated');
        }

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente.',
        ]);
    }
}
