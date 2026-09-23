<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pages\StoreContactRequest;
use App\Mail\Pages\Contact\AlertsMails;
use App\Mail\Pages\Contact\ResponseMails;
use App\Models\Contact;
use App\Models\Faq\Faq;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactsController extends Controller
{
    public function index()
    {
        seo()->setTitle('Contacto')->setCanonical(url()->current());

        return view('pages.views.contacts.index')->with([
            'homeFaqs' => Faq::take(5)->get(),
        ]);
    }

    public function storage(StoreContactRequest $request)
    {
        $contact = new Contact;
        $contact->slack = $this->generate_slack('contacts');
        $contact->firstname = $request->firstname;
        $contact->lastname = $request->lastname;
        $contact->cellphone = $request->cellphone;
        $contact->email = $request->email;
        $contact->reviewed = 0;
        $contact->message = $request->message;
        $contact->save();

        if (setting('contact_notifications') == 1) {
            try {
                Mail::send(new AlertsMails($contact));
            } catch (\Throwable $e) {
                Log::warning('Fallo al enviar AlertsMails de contacto', [
                    'contact' => $contact->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            Mail::send(new ResponseMails($contact));
        } catch (\Throwable $e) {
            Log::warning('Fallo al enviar ResponseMails de contacto', [
                'contact' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'slack' => $contact->slack,
            'message' => 'Tu mensaje ha sido enviado correctamente.',
        ]);
    }
}
