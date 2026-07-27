<?php

namespace App\Http\Controllers\Managers\Mailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Mailer\StoreMailerVariableRequest;
use App\Http\Requests\Managers\Mailer\UpdateMailerVariableRequest;
use App\Models\Mailer\MailerVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailerVariableController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $module = $request->input('module');
        $category = $request->input('category');

        $query = MailerVariable::query()->orderBy('module')->orderBy('category')->orderBy('key');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($module) {
            $query->where('module', $module);
        }
        if ($category) {
            $query->where('category', $category);
        }

        $variables = $query->paginate(30);
        $modules = MailerVariable::distinct('module')->pluck('module')->filter()->toArray();
        $categories = MailerVariable::distinct('category')->pluck('category')->filter()->toArray();

        return view('managers.views.mailer.variables.index', compact('variables', 'search', 'module', 'category', 'modules', 'categories'));
    }

    public function create(): View
    {
        // Las vistas create/edit recorren $categories y $modules como
        // valor => etiqueta; sin pasarlos, la pantalla moría con
        // "Undefined variable $categories".
        return view('managers.views.mailer.variables.create', [
            'categories' => MailerVariable::CATEGORIES,
            'modules' => MailerVariable::MODULES,
        ]);
    }

    public function store(StoreMailerVariableRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['is_system'] = $request->has('is_system');
        $validated['is_enabled'] = $request->boolean('is_enabled');

        if (MailerVariable::where('key', $validated['key'])->where('module', $validated['module'])->exists()) {
            return back()->withInput()->with('error', 'Ya existe una variable con esta clave en este módulo.');
        }

        $variable = MailerVariable::create($validated);

        return redirect()->route('mailers.variables.index')
            ->with('success', "Variable '{$variable->key}' creada.");
    }

    public function edit(MailerVariable $variable): View
    {
        return view('managers.views.mailer.variables.edit', [
            'variable' => $variable,
            'categories' => MailerVariable::CATEGORIES,
            'modules' => MailerVariable::MODULES,
        ]);
    }

    public function update(UpdateMailerVariableRequest $request, MailerVariable $variable): RedirectResponse
    {
        if ($variable->is_system) {
            return back()->with('error', 'No se puede editar una variable del sistema.');
        }

        $validated = $request->validated();

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $variable->update($validated);

        return redirect()->route('mailers.variables.index')
            ->with('success', "Variable '{$variable->key}' actualizada.");
    }

    public function destroy(MailerVariable $variable): RedirectResponse
    {
        if ($variable->is_system) {
            return back()->with('error', 'No se puede eliminar una variable del sistema.');
        }

        $variable->delete();

        return redirect()->route('mailers.variables.index')
            ->with('success', 'Variable eliminada.');
    }

    public function toggleStatus(MailerVariable $variable): RedirectResponse
    {
        $variable->is_enabled = ! $variable->is_enabled;
        $variable->save();

        return back()->with('success', "Variable '{$variable->key}' ".($variable->is_enabled ? 'habilitada' : 'deshabilitada').'.');
    }

    public function getByModule(Request $request): JsonResponse
    {
        $module = $request->query('module', 'core');
        $variables = MailerVariable::enabled()
            ->where(function ($q) use ($module) {
                $q->where('module', $module)->orWhere('module', 'core');
            })
            ->orderBy('category')->orderBy('key')
            ->get();

        return response()->json(['success' => true, 'variables' => $variables]);
    }

    public function getGroupedByCategory(Request $request): JsonResponse
    {
        $module = $request->query('module', 'core');
        $grouped = MailerVariable::enabled()
            ->where(function ($q) use ($module) {
                $q->where('module', $module)->orWhere('module', 'core');
            })
            ->get()
            ->groupBy('category');

        return response()->json(['success' => true, 'groups' => $grouped]);
    }

    public function getAvailableKeys(): JsonResponse
    {
        $keys = MailerVariable::enabled()->pluck('key');

        return response()->json(['success' => true, 'keys' => $keys]);
    }
}
