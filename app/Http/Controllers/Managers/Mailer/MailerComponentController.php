<?php

namespace App\Http\Controllers\Managers\Mailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Mailer\StoreMailerComponentRequest;
use App\Http\Requests\Managers\Mailer\UpdateMailerComponentRequest;
use App\Models\Mailer\MailerLayout;
use App\Services\Mailer\MailerTemplateRendererService;
use App\Services\Mailer\MailerVariableService;
use App\Traits\BuildsJsonResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailerComponentController extends Controller
{
    use BuildsJsonResponses;

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');

        $query = MailerLayout::query()->orderBy('alias');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%");
            });
        }
        if ($type) {
            $query->where('type', $type);
        }

        $components = $query->paginate(20);

        return view('managers.views.mailer.components.index', compact('components', 'search', 'type'));
    }

    public function create(): View
    {
        return view('managers.views.mailer.components.create');
    }

    public function store(StoreMailerComponentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['is_enabled'] = $request->has('is_enabled');
        $validated['is_protected'] = false;

        $component = MailerLayout::create($validated);

        return redirect()->route('mailers.components.edit', $component->uid)
            ->with('success', "Componente '{$component->name}' creado exitosamente.");
    }

    public function edit(string $uid): View
    {
        $component = MailerLayout::where('uid', $uid)->firstOrFail();

        return view('managers.views.mailer.components.edit', compact('component'));
    }

    public function update(UpdateMailerComponentRequest $request, string $uid): RedirectResponse
    {
        $component = MailerLayout::where('uid', $uid)->firstOrFail();

        $validated = $request->validated();
        $validated['is_enabled'] = $request->has('is_enabled');

        if (! $component->is_protected) {
            $validated['is_protected'] = $request->has('is_protected');
        } else {
            unset($validated['is_protected']);
        }

        $component->update($validated);

        return redirect()->route('mailers.components.edit', $component->uid)
            ->with('success', "Componente '{$component->name}' actualizado.");
    }

    public function destroy(string $uid): RedirectResponse
    {
        abort_unless(auth()->user()->can('newsletters.delete'), 403);

        $component = MailerLayout::where('uid', $uid)->firstOrFail();

        if ($component->is_protected) {
            return back()->with('error', 'No se puede eliminar un componente protegido.');
        }

        $name = $component->name;
        $component->delete();

        return redirect()->route('mailers.components.index')
            ->with('success', "Componente '{$name}' eliminado.");
    }

    public function preview(string $uid)
    {
        $component = MailerLayout::where('uid', $uid)->firstOrFail();
        $content = $component->content ?? '';

        return response($content)->header('Content-Type', 'text/html');
    }

    public function previewAjax(Request $request, string $uid): JsonResponse
    {
        // findOrFail() fuera del try/catch, igual que duplicate() más abajo:
        // si el uid no existe debe ser un 404 normal, no un 500.
        $component = MailerLayout::where('uid', $uid)->firstOrFail();

        try {
            $content = $request->input('content', $component->content ?? '');
            $html = MailerTemplateRendererService::replaceVariables($content, []);

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'html' => '<div class="text-danger p-3">Error al generar preview.</div>'], 500);
        }
    }

    public function duplicate(string $uid): RedirectResponse
    {
        abort_unless(auth()->user()->can('newsletters.create'), 403);

        $original = MailerLayout::where('uid', $uid)->firstOrFail();

        try {
            $copy = $original->replicate();
            $copy->name = $original->name.' (copia)';
            $copy->alias = $original->alias.'_copy_'.time();
            $copy->is_protected = false;
            $copy->save();

            return redirect()->route('mailers.components.edit', $copy->uid)
                ->with('success', 'Componente duplicado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al duplicar el componente.');
        }
    }

    public function toggleStatus(string $uid): RedirectResponse
    {
        $component = MailerLayout::where('uid', $uid)->firstOrFail();
        $component->is_enabled = ! $component->is_enabled;
        $component->save();

        $status = $component->is_enabled ? 'habilitado' : 'deshabilitado';

        return back()->with('success', "Componente '{$component->name}' {$status}.");
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:enable,disable,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:mailer_layouts,id'],
        ]);

        $permission = $request->action === 'delete' ? 'newsletters.delete' : 'newsletters.update';
        abort_unless(auth()->user()->can($permission), 403);

        // Los componentes protegidos (header/footer/wrapper) nunca se pueden
        // eliminar, igual que destroy() — se excluyen en vez de romper el lote.
        $query = MailerLayout::whereIn('id', $request->ids);
        if ($request->action === 'delete') {
            $query->where('is_protected', false);
        }
        $count = $query->count();

        match ($request->action) {
            'enable' => $query->update(['is_enabled' => true]),
            'disable' => $query->update(['is_enabled' => false]),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' componente(s) procesados.']);
    }

    public function variables(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'variables' => MailerVariableService::getGroupedForModule('core'),
        ]);
    }
}
