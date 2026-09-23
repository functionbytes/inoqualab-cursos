<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\BulkActionBundleRequest;
use App\Http\Requests\Managers\StoreBundleRequest;
use App\Http\Requests\Managers\UpdateBundleRequest;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $view = request()->ajax() ? 'managers.views.settings.bundles._table' : 'managers.views.settings.bundles.index';

        return view($view)->with([
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

        $thumbnail = $bundle->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';

        return view('managers.views.settings.bundles.edit')->with([
            'bundle' => $bundle,
            'courses' => $courses,
            'availables' => $availables,
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

        // detach()+attach() en loop, sin transacción y sin validar que los
        // ids existan: un id inexistente/borrado a mitad de la lista tiraba
        // QueryException por la FK de bundle_course, dejando el bundle SIN
        // NINGÚN curso (detach() ya se había ejecutado y confirmado). sync()
        // es una sola operación atómica, y se filtra contra cursos reales.
        $courseIds = $this->resolveCourseIds($request->courses);

        DB::transaction(function () use ($bundle, $courseIds) {
            $bundle->courses()->sync($courseIds);
        });

        return response()->json([
            'success' => true,
            'slack' => $bundle->slack,
            'message' => 'Se actualizó el paquete correctamente',
        ]);

    }

    /** Ids de curso válidos a partir de un string "1,2,3" (ignora ids inexistentes). */
    private function resolveCourseIds(?string $courses): array
    {
        if ($courses === null || $courses === '') {
            return [];
        }

        $ids = array_filter(explode(',', $courses), fn ($id) => $id !== '');

        return Course::query()->whereIn('id', $ids)->pluck('id')->all();
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

        $courseIds = $this->resolveCourseIds($request->courses);

        // Riesgo residual (condición de carrera: un curso borrado entre
        // resolveCourseIds() y el attach()) menor que en update(), pero real
        // -- sin transacción, el bundle quedaba creado sin sus cursos si el
        // attach() fallaba.
        DB::transaction(function () use ($bundle, $courseIds) {
            $bundle->save();

            if (! empty($courseIds)) {
                $bundle->courses()->attach($courseIds);
            }
        });

        return response()->json([
            'success' => true,
            'slack' => $bundle->slack,
            'message' => 'Se creó el paquete correctamente',
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
        abort_unless(auth()->user()->can('bundles.update'), 403);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $bundle = Bundle::slack($request->bundle);
            $bundle->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'bundle' => $bundle->slack]);

        }

    }

    public function deleteThumbnails($id)
    {
        abort_unless(auth()->user()->can('bundles.delete'), 403);

        Media::where('id', $id)
            ->where('model_type', Bundle::class)
            ->first()?->delete();

        return response()->json(['status' => 'success']);
    }

    public function toggleAvailable(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('bundles.update'), 403);

        $bundle = Bundle::slack($request->slack);

        // El scope slack() devuelve el Builder cuando no hay match.
        if (! $bundle instanceof Bundle) {
            return response()->json(['success' => false, 'message' => 'Paquete no encontrado.'], 404);
        }

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

    public function bulkAction(BulkActionBundleRequest $request): JsonResponse
    {
        $query = Bundle::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' paquete(s) procesados.']);
    }
}
