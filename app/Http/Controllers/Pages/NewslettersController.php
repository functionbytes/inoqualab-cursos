<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Newsletter\PublicSubscribeRequest;
use App\Mail\Newsletter\UnsubscribedMail;
use App\Models\Newsletter;
use App\Services\NewsletterMailjetService;
use App\Services\NewsletterService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class NewslettersController extends Controller
{
    public function __construct(
        private readonly NewsletterService $service,
        private readonly NewsletterMailjetService $mailjet,
    ) {}

    public function store(PublicSubscribeRequest $request): JsonResponse
    {
        if (setting('newsletter_enabled') === '0') {
            return response()->json(['success' => false, 'message' => 'Las suscripciones están desactivadas.'], 422);
        }

        try {
            $subscriber = $this->service->subscribe(
                $request->validated('email'),
                $request->validated('name'),
                $request->ip()
            );

            $message = (! $subscriber->is_active)
                ? 'Te hemos enviado un correo de confirmación. Revisa tu bandeja de entrada.'
                : 'Te has suscrito correctamente.';

            return response()->json(['success' => true, 'message' => $message]);
        } catch (QueryException) {
            return response()->json(['success' => true, 'message' => 'Ya estás suscrito.']);
        }
    }

    public function confirm(string $token): RedirectResponse
    {
        $subscriber = $this->service->confirmSubscription($token);

        if (! $subscriber) {
            return redirect()->route('home')->with('info', 'Este enlace de confirmación ya no es válido o ha expirado.');
        }

        return redirect()->route('home')->with('success', '¡Tu suscripción ha sido confirmada! Bienvenido al newsletter.');
    }

    public function unsubscribe(string $slack): RedirectResponse
    {
        $newsletter = Newsletter::findBySlack($slack);

        if ($newsletter && $newsletter->is_active) {
            $newsletter->unsubscribe();
            $this->mailjet->removeContact($newsletter->email);
            Mail::queue(new UnsubscribedMail($newsletter->email));
        }

        return redirect()->route('home')->with('info', 'Has sido dado de baja del newsletter correctamente.');
    }

    public function ajaxPopup(): Response
    {
        if (setting('newsletter_enabled') === '0' || setting('newsletter_popup_enabled') === '0') {
            return response('', 204);
        }

        return response()->view('pages.partials.newsletter-popup');
    }
}
