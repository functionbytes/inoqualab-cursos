<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Testimonies\BulkActionTestimonieRequest;
use App\Http\Requests\Managers\Settings\Testimonies\StoreTestimonieRequest;
use App\Http\Requests\Managers\Settings\Testimonies\UpdateTestimonieRequest;
use App\Models\Testimonie;
use Illuminate\Http\JsonResponse;
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
                $query->where(function ($q) use ($searchKey) {
                    $q->where('testimonies.firstname', 'like', '%'.$searchKey.'%')
                        ->orWhere('testimonies.lastname', 'like', '%'.$searchKey.'%')
                        ->orWhere(DB::raw("CONCAT(testimonies.firstname, ' ', testimonies.lastname)"), 'like', '%'.$searchKey.'%');
                });
            });
        }

        if ($available != null) {
            $testimonies = $testimonies->where('available', $available);
        }

        $testimonies = $testimonies->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.settings.testimonies._table' : 'managers.views.settings.testimonies.index';

        return view($view)->with([
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

    public function update(UpdateTestimonieRequest $request)
    {
        abort_unless(auth()->user()->can('testimonies.update'), 403);

        $testimonie = Testimonie::slack($request->slack);
        $testimonie->firstname = $request->firstname;
        $testimonie->lastname = $request->lastname;
        $testimonie->role = $request->role;
        $testimonie->icon = $request->icon;
        $testimonie->rating = $request->rating ?? 5;
        $testimonie->benefit = $request->benefit;
        $testimonie->position = $request->position ?? 0;
        $testimonie->counter_value = $request->counter_value;
        $testimonie->counter_suffix = $request->counter_suffix;
        $testimonie->description = $request->description;
        $testimonie->available = $request->available;
        $testimonie->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó el testimonio correctamente',
        ]);

    }

    public function store(StoreTestimonieRequest $request)
    {
        abort_unless(auth()->user()->can('testimonies.create'), 403);

        $testimonie = new Testimonie;
        $testimonie->slack = $this->generate_slack('testimonies');
        $testimonie->firstname = $request->firstname;
        $testimonie->lastname = $request->lastname;
        $testimonie->role = $request->role;
        $testimonie->icon = $request->icon;
        $testimonie->rating = $request->rating ?? 5;
        $testimonie->benefit = $request->benefit;
        $testimonie->position = $request->position ?? 0;
        $testimonie->counter_value = $request->counter_value;
        $testimonie->counter_suffix = $request->counter_suffix;
        $testimonie->description = $request->description;
        $testimonie->available = $request->available;
        $testimonie->save();

        return response()->json([
            'success' => true,
            'message' => 'Se creó el testimonio correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('testimonies.delete'), 403);

        $testimonie = Testimonie::slack($slack);
        $testimonie->delete();

        return redirect()->route('manager.testimonies');

    }

    public function bulkAction(BulkActionTestimonieRequest $request): JsonResponse
    {
        $query = Testimonie::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' testimonio(s) procesados.']);
    }
}
