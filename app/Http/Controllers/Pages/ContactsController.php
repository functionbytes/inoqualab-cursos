<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Mail\Pages\Contact\AlertsMails;
use App\Mail\Pages\Contact\ResponseMails;
use App\Models\Contact;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactsController extends Controller
{
    public function index()
    {

        SEOMeta::setTitle(getSetting()->meta_title);
        SEOMeta::setDescription(getSetting()->meta_description);
        SEOMeta::setCanonical(getUrl());

        SEOTools::setTitle(getSetting()->meta_title);
        SEOTools::setDescription(getSetting()->meta_description);
        SEOTools::opengraph()->setUrl(getUrl());
        SEOTools::setCanonical(getUrl());
        SEOTools::opengraph()->addProperty('type', 'articles');
        SEOTools::twitter()->setSite('@bpmsandiego');
        SEOTools::jsonLd()->addImage(getMeta());

        OpenGraph::setTitle(getSetting()->meta_title);
        OpenGraph::setDescription(getSetting()->meta_description);
        OpenGraph::setUrl(getUrl());
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('locale', 'en-En');
        OpenGraph::addImage(getMeta());

        JsonLd::setTitle(getSetting()->meta_title);
        JsonLd::setDescription(getSetting()->meta_description);
        JsonLd::addImage(getMeta());

        return view('pages.views.contacts.index')->with([

        ]);

    }

    public function storage(Request $request)
    {
        $request->validate([
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'cellphone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'firstname.required' => 'El nombre es obligatorio.',
            'lastname.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'message.required' => 'El mensaje es obligatorio.',
        ]);

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
            }
        }

        try {
            Mail::send(new ResponseMails($contact));
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => true,
            'slack' => $contact->slack,
            'message' => 'Tu mensaje ha sido enviado correctamente.',
        ]);
    }
}
