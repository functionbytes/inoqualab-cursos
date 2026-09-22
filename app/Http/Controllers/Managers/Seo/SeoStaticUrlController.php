<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoStaticUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeoStaticUrlController extends Controller
{
    public function index(Request $request): View
    {
        $query = SeoStaticUrl::query()
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('url', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($q) use ($request) {
                $q->where('is_active', $request->input('status') === 'active');
            });

        $total = SeoStaticUrl::query()->count();
        $totalActive = SeoStaticUrl::query()->where('is_active', true)->count();
        $totalInactive = SeoStaticUrl::query()->where('is_active', false)->count();

        $staticUrls = $query->latest()->paginate(paginationNumber(15))->withQueryString();

        $view = request()->ajax() ? 'managers.views.seo.static-urls._table' : 'managers.views.seo.static-urls.index';

        return view($view, compact('staticUrls', 'total', 'totalActive', 'totalInactive'));
    }

    public function create(): View
    {
        $changefreqOptions = SeoStaticUrl::CHANGEFREQ_OPTIONS;
        $priorityOptions = SeoStaticUrl::PRIORITY_OPTIONS;

        return view('managers.views.seo.static-urls.create', compact('changefreqOptions', 'priorityOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500', 'unique:seo_static_urls,url'],
            'priority' => ['nullable', 'numeric', 'in:'.implode(',', SeoStaticUrl::PRIORITY_OPTIONS)],
            'changefreq' => ['nullable', 'string', 'in:'.implode(',', SeoStaticUrl::CHANGEFREQ_OPTIONS)],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        SeoStaticUrl::create($validated);

        return redirect()->route('manager.seo.static-urls.index')
            ->with('success', 'URL estática creada correctamente.');
    }

    public function edit(SeoStaticUrl $seoStaticUrl): View
    {
        $changefreqOptions = SeoStaticUrl::CHANGEFREQ_OPTIONS;
        $priorityOptions = SeoStaticUrl::PRIORITY_OPTIONS;

        $staticUrl = $seoStaticUrl;

        return view('managers.views.seo.static-urls.edit', compact('staticUrl', 'changefreqOptions', 'priorityOptions'));
    }

    public function update(Request $request, SeoStaticUrl $seoStaticUrl): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500', Rule::unique('seo_static_urls', 'url')->ignore($seoStaticUrl->id)],
            'priority' => ['nullable', 'numeric', 'in:'.implode(',', SeoStaticUrl::PRIORITY_OPTIONS)],
            'changefreq' => ['nullable', 'string', 'in:'.implode(',', SeoStaticUrl::CHANGEFREQ_OPTIONS)],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $seoStaticUrl->update($validated);

        return redirect()->route('manager.seo.static-urls.index')
            ->with('success', 'URL estática actualizada correctamente.');
    }

    public function destroy(SeoStaticUrl $seoStaticUrl): JsonResponse
    {
        $seoStaticUrl->delete();

        return response()->json([
            'success' => true,
            'message' => 'URL estática eliminada correctamente.',
        ]);
    }

    public function toggleActive(SeoStaticUrl $seoStaticUrl): JsonResponse
    {
        $seoStaticUrl->update(['is_active' => ! $seoStaticUrl->is_active]);

        $state = $seoStaticUrl->is_active ? 'activada' : 'desactivada';

        return response()->json([
            'success' => true,
            'message' => "URL estática {$state} correctamente.",
            'is_active' => $seoStaticUrl->is_active,
        ]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_static_urls,id'],
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        match ($action) {
            'delete' => SeoStaticUrl::query()->whereIn('id', $ids)->delete(),
            'activate' => SeoStaticUrl::query()->whereIn('id', $ids)->update(['is_active' => true]),
            'deactivate' => SeoStaticUrl::query()->whereIn('id', $ids)->update(['is_active' => false]),
        };

        $messages = [
            'delete' => 'URLs estáticas eliminadas correctamente.',
            'activate' => 'URLs estáticas activadas correctamente.',
            'deactivate' => 'URLs estáticas desactivadas correctamente.',
        ];

        return response()->json([
            'success' => true,
            'message' => $messages[$action],
        ]);
    }
}
