<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentsController extends Controller
{
    public function index(Request $request): View
    {

        $searchKey = $request->search;
        $documents = Document::latest()->available();

        if ($searchKey != null) {
            $documents = $documents->where('title', 'like', '%'.$searchKey.'%');
        }

        $documents = $documents->paginate(paginationNumber());

        $variant = portalVariant('customers_documents_variant');

        return view('customers.views.documents.index'.$variant)->with([
            'documents' => $documents,
            'searchKey' => $searchKey,
        ]);

    }
}
