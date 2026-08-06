<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Enterprises\UpdateEnterpriseRatesRequest;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use Illuminate\Support\Facades\DB;

class RatesController extends Controller
{
    public function index($slack)
    {
        abort_unless(auth()->user()->can('enterprises.view'), 403);

        $enterprise = Enterprise::slack($slack);
        $rates = $enterprise->rates;

        return view('managers.views.enterprises.rates.index')->with([
            'enterprise' => $enterprise,
            'rates' => $rates,
        ]);

    }

    public function update(UpdateEnterpriseRatesRequest $request)
    {
        // Sin Form Request, `courses` no tenía ninguna validación de precio
        // (a diferencia del controller gemelo Distributors/RatesController,
        // que sí exige numeric|min:0) -- se podía guardar un precio negativo
        // o no numérico en enterprise_course, corrompiendo cualquier cálculo
        // de comisión/factura que lo use.
        $data = $request->validated();
        $enterprise = Enterprise::slack($data['slack']);

        DB::transaction(function () use ($data, $enterprise) {
            foreach ($data['courses'] as $id => $price) {
                // Acotado por enterprise_id: sin esto, se podía enviar el id de una
                // tarifa (enterprise_course) de OTRA empresa y sobreescribir su
                // precio (IDOR).
                $course = EnterpriseCourse::where('id', $id)
                    ->where('enterprise_id', $enterprise->id)
                    ->first();

                if (! $course) {
                    continue;
                }

                $course->price = $price;
                $course->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Precios de los cursos actualizados exitosamente.',
            'slack' => $enterprise->slack,
        ]);

    }
}
