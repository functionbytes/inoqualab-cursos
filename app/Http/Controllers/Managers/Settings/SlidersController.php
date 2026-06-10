<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SlidersController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $sliders = Slider::descending();

        if ($searchKey) {
            $sliders = $sliders->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $sliders = $sliders->where('available', $available);
        }
        $sliders = $sliders->paginate(paginationNumber());

        return view('managers.views.settings.sliders.index')->with([
            'sliders' => $sliders,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $ubications = collect([
            ['id' => '1', 'title' => 'Centro'],
            ['id' => '2', 'title' => 'Izquierda'],
        ]);

        $ubications = $ubications->pluck('title', 'id');

        $position = Slider::count() + 1;

        $availables = $this->availableOptions();

        return view('managers.views.settings.sliders.create')->with([
            'availables' => $availables,
            'ubications' => $ubications,
            'position' => $position,
        ]);

    }

    public function edit($slack)
    {

        $slider = Slider::slack($slack);

        $availables = $this->availableOptions();

        $ubications = collect([
            ['id' => '1', 'title' => 'Centro'],
            ['id' => '2', 'title' => 'Izquierda'],
        ]);

        $ubications = $ubications->pluck('title', 'id');

        $thumbnail = $slider->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';

        return view('managers.views.settings.sliders.edit')->with([
            'thumbnail' => $thumbnail,
            'slider' => $slider,
            'availables' => $availables,
            'ubications' => $ubications,
        ]);

    }

    public function store(Request $request)
    {

        $slider = new Slider;
        $slider->slack = $this->generate_slack('sliders');
        $slider->title = $request->title;
        $slider->subtitle = $request->subtitle;
        $slider->description = $request->description;
        $slider->position = $request->position;
        $slider->available = $request->available;
        $slider->ubication = $request->ubication;
        $slider->url = $request->url;
        $slider->save();

        return response()->json([
            'success' => true,
            'slack' => $slider->slack,
            'message' => 'Se creo el slider correctamente',
        ]);

    }

    public function update(Request $request)
    {

        $slider = Slider::slack($request->slack);
        $slider->title = $request->title;
        $slider->subtitle = $request->subtitle;
        $slider->description = $request->description;
        $slider->position = $request->position;
        $slider->available = $request->available;
        $slider->ubication = $request->ubication;
        $slider->url = $request->url;
        $slider->update();

        return response()->json([
            'success' => true,
            'slack' => $slider->slack,
            'message' => 'Se actualizo el slider correctamente',
        ]);

    }

    public function destroy($slack)
    {

        $slider = Slider::slack($slack);
        $slider->delete();

        return redirect()->route('manager.sliders');

    }

    public function getThumbnails($slack)
    {

        $slider = Slider::slack($slack);

        if ($slider->getMedia('thumbnail')->count() > 0) {

            $thumbnails = $slider->getMedia('thumbnail');

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

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $slider = Slider::slack(Str::remove('"', $request->slider));
            $slider->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'slider' => $slider->slack]);
        }

    }

    public function deleteThumbnails($id)
    {
        Media::find($id)->delete();

        return response()->json(['status' => 'success']);
    }
}
