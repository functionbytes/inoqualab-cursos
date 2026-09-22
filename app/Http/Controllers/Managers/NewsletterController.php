<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Newsletter\BulkActionSubscriberRequest;
use App\Http\Requests\Managers\Newsletter\ImportSubscribersRequest;
use App\Http\Requests\Managers\Newsletter\StoreSubscriberRequest;
use App\Models\Newsletter;
use App\Services\NewsletterMailjetService;
use App\Services\NewsletterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function __construct(
        private readonly NewsletterService $service,
        private readonly NewsletterMailjetService $mailjet,
    ) {}

    public function store(StoreSubscriberRequest $request): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.create'), 403);
        $result = $this->service->addManual(
            $request->input('email'),
            $request->input('name')
        );

        Cache::forget('newsletter:subscriber_stats');

        return response()->json(['success' => true, 'message' => $result['message']]);
    }

    public function import(ImportSubscribersRequest $request): JsonResponse
    {
        $result = $this->service->importCsv($request->file('file'));

        Cache::forget('newsletter:subscriber_stats');

        return response()->json(['success' => true] + $result);
    }

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $source = $request->query('source');
        $status = $request->query('status');

        $subscribers = Newsletter::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            }))
            ->when($source !== null && $source !== '', fn ($q) => $q->where('source', $source))
            ->when($status === 'active', fn ($q) => $q->subscribed())
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false)->whereNull('confirmation_token'))
            ->when($status === 'pending', fn ($q) => $q->where('is_active', false)->whereNotNull('confirmation_token'))
            ->latest()
            ->paginate(paginationNumber(25))
            ->withQueryString();

        $stats = Cache::remember('newsletter:subscriber_stats', 300, function () {
            $row = Newsletter::query()
                ->selectRaw(
                    'COUNT(*) as total,
                     SUM(is_active = 1) as active,
                     SUM(is_active = 0 AND confirmation_token IS NULL) as inactive,
                     SUM(is_active = 0 AND confirmation_token IS NOT NULL) as pending,
                     SUM(user_id IS NOT NULL) as platform,
                     SUM(MONTH(created_at) = ? AND YEAR(created_at) = ?) as month',
                    [now()->month, now()->year]
                )
                ->first();

            return [
                'total' => (int) $row->total,
                'active' => (int) $row->active,
                'inactive' => (int) $row->inactive,
                'pending' => (int) $row->pending,
                'platform' => (int) $row->platform,
                'month' => (int) $row->month,
            ];
        });

        $view = request()->ajax() ? 'managers.views.newsletter._table' : 'managers.views.newsletter.index';

        return view($view, compact(
            'subscribers', 'stats', 'search', 'source', 'status'
        ));
    }

    public function toggle(Newsletter $newsletter): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        if ($newsletter->is_active) {
            $newsletter->unsubscribe();
            $this->mailjet->removeContact($newsletter->email);
            $label = 'dado de baja';
        } else {
            $newsletter->subscribe();
            $this->mailjet->addContact($newsletter->email, $newsletter->name);
            $label = 'activado';
        }

        Cache::forget('newsletter:subscriber_stats');

        return response()->json(['message' => "Suscriptor {$label} correctamente."]);
    }

    public function resendConfirmation(Newsletter $newsletter): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        $result = $this->service->resendConfirmation($newsletter);

        if (! $result) {
            return response()->json(['message' => 'El suscriptor no está pendiente de confirmación.'], 422);
        }

        return response()->json(['message' => 'Correo de confirmación reenviado correctamente.']);
    }

    public function bulkAction(BulkActionSubscriberRequest $request): JsonResponse
    {
        $data = $request->validated();

        $count = match ($data['action']) {
            'delete' => $this->service->bulkDelete($data['ids']),
            'unsubscribe' => $this->service->bulkUnsubscribe($data['ids']),
            'resubscribe' => $this->service->bulkResubscribe($data['ids']),
        };

        Cache::forget('newsletter:subscriber_stats');

        $messages = [
            'delete' => "{$count} suscriptor(es) eliminado(s) correctamente.",
            'unsubscribe' => "{$count} suscriptor(es) dado(s) de baja correctamente.",
            'resubscribe' => "{$count} suscriptor(es) reactivado(s) correctamente.",
        ];

        return response()->json(['message' => $messages[$data['action']], 'count' => $count]);
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->service->exportCsv($request);
    }

    public function destroy(Newsletter $newsletter): RedirectResponse
    {
        abort_unless(auth()->user()->can('newsletters.delete'), 403);
        $this->mailjet->removeContact($newsletter->email);
        $newsletter->delete();

        Cache::forget('newsletter:subscriber_stats');

        return redirect()->back()->with('success', 'Suscriptor eliminado correctamente.');
    }
}
