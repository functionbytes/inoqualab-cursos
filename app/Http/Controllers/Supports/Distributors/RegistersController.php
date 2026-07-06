<?php

namespace App\Http\Controllers\Supports\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistersController extends Controller
{
    public function index($slack)
    {

        $distributor = Distributor::slack($slack);
        $enterprises = $distributor->enterprises;
        $enterprises->prepend('', '');
        $enterprises = $enterprises->pluck('title', 'slack');

        return view('supports.views.distributors.registers.index')->with([
            'enterprises' => $enterprises,
            'distributor' => $distributor,
        ]);

    }

    public function store(Request $request)
    {
        $enterprise = Enterprise::slack($request->enterprise);

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if ($request->identification && User::where('identification', $request->identification)->exists()) {
            return response()->json(['success' => false, 'message' => 'La identificación ya está registrada en nuestro sistema.']);
        }

        DB::transaction(function () use ($request, $enterprise) {
            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->cellphone = $request->cellphone;
            $user->identification = $request->identification;
            $user->email = $request->email;
            $user->address = $request->address;
            // Sin password explícita, un valor aleatorio (no la identificación,
            // un dato semi-público) — el cliente la establece vía "olvidé mi contraseña".
            $user->password = $request->filled('password') ? $request->password : Str::random(16);
            $user->available = 1;
            $user->role = 'customer';
            $user->terms = 1;
            $user->page = 0;
            $user->setting = 0;
            $user->validation = 1;
            $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $inscription = new EnterpriseUser;
            $inscription->user_id = $user->id;
            $inscription->enterprise_id = $enterprise->id;
            $inscription->available = 1;
            $inscription->save();
        });

        return response()->json(['success' => true, 'message' => 'Se inscribió el cliente correctamente.']);
    }

    public function check(Request $request)
    {

        $user = User::where('identification', $request->identification)->first();

        if ($user) {

            $enterpriseUser = EnterpriseUser::where('user_id', $user->id)->first();

            if ($enterpriseUser) {

                $enterprise = Enterprise::find($enterpriseUser->enterprise_id);

                if ($enterprise) {

                    $distributorEnterprise = DistributorEnterprise::where('enterprise_id', $enterprise->id)->first();

                    if ($distributorEnterprise) {

                        $distributor = Distributor::find($distributorEnterprise->distributor_id);

                        $response = [
                            'success' => true,
                            'message' => 'Este usuario ya está registrado y asignado a la empresa: '.$enterprise->title,
                            'enterprise' => $enterprise->title,
                            'distributor' => $distributor->title,
                            'url_reassign' => route('support.enterprises.users.reassign', ['slack' => $user->slack]),
                            'url_enterprise' => route('support.enterprises.users', ['slack' => $enterprise->slack]),

                        ];

                    } else {
                        $response = [
                            'success' => true,
                            'message' => 'Este usuario ya está registrado en la empresa: '.$enterprise->title.', pero no está asignado a ningún distribuidor.',
                            'enterprise' => $enterprise->title,
                            'distributor' => null,
                        ];
                    }
                }
            } else {
                $response = [
                    'success' => true,
                    'message' => 'Este usuario ya está registrado pero no está asignado a ninguna empresa.',
                    'enterprise' => null,
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Este usuario no está registrado.',
            ];
        }

        return response()->json($response);
    }
}
