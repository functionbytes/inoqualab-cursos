<?php

namespace App\Http\Controllers\Managers\IncomingMails;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Mail\MailAutoConfirmRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MailAutoConfirmRulesController extends Controller
{
    public function index()
    {
        $rules = MailAutoConfirmRule::query()
            ->with('enterprise')
            ->orderByDesc('created_at')
            ->get();

        return view('managers.views.mails.settings', compact('rules'));
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

    public function toggle(int $id): JsonResponse
    {
        $rule = MailAutoConfirmRule::findOrFail($id);
        $rule->is_active = ! $rule->is_active;
        $rule->save();

        return response()->json([
            'success' => true,
            'is_active' => $rule->is_active,
            'message' => $rule->is_active ? 'Regla activada.' : 'Regla desactivada.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        abort_unless(auth()->user()->can('incoming-mails.delete'), 403);
        MailAutoConfirmRule::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Regla eliminada.']);
    }
}
