<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoRedirect;
use App\Services\RedirectChainDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SeoRedirectController extends Controller
{
    public function index(Request $request): View
    {
        $redirects = SeoRedirect::query()
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
            ->when($request->input('is_active') !== null, fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(paginationNumber(20))
            ->withQueryString();

        $view = request()->ajax() ? 'managers.views.seo.redirects._table' : 'managers.views.seo.redirects.index';

        return view($view, compact('redirects'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_path' => ['required', 'string', 'max:500'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'is_regex' => ['boolean'],
            'is_wildcard' => ['boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->guardAgainstRedirectLoop(
            $validated['source_path'],
            $validated['target_path'],
            $validated['is_regex'] ?? false,
            $validated['is_wildcard'] ?? false,
        );

        SeoRedirect::create($validated);
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirección creada correctamente.',
        ]);
    }

    public function update(Request $request, SeoRedirect $seoRedirect): JsonResponse
    {
        $validated = $request->validate([
            'source_path' => ['required', 'string', 'max:500'],
            'target_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'is_regex' => ['boolean'],
            'is_wildcard' => ['boolean'],
            'is_active' => ['boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->guardAgainstRedirectLoop(
            $validated['source_path'],
            $validated['target_path'],
            $validated['is_regex'] ?? $seoRedirect->is_regex,
            $validated['is_wildcard'] ?? $seoRedirect->is_wildcard,
            $seoRedirect->id,
        );

        $seoRedirect->update($validated);
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirección actualizada correctamente.',
        ]);
    }

    /**
     * Rechaza el guardado si el redirect apunta a sí mismo o si, combinado con los
     * redirects activos existentes, formaría un ciclo (bucle infinito de
     * redirecciones para el visitante público). Ver RedirectChainDetector.
     */
    private function guardAgainstRedirectLoop(
        string $sourcePath,
        string $targetPath,
        bool $isRegex,
        bool $isWildcard,
        ?int $ignoreId = null,
    ): void {
        $source = SeoRedirect::normalizeSourcePath($sourcePath, $isRegex, $isWildcard);
        $target = SeoRedirect::normalizeTargetPath($targetPath);

        if ((new RedirectChainDetector)->wouldCreateCycle($source, $target, $ignoreId)) {
            throw ValidationException::withMessages([
                'target_path' => 'El destino no puede apuntar a sí mismo ni crear un ciclo con otra redirección existente.',
            ]);
        }
    }

    public function destroy(SeoRedirect $seoRedirect): JsonResponse
    {
        $seoRedirect->delete();
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirección eliminada correctamente.',
        ]);
    }

    public function toggleActive(SeoRedirect $seoRedirect): JsonResponse
    {
        $seoRedirect->update(['is_active' => ! $seoRedirect->is_active]);
        SeoRedirect::clearCache();

        $state = $seoRedirect->is_active ? 'activada' : 'desactivada';

        return response()->json([
            'success' => true,
            'message' => "Redirección {$state} correctamente.",
            'is_active' => $seoRedirect->is_active,
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:seo_redirects,id'],
        ]);

        SeoRedirect::query()->whereIn('id', $request->input('ids'))->delete();
        SeoRedirect::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Redirecciones eliminadas correctamente.',
        ]);
    }

    // ── Phase 5: Análisis avanzado ────────────────────────────────────────────────

    public function test(SeoRedirect $seoRedirect): JsonResponse
    {
        try {
            $response = Http::withoutRedirecting()->timeout(5)->get(url($seoRedirect->source_path));

            return response()->json([
                'status' => $response->status(),
                'expected' => $seoRedirect->status_code,
                'matches' => $response->status() === $seoRedirect->status_code,
                'target' => $seoRedirect->target_path,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Redirect test failed', ['id' => $seoRedirect->id, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'No se pudo realizar la petición de prueba.'], 422);
        }
    }
}
