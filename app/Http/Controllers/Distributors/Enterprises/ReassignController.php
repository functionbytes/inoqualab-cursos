<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Illuminate\Http\Request;

class ReassignController extends Controller
{
    /** Empresa que pertenece al distribuidor autenticado, o 404 (evita IDOR). */
    private function managedEnterprise(?string $slack): Enterprise
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        return app('distributor')->enterprises()->where('enterprises.slack', $slack)->firstOrFail();
    }

    /** IDs de las empresas del distribuidor autenticado. */
    private function distributorEnterpriseIds(): array
    {
        return app('distributor')->enterprises()->pluck('enterprises.id')->all();
    }

    /** Usuario que pertenece (enterprise_user) a una empresa del distribuidor, o 404. */
    private function managedUser(?string $slack): User
    {
        // El slack llega de un POST sin validar: sin este guard, pasar
        // null a un parámetro `string` lanzaba TypeError y devolvía un
        // 500 en vez de un 404 limpio.
        abort_if($slack === null || $slack === '', 404);

        $enterpriseIds = $this->distributorEnterpriseIds();

        return User::where('slack', $slack)
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('enterprise_user')
                ->whereColumn('enterprise_user.user_id', 'users.id')
                ->whereIn('enterprise_user.enterprise_id', $enterpriseIds))
            ->firstOrFail();
    }

    public function all($slack)
    {
        // Ownership: la empresa debe pertenecer al distribuidor (evita IDOR por slack).
        $enterprise = $this->managedEnterprise($slack);
        $distributor = app('distributor');
        $enterprises = $distributor->enterprises;
        $users = $enterprise->users()->available()->get();
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');
        $users = $users->pluck('identification', 'identification');

        $enterprises = $enterprises->forget($enterprise->slack);

        return view('distributors.views.enterprises.reassigns.all')->with([
            'users' => $users,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
        ]);

    }

    public function reassignAll(Request $request)
    {

        $users = explode(',', $request->users);
        // Ownership: la empresa destino debe pertenecer al distribuidor.
        $newEnterprise = $this->managedEnterprise($request->enterprise);
        $enterpriseIds = $this->distributorEnterpriseIds();

        foreach ($users as $identification) {

            $user = User::identification($identification);

            // Solo reasigna usuarios que YA pertenecen a una empresa del distribuidor.
            $enterpriseUser = EnterpriseUser::where('user_id', $user->id)
                ->whereIn('enterprise_id', $enterpriseIds)
                ->first();

            if (! $enterpriseUser) {
                continue;
            }

            $enterpriseUser->enterprise_id = $newEnterprise->id;
            $enterpriseUser->save();

        }

        return response()->json([
            'success' => true,
            'enterprise' => $newEnterprise->slack,
            'message' => 'Usuarios reasignados a la nueva empresa correctamente.',
        ]);

    }

    public function single($slack)
    {
        // Ownership: solo usuarios de empresas del distribuidor (evita IDOR por slack).
        $user = $this->managedUser($slack);
        $enterprise = $user->getEnterprise();

        // La empresa puede haberse borrado (soft delete) después de asociar
        // al usuario; getEnterprise() devuelve null en ese caso.
        abort_unless($enterprise instanceof Enterprise, 404, 'La empresa de este usuario ya no existe.');

        $distributor = app('distributor');
        $enterprises = $distributor->enterprises;
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');

        $enterprises = $enterprises->forget($enterprise->slack);

        return view('distributors.views.enterprises.reassigns.single')->with([
            'user' => $user,
            'enterprises' => $enterprises,
            'enterprise' => $enterprise,
        ]);

    }

    public function reassignSingle(Request $request)
    {

        // Ownership: usuario y empresa destino deben pertenecer al distribuidor.
        $user = $this->managedUser($request->slack);
        $newEnterprise = $this->managedEnterprise($request->enterprise);

        $enterpriseUser = EnterpriseUser::where('user_id', $user->id)
            ->whereIn('enterprise_id', $this->distributorEnterpriseIds())
            ->first();

        if ($enterpriseUser) {

            $enterpriseUser->enterprise_id = $newEnterprise->id;
            $enterpriseUser->save();

            return response()->json([
                'success' => true,
                'enterprise' => $newEnterprise->slack,
                'message' => 'Usuario reasignado a la nueva empresa correctamente.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró la relación del usuario con la empresa.',
        ]);

    }
}
