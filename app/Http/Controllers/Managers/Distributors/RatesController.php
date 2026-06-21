<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatesController extends Controller
{
    public function index(Request $request, $slack)
    {

        $distributor = Distributor::slack($slack);

        $rates = $distributor->rates;

        return view('managers.views.distributors.rates.index')->with([
            'distributor' => $distributor,
            'rates' => $rates,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('distributors.update'), 403);

        $distributor = Distributor::slack($request->slack);

        if (! $distributor) {
            return response()->json([
                'success' => false,
                'message' => 'Distribuidor no encontrado.',
            ]);
        }

        DB::transaction(function () use ($request) {
            foreach ($request->courses as $id => $price) {
                $course = DistributorCourse::find($id);
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
        ]);

    }
}
