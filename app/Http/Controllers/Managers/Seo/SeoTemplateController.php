<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoMeta;
use App\Models\Seo\SeoTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoTemplateController extends Controller
{
    public function index(): View
    {
        $templates = SeoTemplate::query()->orderByDesc('priority')->orderBy('name')->paginate(20);

        $stats = [
            'total' => SeoTemplate::count(),
            'active' => SeoTemplate::active()->count(),
        ];

        return view('managers.views.seo.templates.index', compact('templates', 'stats'));
    }

    public function create(): View
    {
        return view('managers.views.seo.templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model_type' => ['nullable', 'string', 'max:255'],
            'title_pattern' => ['nullable', 'string', 'max:200'],
            'description_pattern' => ['nullable', 'string', 'max:500'],
            'og_type' => ['nullable', 'string', 'max:50'],
            'twitter_card' => ['nullable', 'string', 'max:50'],
            'robots' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0', 'max:255'],
        ]);

        SeoTemplate::create($this->withoutNullNotNullableColumns($validated));

        return redirect()
            ->route('manager.seo.templates.index')
            ->with('success', 'Plantilla SEO creada correctamente.');
    }

    public function edit(SeoTemplate $seoTemplate): View
    {
        return view('managers.views.seo.templates.edit', compact('seoTemplate'));
    }

    public function update(Request $request, SeoTemplate $seoTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model_type' => ['nullable', 'string', 'max:255'],
            'title_pattern' => ['nullable', 'string', 'max:200'],
            'description_pattern' => ['nullable', 'string', 'max:500'],
            'og_type' => ['nullable', 'string', 'max:50'],
            'twitter_card' => ['nullable', 'string', 'max:50'],
            'robots' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0', 'max:255'],
        ]);

        $seoTemplate->update($this->withoutNullNotNullableColumns($validated));

        return redirect()
            ->route('manager.seo.templates.index')
            ->with('success', 'Plantilla SEO actualizada correctamente.');
    }

    /**
     * og_type/twitter_card/robots son NOT NULL con default en la BD (migración
     * 2026_06_10_100003). El select "Sin definir" del formulario envía value="",
     * y el middleware ConvertEmptyStringsToNull lo convierte a null antes de
     * llegar aquí -- como la regla es 'nullable', pasa la validación, pero
     * Eloquent inserta un NULL explícito y MySQL lo rechaza (constraint NOT
     * NULL), 500 garantizado en cualquier creación/edición que deje alguno de
     * estos 3 selects en "Sin definir". Se quitan del array para que la BD
     * aplique su propio default en vez de forzar NULL.
     */
    private function withoutNullNotNullableColumns(array $validated): array
    {
        foreach (['og_type', 'twitter_card', 'robots'] as $column) {
            if (array_key_exists($column, $validated) && $validated[$column] === null) {
                unset($validated[$column]);
            }
        }

        return $validated;
    }

    public function destroy(SeoTemplate $seoTemplate): RedirectResponse
    {
        $seoTemplate->delete();

        return back()->with('success', 'Plantilla SEO eliminada correctamente.');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        match ($validated['action']) {
            'activate' => SeoTemplate::whereIn('id', $validated['ids'])->update(['is_active' => true]),
            'deactivate' => SeoTemplate::whereIn('id', $validated['ids'])->update(['is_active' => false]),
            'delete' => SeoTemplate::query()->whereIn('id', $validated['ids'])->delete(),
        };

        return response()->json(['success' => true, 'count' => count($validated['ids'])]);
    }

    public function toggleActive(SeoTemplate $seoTemplate): JsonResponse
    {
        $seoTemplate->update(['is_active' => ! $seoTemplate->is_active]);

        return response()->json(['status' => true, 'is_active' => $seoTemplate->is_active]);
    }

    public function preview(SeoTemplate $seoTemplate): JsonResponse
    {
        $base = SeoMeta::query();

        if ($seoTemplate->model_type) {
            $base->where('seoable_type', $seoTemplate->model_type);
        }

        $affectedCount = (clone $base)->where(function ($q) {
            $q->whereNull('title')->orWhere('title', '');
        })->count();

        $descCount = (clone $base)->where(function ($q) {
            $q->whereNull('description')->orWhere('description', '');
        })->count();

        return response()->json([
            'affected_count' => $affectedCount,
            'desc_count' => $descCount,
            'template_name' => $seoTemplate->name,
        ]);
    }

    public function bulkApply(SeoTemplate $seoTemplate): JsonResponse
    {
        if (! $seoTemplate->is_active) {
            return response()->json(['status' => false, 'message' => 'La plantilla no está activa'], 422);
        }

        $query = SeoMeta::query();

        if ($seoTemplate->model_type) {
            $query->where('seoable_type', $seoTemplate->model_type);
        }

        $updated = 0;

        $query->with('seoable')->each(function (SeoMeta $meta) use ($seoTemplate, &$updated) {
            if (! $meta->seoable) {
                return;
            }

            $applied = $seoTemplate->applyToModel($meta->seoable);
            $fillable = [];

            foreach ($applied as $field => $value) {
                if (empty($meta->$field)) {
                    $fillable[$field] = $value;
                }
            }

            if (! empty($fillable)) {
                $meta->update($fillable);
                $updated++;
            }
        });

        return response()->json([
            'status' => true,
            'message' => "Plantilla aplicada a {$updated} registros SEO.",
            'updated' => $updated,
        ]);
    }
}
