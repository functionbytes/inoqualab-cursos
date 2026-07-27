<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Certifiers\StoreCertifierFileRequest;
use App\Http\Requests\Managers\Certifiers\StoreCertifierRequest;
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

        return view('managers.views.certifiers.index')->with([
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

    public function view($slack)
    {

        $certifier = Certifier::slack($slack);

        return view('managers.views.certifiers.view')->with([
            'certifier' => $certifier,
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

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('certifiers.update'), 403);

        $certifier = Certifier::slack($request->slack);
        $certifier->firstname = Str::upper($request->firstname);
        $certifier->lastname = Str::upper($request->lastname);
        $certifier->identification = $request->identification;
        $certifier->profession = $request->profession;
        $certifier->available = $request->available;
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
