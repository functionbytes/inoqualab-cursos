<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\UpdateManagerPasswordRequest;
use App\Http\Requests\Managers\UpdateManagerProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /** Directorio público donde se guardan los avatares de usuario. */
    private const AVATAR_DIR = 'managers/images/profile';

    public function edit(): View
    {
        return view('managers.views.profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateManagerProfileRequest $request): JsonResponse
    {
        // El perfil SIEMPRE es del usuario autenticado (nunca un id del request).
        $user = auth()->user();

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->identification = $request->identification;
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;

        if ($request->hasFile('avatar')) {
            $user->user_img = $this->storeAvatar($request->file('avatar'), $user);
        }

        if ($user->isDirty()) {
            $user->save();

            $changes = $user->getChanges();
            unset($changes['updated_at']);

            activity()
                ->performedOn($user)
                ->withProperties($changes)
                ->log('updated');
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'image' => $user->user_img ? url($user->user_img) : null,
        ]);
    }

    public function updatePassword(UpdateManagerPasswordRequest $request): JsonResponse
    {
        // El Form Request ya verificó la contraseña actual (regla current_password).
        $user = auth()->user();

        // El modelo hashea via el mutator de 'password' (no usar Hash::make aquí).
        $user->password = $request->password;
        $user->save();

        activity()->performedOn($user)->log('password_changed');

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    /** Guarda el avatar en public/ y devuelve la ruta relativa; borra el anterior si era local. */
    private function storeAvatar(UploadedFile $file, $user): string
    {
        $dir = public_path(self::AVATAR_DIR);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Eliminar avatar previo si apuntaba a un archivo local de este directorio.
        if ($user->user_img && Str::startsWith($user->user_img, self::AVATAR_DIR)) {
            $previous = public_path($user->user_img);
            if (is_file($previous)) {
                @unlink($previous);
            }
        }

        $filename = $user->slack.'-'.now()->format('YmdHis').'.'.$file->getClientOriginalExtension();
        $file->move($dir, $filename);

        return self::AVATAR_DIR.'/'.$filename;
    }
}
