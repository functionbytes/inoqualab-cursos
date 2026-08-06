<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Citie;
use Illuminate\Http\Request;

class UtilitiesController extends Controller
{
    public static function getCities(Request $request)
    {
        if ($request->term === '' || $request->term === null) {
            return response()->json([]);
        }

        // Sin límite, un término de un carácter ("a") devolvía TODAS las
        // ciudades que empiezan por esa letra -- payload potencialmente
        // grande servido a peticiones anónimas repetidas, sin throttle en
        // la ruta. 20 resultados es de sobra para un autocompletado.
        $cities = Citie::with('state.countrie')
            ->where('title', 'like', $request->term.'%')
            ->limit(20)
            ->get();

        $formatted = $cities->map(fn ($citie) => [
            'id' => $citie->id,
            'text' => $citie->title.', '.$citie->state->countrie->title,
        ]);

        return response()->json($formatted);
    }
}
