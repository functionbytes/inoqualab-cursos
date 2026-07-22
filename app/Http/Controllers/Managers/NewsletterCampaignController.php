<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Newsletter\StoreNewsletterCampaignRequest;
use App\Http\Requests\Managers\Newsletter\TestNewsletterCampaignRequest;
use App\Http\Requests\Managers\Newsletter\UpdateNewsletterCampaignRequest;
use App\Jobs\Newsletter\SendNewsletterCampaignJob;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterList;
use App\Services\Mailer\MailerTemplateRendererService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsletterCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $campaigns = NewsletterCampaign::query()
            ->with('creator')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $hasSending = NewsletterCampaign::query()->where('status', 'sending')->exists();

        $stats = NewsletterCampaign::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('managers.views.newsletter.campaigns.index', compact('campaigns', 'hasSending', 'stats', 'search', 'status'));
    }

    public function create(): View
    {
        return view('managers.views.newsletter.campaigns.form', [
            'campaign' => null,
            'lists' => NewsletterList::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreNewsletterCampaignRequest $request): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.create'), 403);
        $data = $request->validated();
        $data['uid'] = Str::uuid();
        $data['created_by'] = auth()->id();

        $campaign = NewsletterCampaign::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Campaña creada correctamente.',
            'redirect' => route('manager.newsletter.campaigns.edit', $campaign),
        ], 201);
    }

    public function edit(NewsletterCampaign $campaign): View
    {
        $campaign->load('creator');

        return view('managers.views.newsletter.campaigns.form', [
            'campaign' => $campaign,
            'lists' => NewsletterList::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateNewsletterCampaignRequest $request, NewsletterCampaign $campaign): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);
        if (! $campaign->isDraft()) {
            return response()->json(['message' => 'Solo se pueden editar campañas en borrador.'], 422);
        }

        $campaign->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Campaña guardada correctamente.']);
    }

    public function destroy(NewsletterCampaign $campaign): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.delete'), 403);
        if ($campaign->isSending()) {
            return response()->json(['message' => 'No se puede eliminar una campaña en envío.'], 422);
        }

        $campaign->delete();

        return response()->json(['success' => true, 'message' => 'Campaña eliminada correctamente.']);
    }

    public function send(NewsletterCampaign $campaign): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        if (! $campaign->isDraft()) {
            return response()->json(['message' => 'Solo se pueden enviar campañas en estado borrador.'], 422);
        }

        $recipientsCount = $campaign->newsletter_list_id
            ? $campaign->list->subscribers()->where('newsletters.is_active', true)->count()
            : Newsletter::query()->subscribed()->count();

        if ($recipientsCount === 0) {
            return response()->json(['message' => 'No hay suscriptores activos para enviar la campaña.'], 422);
        }

        $campaign->update([
            'status' => 'sending',
            'recipients_count' => $recipientsCount,
            'started_at' => now(),
        ]);

        SendNewsletterCampaignJob::dispatch($campaign->fresh());

        return response()->json([
            'success' => true,
            'message' => "Campaña en envío a {$recipientsCount} suscriptores.",
        ]);
    }

    public function test(TestNewsletterCampaignRequest $request, NewsletterCampaign $campaign): JsonResponse
    {
        // Envía correo a una dirección arbitraria: sin este guard, cualquier holder
        // de rol manager sin newsletters.update podía usarlo como relay de correo.
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        $data = $request->validated();

        try {
            $variables = [
                'SITE_NAME' => config('app.name'),
                'SITE_URL' => config('app.url'),
                'SITE_LOGO_URL' => config('app.url').'/images/logo.png',
                'SUPPORT_EMAIL' => config('mail.from.address'),
                'CURRENT_YEAR' => date('Y'),
                'SUBSCRIBER_EMAIL' => $data['email'],
                'SUBSCRIBER_NAME' => ' Suscriptor',
                'UNSUBSCRIBE_URL' => config('app.url').'/newsletters/unsubscribe/test-token',
            ];

            $subject = MailerTemplateRendererService::replaceVariables($campaign->subject, $variables);
            $html = MailerTemplateRendererService::renderContentWithWrapper($campaign->content, $variables);

            Mail::html($html, fn ($msg) => $msg->to($data['email'])->subject('[PRUEBA] '.$subject));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al enviar: '.$e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => "Correo de prueba enviado a {$data['email']}"]);
    }

    public function activeCount(): JsonResponse
    {
        $count = Newsletter::query()->subscribed()->count();

        return response()->json(['count' => $count]);
    }

    public function duplicate(NewsletterCampaign $campaign): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.create'), 403);

        $copy = NewsletterCampaign::create([
            'uid' => Str::uuid(),
            'name' => 'Copia de '.$campaign->name,
            'subject' => $campaign->subject,
            'preheader' => $campaign->preheader,
            'content' => $campaign->content,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaña duplicada como borrador.',
            'redirect' => route('manager.newsletter.campaigns.edit', $copy),
        ]);
    }

    public function retry(NewsletterCampaign $campaign): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        if (! $campaign->isFailed()) {
            return response()->json(['message' => 'Solo se pueden reintentar campañas fallidas.'], 422);
        }

        $campaign->update([
            'status' => 'draft',
            'recipients_count' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'started_at' => null,
            'sent_at' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Campaña restablecida como borrador.']);
    }

    public function preview(NewsletterCampaign $campaign): Response
    {
        $variables = [
            'SITE_NAME' => config('app.name'),
            'SITE_URL' => config('app.url'),
            'SITE_LOGO_URL' => config('app.url').'/images/logo.png',
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'CURRENT_YEAR' => date('Y'),
            'SUBSCRIBER_EMAIL' => 'preview@ejemplo.com',
            'SUBSCRIBER_NAME' => ' Suscriptor',
            'UNSUBSCRIBE_URL' => config('app.url').'/newsletters/unsubscribe/preview-token',
        ];

        $html = MailerTemplateRendererService::renderContentWithWrapper($campaign->content, $variables);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
