<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Testimonie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimoniesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;
        $testimonies = Testimonie::descending();

        if ($searchKey != null) {

            $testimonies->when(! strpos($searchKey, '-'), function ($query) use ($searchKey) {
                $query->where('testimonies.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('testimonies.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(testimonies.firstname, ' ', testimonies.lastname)"), 'like', '%'.$searchKey.'%');
            });
        }

        if ($available != null) {
            $testimonies = $testimonies->where('available', $available);
        }

        $testimonies = $testimonies->paginate(paginationNumber());

        return view('managers.views.settings.testimonies.index')->with([
            'testimonies' => $testimonies,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.settings.testimonies.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $testimonie = Testimonie::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.settings.testimonies.edit')->with([
            'testimonie' => $testimonie,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {

        $testimonie = Testimonie::slack($request->slack);
        $testimonie->firstname = $request->firstname;
        $testimonie->lastname = $request->lastname;
        $testimonie->description = $request->description;
        $testimonie->available = $request->available;
        $testimonie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo el testimonio correctamente',
        ]);

    }

    public function store(Request $request)
    {

        $testimonie = new Testimonie;
        $testimonie->slack = $this->generate_slack('testimonies');
        $testimonie->firstname = $request->firstname;
        $testimonie->lastname = $request->lastname;
        $testimonie->description = $request->description;
        $testimonie->available = $request->available;
        $testimonie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creado el testimonio correctamente',
        ]);

    }

    public function destroy($slack)
    {

        $testimonie = Testimonie::slack($slack);
        $testimonie->delete();

        return redirect()->route('manager.testimonies');

    }
}
