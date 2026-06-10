<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Citie;
use Illuminate\Http\Request;

class UtilitiesController extends Controller
{
    public static function getCities(Request $request)
    {

        if ($request->term != '') {
            $cities = Citie::where('title', 'like', $request->term.'%')->get();
            $formatted_tags = [];
            foreach ($cities as $citie) {
                $formatted_tags[] = ['id' => $citie->id, 'text' => $citie->title.', '.$citie->state->countrie->title];
            }
        } else {
            $formatted_tags = [];
        }

        return \Response::json($formatted_tags);
    }
}
