<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\Contacts\BulkActionContactRequest;
use App\Http\Requests\Managers\Settings\Contacts\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('contacts.view'), 403);

        $searchKey = $request->search;
        $reviewed = $request->reviewed;
        $contacts = Contact::orderBy('created_at', 'desc')->latest();

        if ($searchKey != null) {
            $contacts->when(! strpos($searchKey, '-'), function ($query) use ($searchKey) {
                $query->where(function ($q) use ($searchKey) {
                    $q->where('contacts.firstname', 'like', '%'.$searchKey.'%')
                        ->orWhere('contacts.lastname', 'like', '%'.$searchKey.'%')
                        ->orWhere(DB::raw("CONCAT(contacts.firstname, ' ', contacts.lastname)"), 'like', '%'.$searchKey.'%');
                });
            });
        }

        if ($reviewed != null) {
            $contacts = $contacts->where('reviewed', $reviewed);
        }

        $contacts = $contacts->paginate(paginationNumber());

        $view = $request->ajax() ? 'managers.views.settings.contacts._table' : 'managers.views.settings.contacts.index';

        return view($view)->with([
            'contacts' => $contacts,
            'reviewed' => $reviewed,
            'searchKey' => $searchKey,
        ]);

    }

    public function edit($slack)
    {
        abort_unless(auth()->user()->can('contacts.update'), 403);

        $contact = Contact::slack($slack);

        $revieweds = collect([
            ['id' => '1', 'label' => 'Gestionado'],
            ['id' => '0', 'label' => 'Pendiente'],
        ]);

        $revieweds = $revieweds->pluck('label', 'id');

        return view('managers.views.settings.contacts.edit')->with([
            'contact' => $contact,
            'revieweds' => $revieweds,
        ]);

    }

    public function update(UpdateContactRequest $request)
    {
        abort_unless(auth()->user()->can('contacts.update'), 403);

        $contact = Contact::slack($request->slack);
        $contact->reviewed = $request->reviewed;
        $contact->update();

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó correctamente el formulario de contacto',
        ]);

    }

    public function destroy($slack)
    {
        abort_unless(auth()->user()->can('contacts.delete'), 403);

        $contact = Contact::slack($slack);
        $contact->delete();

        return redirect()->route('manager.contacts');
    }

    public function bulkAction(BulkActionContactRequest $request): JsonResponse
    {
        $query = Contact::whereIn('id', $request->ids);
        $count = $query->count();

        match ($request->action) {
            'reviewed' => $query->update(['reviewed' => 1]),
            'pending' => $query->update(['reviewed' => 0]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' contacto(s) procesados.']);
    }
}
