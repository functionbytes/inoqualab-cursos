<?php

namespace App\Http\Controllers\Supports\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supports\StoreDocumentRequest;
use App\Http\Requests\Supports\UpdateDocumentRequest;
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

        return view('supports.views.documents.index')->with([
            'documents' => $documents,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);
    }

    public function create()
    {
        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.documents.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $document = Document::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        $file = $document->getMedia('files')->count() > 0 ? 'true' : 'false';

        return view('supports.views.documents.edit')->with([
            'availables' => $availables,
            'document' => $document,
            'file' => $file,
        ]);

    }

    public function update(UpdateDocumentRequest $request)
    {
        $document = Document::slack($request->slack);

        abort_unless($document instanceof Document, 404);

        $document->title = Str::upper($request->title);
        $document->description = $request->description;
        $document->available = $request->available;
        $document->save();

        return response()->json([
            'success' => true,
            'slack' => $document->slack,
            'message' => 'Se actualizó el documento correctamente.',
        ]);
    }

    public function store(StoreDocumentRequest $request)
    {
        $document = new Document;
        $document->slack = $this->generate_slack('trusteds');
        $document->title = Str::upper($request->title);
        $document->description = $request->description;
        $document->available = 1;
        $document->save();

        return response()->json([
            'success' => true,
            'slack' => $document->slack,
            'message' => 'Se creó el documento correctamente.',
        ]);
    }

    public function destroy($slack)
    {
        $document = Document::slack($slack);

        if (! $document) {
            return redirect()->route('support.documents');
        }

        $document->delete();

        return redirect()->route('support.documents');
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
