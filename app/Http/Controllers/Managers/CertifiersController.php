<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Certifiers\StoreCertifierFileRequest;
use App\Http\Requests\Managers\Certifiers\StoreCertifierRequest;
use App\Http\Requests\Managers\Certifiers\UpdateCertifierRequest;
use App\Models\Certifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CertifiersController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;
        $certifiers = Certifier::descending();

        if ($searchKey != null) {
            $certifiers = $certifiers->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $certifiers = $certifiers->where('available', $available);
        }

        $certifiers = $certifiers->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.certifiers._table' : 'managers.views.certifiers.index';

        return view($view)->with([
            'certifiers' => $certifiers,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.certifiers.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $certifier = Certifier::slack($slack);

        $availables = $this->availableOptions();

        $thumbnail = $certifier->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';
        $signature = $certifier->getMedia('signature')->count() > 0 ? 'true' : 'false';

        return view('managers.views.certifiers.edit')->with([
            'availables' => $availables,
            'certifier' => $certifier,
            'thumbnail' => $thumbnail,
            'signature' => $signature,
        ]);

    }

    public function update(UpdateCertifierRequest $request)
    {
        $data = $request->validated();

        $certifier = Certifier::slack($data['slack']);
        $certifier->firstname = Str::upper($data['firstname']);
        $certifier->lastname = Str::upper($data['lastname']);
        $certifier->identification = $data['identification'] ?? null;
        $certifier->profession = $data['profession'] ?? null;
        $certifier->available = $data['available'];
        $certifier->update();

        return response()->json([
            'success' => true,
            'slack' => $certifier->slack,
            'message' => 'Se actualizo el certificado correctamente',
        ]);

    }

    public function store(StoreCertifierRequest $request)
    {
        abort_unless(auth()->user()->can('certifiers.create'), 403);

        $certifier = new Certifier;
        $certifier->slack = $this->generate_slack('certifiers');
        $certifier->firstname = Str::upper($request->firstname);
        $certifier->lastname = Str::upper($request->lastname);
        $certifier->profession = $request->profession;
        $certifier->identification = $request->identification;
        $certifier->description = $request->description;
        $certifier->available = $request->available;
        $certifier->save();

        return response()->json([
            'success' => true,
            'slack' => $certifier->slack,
            'message' => 'Se creo el certificado correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('certifiers.delete'), 403);

        $certifier = Certifier::slack($slack);
        $certifier->delete();

        return redirect()->route('manager.certifiers');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:publish,hide,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:certifiers,id'],
        ]);

        $permission = $request->action === 'delete' ? 'certifiers.delete' : 'certifiers.update';
        abort_unless(auth()->user()->can($permission), 403);

        $query = Certifier::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'publish' => $query->update(['available' => 1]),
            'hide' => $query->update(['available' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' capacitador(es) procesados.']);
    }

    public function getThumbnails($slack)
    {

        $certifier = Certifier::slack($slack);

        if ($certifier->getMedia('thumbnail')->count() > 0) {

            $thumbnails = $certifier->getMedia('thumbnail');

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

    public function storeThumbnails(StoreCertifierFileRequest $request)
    {
        if (! $request->hasFile('file') || ! $request->file('file')->isValid()) {
            return response()->json(['status' => 'error', 'message' => 'El archivo no es válido.'], 422);
        }

        $certifier = Certifier::slack(Str::remove('"', $request->certifier));
        $certifier->addMediaFromRequest('file')->toMediaCollection('thumbnail');

        return response()->json(['status' => 'success', 'certifier' => $certifier->slack]);
    }

    public function deleteThumbnails($id)
    {
        abort_unless(auth()->user()->can('certifiers.update'), 403);

        Media::where('id', $id)
            ->where('model_type', Certifier::class)
            ->where('collection_name', 'thumbnail')
            ->first()?->delete();

        return response()->json(['status' => 'success']);
    }

    public function getSignatures($slack)
    {

        $certifier = Certifier::slack($slack);

        if ($certifier->getMedia('signature')->count() > 0) {

            $thumbnails = $certifier->getMedia('signature');

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

    public function storeSignatures(StoreCertifierFileRequest $request)
    {
        if (! $request->hasFile('file') || ! $request->file('file')->isValid()) {
            return response()->json(['status' => 'error', 'message' => 'El archivo no es válido.'], 422);
        }

        $certifier = Certifier::slack(Str::remove('"', $request->certifier));
        $certifier->addMediaFromRequest('file')->toMediaCollection('signature');

        return response()->json(['status' => 'success', 'certifier' => $certifier->slack]);
    }

    public function deleteSignatures($id)
    {
        abort_unless(auth()->user()->can('certifiers.update'), 403);

        Media::where('id', $id)
            ->where('model_type', Certifier::class)
            ->where('collection_name', 'signature')
            ->first()?->delete();

        return response()->json(['status' => 'success']);
    }
}
