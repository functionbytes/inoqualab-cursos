<?php

namespace App\Http\Controllers\Supports\Contacts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supports\BulkActionContactRequest;
use App\Http\Requests\Supports\UpdateContactRequest;
use App\Models\Contact;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    public function index(Request $request)
    {
        $searchKey = $request->search;
        $reviewed = $request->reviewed;

        $contacts = Contact::orderBy('created_at', 'desc');

        if ($searchKey != null) {
            $contacts = $contacts->where(function ($query) use ($searchKey) {
                $query->where('firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(firstname, ' ', lastname)"), 'like', '%'.$searchKey.'%');
            });
        }

        if ($reviewed != null) {
            $contacts = $contacts->where('reviewed', $reviewed);
        }

        $contacts = $contacts->paginate(paginationNumber());

        // Total + desglose por estado en 1 sola query de agregación.
        $agg = Contact::query()->selectRaw(
            'COUNT(*) total,
             SUM(reviewed = 1) reviewed,
             SUM(reviewed = 0) pending'
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'reviewed' => (int) $agg->reviewed,
            'pending' => (int) $agg->pending,
        ];

        $view = $request->ajax() ? 'supports.views.contacts._table' : 'supports.views.contacts.index';

        return view($view)->with([
            'contacts' => $contacts,
            'reviewed' => $reviewed,
            'searchKey' => $searchKey,
            'stats' => $stats,
        ]);
    }

    public function edit($slack)
    {

        $contact = Contact::slack($slack);

        $revieweds = collect([
            ['id' => '1', 'label' => 'Gestionado'],
            ['id' => '0', 'label' => 'Pendiente'],
        ]);

        $revieweds = $revieweds->pluck('label', 'id');

        return view('supports.views.contacts.edit')->with([
            'contact' => $contact,
            'revieweds' => $revieweds,
        ]);

    }

    public function update(UpdateContactRequest $request)
    {

        $contact = Contact::slack($request->slack);

        if (! $contact) {
            return response()->json(['success' => false, 'message' => 'Contacto no encontrado.']);
        }

        $contact->reviewed = $request->reviewed ? 1 : 0;
        $contact->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó correctamente',
        ]);

    }

    public function view($slack)
    {

        $contact = Contact::slack($slack);

        $revieweds = collect([
            ['id' => '1', 'label' => 'Gestionado'],
            ['id' => '0', 'label' => 'Pendiente'],
        ]);

        $revieweds = $revieweds->pluck('label', 'id');

        return view('supports.views.contacts.view')->with([
            'contact' => $contact,
            'revieweds' => $revieweds,
        ]);

    }

    public function destroy($slack)
    {
        $contact = Contact::slack($slack);
        $contact->delete();

        return redirect()->route('support.contacts');
    }

    public function bulkAction(BulkActionContactRequest $request): JsonResponse
    {
        $query = Contact::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' contacto(s) procesados.']);
    }
}
