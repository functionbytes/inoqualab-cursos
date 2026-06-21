<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;
        $documents = Document::descending();

        if ($searchKey != null) {
            $documents = $documents->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $documents = $documents->where('available', $available);
        }

        $documents = $documents->paginate(paginationNumber());

        return view('managers.views.settings.documents.index')->with([
            'documents' => $documents,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {
        $availables = $this->availableOptions();

        return view('managers.views.settings.documents.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $document = Document::slack($slack);

        $availables = $this->availableOptions();

        $file = $document->getMedia('files')->count() > 0 ? 'true' : 'false';

        return view('managers.views.settings.documents.edit')->with([
            'availables' => $availables,
            'document' => $document,
            'file' => $file,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('documents.update'), 403);

        $document = Document::slack($request->slack);
        $document->title = Str::upper($request->title);
        $document->description = $request->description;
        $document->available = $request->available;
        $document->update();

        return response()->json([
            'success' => true,
            'slack' => $document->slack,
            'message' => 'Se actualizo el documento correctamente',
        ]);

    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('documents.create'), 403);

        $document = new Document;
        $document->slack = $this->generate_slack('trusteds');
        $document->title = Str::upper($request->title);
        $document->description = $request->description;
        $document->available = 1;
        $document->save();

        return response()->json([
            'success' => true,
            'slack' => $document->slack,
            'message' => 'Se creo el documento correctamente',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('documents.delete'), 403);

        $document = Document::slack($slack);
        $document->delete();

        return redirect()->route('manager.documents');
    }

    public function getFiles($slack)
    {

        $document = Document::slack($slack);

        if ($document->getMedia('files')->count() > 0) {

            $files = $document->getMedia('files');

            foreach ($files as $thumbnail) {

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

    public function storeFiles(Request $request)
    {

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $document = Document::slack($request->documents);
            $document->addMediaFromRequest('file')->toMediaCollection('files');

            return response()->json(['status' => 'success', 'document' => $document->slack]);
        }

    }

    public function deleteFiles($id)
    {
        Media::find($id)->delete();

        return response()->json(['status' => 'success']);
    }
}
