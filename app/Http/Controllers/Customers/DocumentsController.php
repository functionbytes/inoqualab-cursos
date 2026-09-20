<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentsController extends Controller
{
    /**
     * Extensión -> grupo mostrado en el filtro. No es un campo de la tabla
     * documents (no existe ninguna columna categorizable) -- sale del nombre
     * del archivo en la media asociada, así que agrupar por extensión es la
     * única categoría disponible sin agregar una migración.
     *
     * @var array<string, array<int, string>>
     */
    private const TYPE_GROUPS = [
        'pdf' => ['pdf'],
        'word' => ['doc', 'docx'],
        'excel' => ['xls', 'xlsx', 'csv'],
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ];

    public function index(Request $request): View|JsonResponse
    {

        $searchKey = $request->search;
        $type = $request->type;
        $knownExtensions = collect(self::TYPE_GROUPS)->flatten()->all();

        // Conteo por tipo sobre TODOS los documentos disponibles, no solo la
        // página cargada -- misma lógica que $counts en Certificados/Cursos.
        // Consulta liviana (solo el nombre de archivo de la media) resuelta
        // en PHP: la extensión no es una columna, así que no hay forma directa
        // de agruparla en SQL sin duplicar esta misma lista de grupos ahí.
        $allExtensions = Document::available()
            ->with(['media' => fn ($q) => $q->where('collection_name', 'files')])
            ->get()
            ->map(fn ($d) => Str::lower(pathinfo($d->getFirstMedia('files')?->file_name ?? '', PATHINFO_EXTENSION)));

        $typeCounts = ['todos' => $allExtensions->count()];
        foreach (self::TYPE_GROUPS as $key => $extensions) {
            $typeCounts[$key] = $allExtensions->filter(fn ($ext) => in_array($ext, $extensions, true))->count();
        }
        $typeCounts['other'] = $allExtensions->filter(fn ($ext) => $ext !== '' && ! in_array($ext, $knownExtensions, true))->count();

        // with('media'): index.blade.php/index-b.blade.php llaman
        // getFirstMedia('files') por cada fila del listado -- sin esto es una
        // query extra a la tabla media por documento mostrado (N+1).
        $documents = Document::with('media')->latest()->available();

        if ($searchKey != null) {
            $documents = $documents->where('title', 'like', '%'.$searchKey.'%');
        }

        if (isset(self::TYPE_GROUPS[$type])) {
            $extensions = self::TYPE_GROUPS[$type];
            $documents->whereHas('media', function ($query) use ($extensions) {
                $query->where('collection_name', 'files')->where(function ($query) use ($extensions) {
                    foreach ($extensions as $extension) {
                        $query->orWhere('file_name', 'like', "%.{$extension}");
                    }
                });
            });
        } elseif ($type === 'other') {
            $documents->where(function ($query) use ($knownExtensions) {
                $query->whereDoesntHave('media', fn ($query) => $query->where('collection_name', 'files'))
                    ->orWhereHas('media', function ($query) use ($knownExtensions) {
                        $query->where('collection_name', 'files');
                        foreach ($knownExtensions as $extension) {
                            $query->where('file_name', 'not like', "%.{$extension}");
                        }
                    });
            });
        }

        $documents = $documents->paginate(paginationNumber());

        $variant = portalVariant('customers_documents_variant');

        // El buscador, el filtro por tipo y el paginador se resuelven por
        // AJAX (ver el script en documents/index.blade.php): se devuelve solo
        // el fragmento re-renderizado en vez de la página completa.
        if ($request->ajax()) {
            return response()->json([
                'html' => view('customers.partials.views.documents.list', compact('documents', 'searchKey', 'type', 'typeCounts'))->render(),
                'total' => $documents->total(),
                'label' => Str::plural('documento', $documents->total()),
            ]);
        }

        return view('customers.views.documents.index'.$variant)->with([
            'documents' => $documents,
            'searchKey' => $searchKey,
            'type' => $type,
            'typeCounts' => $typeCounts,
        ]);

    }
}
