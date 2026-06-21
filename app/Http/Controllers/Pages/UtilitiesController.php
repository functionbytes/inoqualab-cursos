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

        $cities = Citie::with('state.countrie')
            ->where('title', 'like', $request->term.'%')
            ->get();

        $formatted = $cities->map(fn ($citie) => [
            'id' => $citie->id,
            'text' => $citie->title.', '.$citie->state->countrie->title,
        ]);

        return response()->json($formatted);
    }
}
