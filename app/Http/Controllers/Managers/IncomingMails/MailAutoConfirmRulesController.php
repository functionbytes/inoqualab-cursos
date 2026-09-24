<?php

namespace App\Http\Controllers\Managers\IncomingMails;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\IncomingMails\BulkActionMailAutoConfirmRuleRequest;
use App\Models\Enterprise\Enterprise;
use App\Models\Mail\MailAutoConfirmRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailAutoConfirmRulesController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $confidence = $request->get('confidence', '');

        $rules = MailAutoConfirmRule::query()
            ->with('enterprise')
            ->when($search !== '', fn ($query) => $query->whereHas(
                'enterprise',
                fn ($enterprise) => $enterprise->where('title', 'like', "%{$search}%")
            ))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === '1'))
            ->when($confidence === 'high', fn ($query) => $query->where('min_confidence', '>=', 90))
            ->when($confidence === 'medium', fn ($query) => $query->whereBetween('min_confidence', [70, 89]))
            ->when($confidence === 'low', fn ($query) => $query->where('min_confidence', '<', 70))
            ->orderByDesc('created_at')
            ->paginate(paginationNumber(10))
            ->withQueryString();

        $statsRaw = MailAutoConfirmRule::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive')
            ->selectRaw('AVG(min_confidence) as average')
            ->first();

        $stats = [
            'total' => (int) $statsRaw->total,
            'active' => (int) $statsRaw->active,
            'inactive' => (int) $statsRaw->inactive,
            'average' => (int) round((float) $statsRaw->average),
        ];

        $view = $request->ajax() ? 'managers.views.mails.settings._table' : 'managers.views.mails.settings.index';

        return view($view, compact('rules', 'stats', 'search', 'status', 'confidence'));
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('incoming-mails.create'), 403);
        $enterpriseId = $request->input('enterprise_id');
        $minConfidence = (int) $request->input('min_confidence', 90);

        if (! $enterpriseId || $minConfidence < 1 || $minConfidence > 100) {
            return response()->json(['success' => false, 'message' => 'Parámetros inválidos.']);
        }

        $enterprise = Enterprise::id($enterpriseId);

        if (! $enterprise instanceof Enterprise) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada.']);
        }

        $exists = MailAutoConfirmRule::query()->where('enterprise_id', $enterprise->id)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Ya existe una regla para esta empresa.']);
        }

        $rule = MailAutoConfirmRule::create([
            'enterprise_id' => $enterprise->id,
            'min_confidence' => $minConfidence,
            'is_active' => true,
        ]);

        $rule->load('enterprise');

        return response()->json([
            'success' => true,
            'message' => "Regla creada para {$enterprise->title}.",
            'rule' => [
                'id' => $rule->id,
                'enterprise_title' => $rule->enterprise->title,
                'min_confidence' => $rule->min_confidence,
                'is_active' => $rule->is_active,
            ],
        ]);
    }

    // Route model binding en vez de (int $id): un {id} no numérico (URL
    // manipulada a mano, bug de JS) tiraba TypeError/500 en vez de un 404 limpio.
    public function toggle(MailAutoConfirmRule $rule): JsonResponse
    {
        $rule->is_active = ! $rule->is_active;
        $rule->save();

        return response()->json([
            'success' => true,
            'is_active' => $rule->is_active,
            'message' => $rule->is_active ? 'Regla activada.' : 'Regla desactivada.',
        ]);
    }

    public function destroy(MailAutoConfirmRule $rule): JsonResponse
    {
        abort_unless(auth()->user()->can('incoming-mails.delete'), 403);
        $rule->delete();

        return response()->json(['success' => true, 'message' => 'Regla eliminada.']);
    }

    public function bulkAction(BulkActionMailAutoConfirmRuleRequest $request): JsonResponse
    {
        $query = MailAutoConfirmRule::query()->whereIn('id', $request->validated('ids'));
        $count = $query->count();

        match ($request->validated('action')) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'count' => $count]);
    }
}
