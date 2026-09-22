<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Trusteds\StoreTrustedRequest;
use App\Http\Requests\Managers\Settings\Trusteds\UpdateTrustedRequest;
use App\Models\Trusted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrustedsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $trusteds = Trusted::descending();

        if ($searchKey) {
            $trusteds = $trusteds->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $trusteds = $trusteds->where('available', $available);
        }

        $trusteds = $trusteds->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.settings.trusteds._table' : 'managers.views.settings.trusteds.index';

        return view($view)->with([
            'trusteds' => $trusteds,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.settings.trusteds.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $trusted = Trusted::slack($slack);

        $availables = $this->availableOptions();

        $thumbnail = $trusted->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';

        return view('managers.views.settings.trusteds.edit')->with([
            'trusted' => $trusted,
            'thumbnail' => $thumbnail,
            'availables' => $availables,
        ]);

    }

    public function update(UpdateTrustedRequest $request)
    {
        abort_unless(auth()->user()->can('trusteds.update'), 403);

        $trusted = Trusted::id($request->id);
        $trusted->title = $request->title;
        $trusted->slug = Str::slug($request->title, '-');
        $trusted->available = $request->available;
        $trusted->url = $request->url;
        $trusted->update();

        return response()->json([
            'success' => true,
            'slack' => $trusted->slack,
            'message' => 'Se actualizo el aliado correctamente',
        ]);

    }

    public function store(StoreTrustedRequest $request)
    {
        abort_unless(auth()->user()->can('trusteds.create'), 403);

        $trusted = new Trusted;
        $trusted->slack = $this->generate_slack('trusteds');
        $trusted->title = $request->title;
        $trusted->slug = Str::slug($request->title, '-');
        $trusted->available = $request->available;
        $trusted->url = $request->url;
        $trusted->save();

        return response()->json([
            'success' => true,
            'slack' => $trusted->slack,
            'message' => 'Se creo el aliado correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('trusteds.delete'), 403);

        $trusted = Trusted::slack($slack);
        $trusted->delete();

        return redirect()->route('manager.trusteds');

    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:trusteds,id'],
        ]);

        $permission = $request->action === 'delete' ? 'trusteds.delete' : 'trusteds.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = Trusted::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' aliado(s) procesados.']);
    }

    public function getThumbnails($slack)
    {

        $trusted = Trusted::slack($slack);

        if ($trusted->getMedia('thumbnail')->count() > 0) {

            $thumbnails = $trusted->getMedia('thumbnail');

            foreach ($thumbnails as $thumbnail) {

                $images[] = [
                    'id' => $thumbnail->id,
                    'uuid' => $thumbnail->uuid,
                    'name' => $thumbnail->name,
                    'file' => $thumbnail->file_name,
                    'path' => $thumbnail->getfullUrl(),
                    'size' => $thumbnail->size,
                ];
            }

            return response()->json($images);
        }

        $images = [];

        return response()->json($images);

    }

    public function storeThumbnails(Request $request)
    {
        abort_unless(auth()->user()->can('trusteds.update'), 403);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $trusted = Trusted::slack(Str::remove('"', $request->trusted));
            $trusted->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'certifier' => $trusted->slack]);

        }

    }

    public function deleteThumbnails($id)
    {
        abort_unless(auth()->user()->can('trusteds.delete'), 403);

        Media::where('id', $id)
            ->where('model_type', Trusted::class)
            ->where('collection_name', 'thumbnail')
            ->first()?->delete();

        return response()->json(['status' => 'success']);
    }
}
