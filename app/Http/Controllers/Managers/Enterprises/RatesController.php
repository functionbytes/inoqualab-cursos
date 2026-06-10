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

        $enterprise = Enterprise::slack($slack);
        $rates = $enterprise->rates;

        return view('managers.views.enterprises.rates.index')->with([
            'enterprise' => $enterprise,
            'rates' => $rates,
        ]);

    }

    public function update(Request $request)
    {

        $enterprise = Enterprise::slack($request->slack);

        DB::transaction(function () use ($request) {
            foreach ($request->courses as $id => $price) {
                $course = EnterpriseCourse::find($id);
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
