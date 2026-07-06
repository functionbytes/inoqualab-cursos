<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use Illuminate\Http\Request;
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

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('enterprises.update'), 403);

        $enterprise = Enterprise::slack($request->slack);

        DB::transaction(function () use ($request, $enterprise) {
            foreach ($request->courses as $id => $price) {
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
