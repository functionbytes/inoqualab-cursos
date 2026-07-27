<?php

namespace App\Http\Controllers\Managers\Mailer;

use App\Enums\EndpointLogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Mailer\StoreMailerEndpointRequest;
use App\Http\Requests\Managers\Mailer\UpdateMailerEndpointRequest;
use App\Jobs\Mailer\SendEndpointEmailJob;
use App\Models\Mailer\MailerEndpoint;
use App\Models\Mailer\MailerEndpointLog;
use App\Models\Mailer\MailerTemplate;
use App\Traits\BuildsJsonResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MailerEndpointController extends Controller
{
    use BuildsJsonResponses;

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = MailerEndpoint::withTrashed(false)->withCount('successLogs')
            ->with('template')
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            });
        }
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->inactive();
        }

        $endpoints = $query->paginate(20);

        $stats = [
            'total' => MailerEndpoint::count(),
            'active' => MailerEndpoint::active()->count(),
            'inactive' => MailerEndpoint::inactive()->count(),
            'total_requests' => MailerEndpoint::sum('requests_count'),
        ];

        $sources = ['api', 'internal', 'webhook'];

        return view('managers.views.mailer.endpoints.index', compact('endpoints', 'stats', 'search', 'status', 'sources'));
    }

    public function create(): View
    {
        $templates = MailerTemplate::enabled()->orderBy('name')->get();

        return view('managers.views.mailer.endpoints.create', compact('templates'));
    }

    public function store(StoreMailerEndpointRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $reserved = config('mailer-module.reserved_slugs', ['send', 'info', 'status', 'logs']);
        if (in_array($validated['slug'], $reserved)) {
            return back()->withInput()->with('error', "El slug '{$validated['slug']}' está reservado.");
        }

        $validated['is_active'] = $request->has('is_active');
        $endpoint = MailerEndpoint::create($validated);

        return redirect()->route('mailers.endpoints.edit', $endpoint->id)
            ->with('success', "Endpoint '{$endpoint->name}' creado.");
    }

    public function edit(MailerEndpoint $endpoint): View
    {
        $templates = MailerTemplate::enabled()->orderBy('name')->get();
        $recentLogs = $endpoint->logs()->latest()->take(5)->get();

        $stats = [
            'total' => $endpoint->requests_count,
            'success' => $endpoint->successLogs()->count(),
            'failed' => $endpoint->failedLogs()->count(),
            'last_24h' => $endpoint->logs()->where('created_at', '>=', now()->subDay())->count(),
        ];

        return view('managers.views.mailer.endpoints.edit', compact('endpoint', 'templates', 'recentLogs', 'stats'));
    }

    public function update(UpdateMailerEndpointRequest $request, MailerEndpoint $endpoint): RedirectResponse
    {
        $validated = $request->validated();

        $validated['is_active'] = $request->has('is_active');
        $endpoint->update($validated);

        return redirect()->route('mailers.endpoints.edit', $endpoint->id)
            ->with('success', 'Endpoint actualizado.');
    }

    public function destroy(MailerEndpoint $endpoint): RedirectResponse
    {
        abort_unless(auth()->user()->can('newsletters.delete'), 403);

        $name = $endpoint->name;
        $endpoint->delete();

        return redirect()->route('mailers.endpoints.index')
            ->with('success', "Endpoint '{$name}' eliminado.");
    }

    public function logs(Request $request, MailerEndpoint $endpoint): View
    {
        $searchEmail = $request->input('email');
        $filterStatus = $request->input('status');
        $period = $request->input('period');

        $query = $endpoint->logs()->latest();

        if ($searchEmail) {
            $query->searchEmail($searchEmail);
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        if ($period) {
            $query->period($period);
        }

        $logs = $query->paginate(30);

        $stats = [
            'total' => $endpoint->logs()->count(),
            'success' => $endpoint->successLogs()->count(),
            'failed' => $endpoint->failedLogs()->count(),
            'success_rate' => $endpoint->successRate(),
        ];

        return view('managers.views.mailer.endpoints.logs', compact('endpoint', 'logs', 'stats', 'searchEmail', 'filterStatus', 'period'));
    }

    public function regenerateToken(MailerEndpoint $endpoint): RedirectResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        $endpoint->update(['api_token' => MailerEndpoint::generateToken()]);

        return back()->with('success', 'Token regenerado exitosamente.');
    }

    public function documentation(): View
    {
        $endpoints = MailerEndpoint::active()->with('template')->orderBy('name')->get();
        $appUrl = rtrim(config('app.url'), '/');

        return view('managers.views.mailer.endpoints.documentation', compact('endpoints', 'appUrl'));
    }

    // ─── API pública ──────────────────────────────────────────────────────────

    public function send(Request $request, string $slug): JsonResponse
    {
        $endpoint = MailerEndpoint::where('slug', $slug)->active()->first();

        if (! $endpoint) {
            return $this->jsonError('not_found', 'Endpoint not found or inactive', 404);
        }

        $token = $request->bearerToken() ?? $request->header('X-API-Token') ?? $request->input('api_token');

        // Comparación en tiempo constante para evitar timing attacks sobre el token.
        if (! hash_equals((string) $endpoint->api_token, (string) $token)) {
            return $this->jsonError('unauthorized', 'Invalid API token', 401);
        }

        $payload = $request->all();
        $payloadSize = strlen(json_encode($payload));

        if ($payloadSize > config('mailer-module.limits.payload_max_bytes', 262144)) {
            return $this->jsonError('payload_too_large', 'Payload exceeds maximum size', 413);
        }

        $requiredVars = $endpoint->required_variables ?? [];
        foreach ($requiredVars as $var) {
            if (! array_key_exists($var, $payload)) {
                return $this->jsonError('missing_variable', "Required variable '{$var}' is missing", 422);
            }
        }

        $recipient = $payload['email'] ?? $payload['recipient_email'] ?? null;

        if ($recipient === null) {
            return $this->jsonError('missing_email', 'Recipient email is required (field: email or recipient_email)', 422);
        }

        // El formato se validaba solo dentro del job: la API respondía 202
        // "encolado" y el envío moría después en silencio, así que quien
        // integraba veía un éxito que nunca llegaba a destino.
        if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('invalid_email', 'Recipient email is not a valid address', 422);
        }

        try {
            $log = MailerEndpointLog::create([
                'mailer_endpoint_id' => $endpoint->id,
                'payload' => $payload,
                'status' => EndpointLogStatus::Pending,
            ]);

            SendEndpointEmailJob::dispatch($endpoint->id, $payload, $log->id);

            return $this->jsonSuccess(['log_id' => $log->id, 'queued' => true], 202, [
                'message' => 'Email queued successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Endpoint send failed', ['slug' => $slug, 'error' => $e->getMessage()]);

            return $this->jsonError('internal_error', 'Failed to queue email', 500);
        }
    }

    public function info(string $slug): JsonResponse
    {
        $endpoint = MailerEndpoint::where('slug', $slug)->active()->first();

        if (! $endpoint) {
            return $this->jsonError('not_found', 'Endpoint not found', 404);
        }

        return $this->jsonSuccess([
            'slug' => $endpoint->slug,
            'name' => $endpoint->name,
            'type' => $endpoint->type,
            'source' => $endpoint->source,
            'expected_variables' => $endpoint->expected_variables ?? [],
            'required_variables' => $endpoint->required_variables ?? [],
            'is_active' => $endpoint->is_active,
        ]);
    }

    public function status(string $slug): JsonResponse
    {
        $endpoint = MailerEndpoint::where('slug', $slug)->active()->first();

        if (! $endpoint) {
            return $this->jsonError('not_found', 'Endpoint not found', 404);
        }

        return $this->jsonSuccess([
            'slug' => $endpoint->slug,
            'requests_count' => $endpoint->requests_count,
            'success_rate' => $endpoint->successRate(),
            'last_request_at' => $endpoint->last_request_at?->toIso8601String(),
        ]);
    }
}
