<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Trusted;
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

        return view('managers.views.settings.trusteds.index')->with([
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

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

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

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

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
        abort_unless(auth()->user()->can('settings.update'), 403);

        $trusted = Trusted::slack($slack);
        $trusted->delete();

        return redirect()->route('manager.trusteds');

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
        abort_unless(auth()->user()->can('settings.update'), 403);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $trusted = Trusted::slack(Str::remove('"', $request->trusted));
            $trusted->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'certifier' => $trusted->slack]);

        }

    }

    public function deleteThumbnails($id)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        Media::find($id)->delete();

        return response()->json(['status' => 'success']);
    }
}
