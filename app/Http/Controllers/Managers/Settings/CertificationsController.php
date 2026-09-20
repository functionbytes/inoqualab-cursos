<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Certifications\StoreCertificationRequest;
use App\Http\Requests\Managers\Settings\Certifications\UpdateCertificationRequest;
use App\Models\Certification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CertificationsController extends Controller
{
    public function index(Request $request): View
    {

        $searchKey = $request->search;
        $available = $request->available;
        $certifications = Certification::descending()->with('media');

        if ($searchKey != null) {
            $certifications = $certifications->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $certifications = $certifications->where('available', $available);
        }

        $certifications = $certifications->paginate(paginationNumber());

        return view('managers.views.settings.certifications.index')->with([
            'certifications' => $certifications,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create(): View
    {

        $availables = $this->availableOptions();

        return view('managers.views.settings.certifications.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack): View
    {

        $certification = Certification::slack($slack);

        $availables = $this->availableOptions();

        $thumbnail = $certification->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';

        return view('managers.views.settings.certifications.edit')->with([
            'certification' => $certification,
            'thumbnail' => $thumbnail,
            'availables' => $availables,
        ]);

    }

    public function update(UpdateCertificationRequest $request): JsonResponse
    {
        abort_unless(auth()->user()->can('certifications.update'), 403);

        $certification = Certification::slack($request->slack);
        $certification->title = Str::upper($request->title);
        $certification->slug = Str::slug($request->title, '-');
        $certification->description = $request->description;
        $certification->available = $request->available;
        $certification->update();

        return response()->json([
            'success' => true,
            'slack' => $certification->slack,
            'message' => 'Se actualizo el certificado correctamente',
        ]);

    }

    public function store(StoreCertificationRequest $request): JsonResponse
    {
        abort_unless(auth()->user()->can('certifications.create'), 403);

        $certification = new Certification;
        $certification->slack = $this->generate_slack('certifications');
        $certification->title = Str::upper($request->title);
        $certification->slug = Str::slug($request->title, '-');
        $certification->description = $request->description;
        $certification->available = $request->available;
        $certification->save();

        return response()->json([
            'success' => true,
            'slack' => $certification->slack,
            'message' => 'Se creo el certificado correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('certifications.delete'), 403);

        $certification = Certification::slack($slack);
        $certification->delete();

        return redirect()->route('manager.certifications');

    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:certifications,id'],
        ]);

        $permission = $request->action === 'delete' ? 'certifications.delete' : 'certifications.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = Certification::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' certificado(s) procesados.']);
    }

    public function getThumbnails($slack)
    {

        $certification = Certification::slack($slack);

        if ($certification->getMedia('thumbnail')->count() > 0) {

            $thumbnails = $certification->getMedia('thumbnail');

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
        abort_unless(auth()->user()->can('certifications.update'), 403);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $certification = Certification::slack($request->certification);
            $certification->addMediaFromRequest('file')->toMediaCollection('thumbnail');

            return response()->json(['status' => 'success', 'certification' => $certification->slack]);

        }

    }

    public function deleteThumbnails($id)
    {
        abort_unless(auth()->user()->can('certifications.delete'), 403);

        Media::where('id', $id)
            ->where('model_type', Certification::class)
            ->where('collection_name', 'thumbnail')
            ->first()?->delete();

        return response()->json(['status' => 'success']);
    }
}
