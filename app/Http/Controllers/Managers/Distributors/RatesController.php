<?php

namespace App\Http\Controllers\Managers\Distributors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Distributors\UpdateDistributorRatesRequest;
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

    public function update(UpdateDistributorRatesRequest $request)
    {
        abort_unless(auth()->user()->can('distributors.update'), 403);

        $data = $request->validated();
        $distributor = Distributor::slack($data['slack']);

        DB::transaction(function () use ($data, $distributor) {
            foreach ($data['courses'] as $id => $price) {
                // Ownership: la tarifa debe pertenecer al distribuidor resuelto arriba,
                // si no cualquier id de distributor_courses ajeno sobreescribe su precio.
                $course = DistributorCourse::find($id);
                if (! $course || $course->distributor_id !== $distributor->id) {
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
