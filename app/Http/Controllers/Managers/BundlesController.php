<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\StoreBundleRequest;
use App\Http\Requests\Managers\UpdateBundleRequest;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BundlesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $bundles = Bundle::withCount('courses')->with('media')->descending();

        if ($searchKey != null) {
            $bundles = $bundles->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $bundles = $bundles->where('available', $available);
        }

        $bundles = $bundles->paginate(paginationNumber());

        return view('managers.views.settings.bundles.index')->with([
            'bundles' => $bundles,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $courses = Course::latest()->available()->get();
        $courses = $courses->pluck('title', 'id');

        $availables = $this->availableOptions(true);

        return view('managers.views.settings.bundles.create')->with([
            'availables' => $availables,
            'courses' => $courses,
        ]);

    }

    public function edit($slack)
    {

        $bundle = Bundle::slack($slack);

        $courses = Course::latest()->available()->get();
        $courses = $courses->pluck('title', 'id');

        $availables = $this->availableOptions();

        $keywords = collect(explode(',', $bundle->meta_keywords));

        $thumbnail = $bundle->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';

        return view('managers.views.settings.bundles.edit')->with([
            'bundle' => $bundle,
            'courses' => $courses,
            'availables' => $availables,
            'keywords' => $keywords,
            'thumbnail' => $thumbnail,
        ]);
    }

    public function update(UpdateBundleRequest $request)
    {
        abort_unless(auth()->user()->can('bundles.update'), 403);

        $bundle = Bundle::slack($request->slack);
        $bundle->title = Str::upper($request->title);
        $bundle->slug = Str::slug($request->title, '-');
        $bundle->description = $request->description;
        $bundle->price = $request->price;
        $bundle->available = $request->available;
        $bundle->meta_title = $request->meta_title;
        $bundle->meta_description = $request->meta_description;
        $bundle->meta_keywords = $request->meta_keywords;
        $bundle->start_date = Carbon::parse($request->start_date);
        $bundle->expire_at = Carbon::parse($request->expire_at);
        $bundle->update();

        $bundle->courses()->detach();

        if ($request->courses != null) {
            foreach (explode(',', $request->courses) as $key => $id) {
                $bundle->courses()->attach($key, ['course_id' => $id]);
            }
        }

        return response()->json([
            'success' => true,
            'slack' => $bundle->slack,
            'message' => 'Se actualizo el paquete correctamente',
        ]);

    }

    public function store(StoreBundleRequest $request)
    {
        abort_unless(auth()->user()->can('bundles.create'), 403);

        $bundle = new Bundle;
        $bundle->slack = $this->generate_slack('bundles');
        $bundle->title = Str::upper($request->title);
        $bundle->slug = Str::slug($request->title, '-');
        $bundle->description = $request->description;
        $bundle->price = $request->price;
        $bundle->available = $request->available;
        $bundle->meta_title = $request->meta_title;
        $bundle->meta_description = $request->meta_description;
        $bundle->meta_keywords = $request->meta_keywords;
        $bundle->start_date = Carbon::parse($request->start_date);
        $bundle->expire_at = Carbon::parse($request->expire_at);
        $bundle->save();

        if ($request->courses != null) {
            foreach (explode(',', $request->courses) as $key => $id) {
                $bundle->courses()->attach($key, ['course_id' => $id]);
            }
        }

        return response()->json([
            'success' => true,
            'slack' => $bundle->slack,
            'message' => 'Se creado el paquete correctamente',
        ]);

    }

    public function getThumbnails($slack)
    {

        $bundle = Bundle::slack($slack);

        if ($bundle->getMedia('thumbnail')->count() > 0) {

            $thumbnails = $bundle->getMedia('thumbnail');

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

            $bundle = Bundle::slack($request->bundle);
            $bundle->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'bundle' => $bundle->slack]);

        }

    }

    public function deleteThumbnails($id)
    {
        Media::find($id)->delete();

        return response()->json(['status' => 'success']);
    }

    public function toggleAvailable(Request $request): JsonResponse
    {
        $bundle = Bundle::slack($request->slack);
        $bundle->available = $bundle->available ? 0 : 1;
        $bundle->save();

        return response()->json([
            'success' => true,
            'available' => $bundle->available,
            'message' => $bundle->available ? 'Paquete publicado' : 'Paquete ocultado',
        ]);
    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('bundles.delete'), 403);

        $bundle = Bundle::slack($slack);
        $bundle->delete();

        return redirect()->route('manager.bundles');
    }
}
