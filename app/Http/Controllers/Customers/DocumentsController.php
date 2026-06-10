<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $documents = Document::latest()->available();

        if ($searchKey != null) {
            $documents = $documents->where('title', 'like', '%'.$searchKey.'%');
        }

        $documents = $documents->paginate(paginationNumber());

        return view('customers.views.documents.index')->with([
            'documents' => $documents,
            'searchKey' => $searchKey,
        ]);

    }
}
