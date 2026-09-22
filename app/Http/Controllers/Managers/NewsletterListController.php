<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Models\NewsletterList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsletterListController extends Controller
{
    public function index(): View
    {
        $lists = NewsletterList::query()
            ->withCount('subscribers')
            ->orderBy('name')
            ->paginate(paginationNumber());

        $view = request()->ajax() ? 'managers.views.newsletter.lists._table' : 'managers.views.newsletter.lists.index';

        return view($view, compact('lists'));
    }

    public function create(): View
    {
        return view('managers.views.newsletter.lists.form', ['list' => null]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.create'), 403);

        $data = $this->validated($request);

        // Las listas creadas desde el panel son manuales (las dinámicas las siembra
        // el sistema y se gestionan solas por eventos).
        $list = NewsletterList::create([
            'slack' => Str::slug($data['name']).'-'.Str::random(5),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger' => 'manual',
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lista creada correctamente.',
            'redirect' => route('manager.newsletter.lists.index'),
        ], 201);
    }

    public function edit(NewsletterList $list): View
    {
        return view('managers.views.newsletter.lists.form', compact('list'));
    }

    public function update(Request $request, NewsletterList $list): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        $data = $this->validated($request);

        $list->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json(['success' => true, 'message' => 'Lista actualizada correctamente.']);
    }

    public function show(Request $request, NewsletterList $list): View
    {
        $members = $list->subscribers()
            ->orderByPivot('created_at', 'desc')
            ->paginate(paginationNumber());

        $view = $request->ajax() ? 'managers.views.newsletter.lists._members' : 'managers.views.newsletter.lists.members';

        return view($view, compact('list', 'members'));
    }

    public function addMember(Request $request, NewsletterList $list): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no es válido.',
        ]);

        $list->addByEmail($data['email'], $data['name'] ?? null, 'Agregado manualmente');

        return response()->json([
            'success' => true,
            'message' => 'Suscriptor agregado a la lista.',
            'redirect' => route('manager.newsletter.lists.members', $list->id),
        ]);
    }

    public function removeMember(NewsletterList $list, int $newsletter): JsonResponse
    {
        abort_unless(auth()->user()->can('newsletters.update'), 403);

        $list->subscribers()->detach($newsletter);

        return response()->json(['success' => true, 'message' => 'Suscriptor retirado de la lista.']);
    }

    public function destroy(NewsletterList $list): RedirectResponse
    {
        abort_unless(auth()->user()->can('newsletters.delete'), 403);

        // Las listas dinámicas están cableadas a comandos/listeners: no se borran
        // desde aquí para no dejar el sistema apuntando a una lista inexistente.
        if ($list->trigger !== 'manual') {
            return back()->with('error', 'Las listas dinámicas del sistema no se pueden eliminar.');
        }

        // newsletter_campaigns.newsletter_list_id tiene nullOnDelete(): borrar
        // la lista deja la campaña con list_id NULL, y
        // NewsletterCampaignController::send() trata "sin lista" como "enviar
        // a TODOS los suscriptores" -- una campaña segmentada terminaría
        // enviándose a toda la base en silencio.
        if ($list->campaigns()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay campañas asociadas a esta lista. Elimínalas o desvincúlalas primero.');
        }

        $list->delete();

        return redirect()->route('manager.newsletter.lists.index')->with('success', 'Lista eliminada correctamente.');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:newsletter_lists,id'],
        ]);

        $permission = $request->action === 'delete' ? 'newsletters.delete' : 'newsletters.update';
        abort_unless(auth()->user()->can($permission), 403);

        if ($request->action === 'delete') {
            // Igual que destroy(): las listas dinámicas y las que tienen campañas
            // asociadas no se pueden eliminar; se excluyen del lote en vez de
            // romperlo.
            $lists = NewsletterList::whereIn('id', $request->ids)
                ->where('trigger', 'manual')
                ->get()
                ->filter(fn (NewsletterList $list) => ! $list->campaigns()->exists());

            $count = $lists->count();
            $lists->each->delete();

            return response()->json(['success' => true, 'message' => $count.' lista(s) eliminadas.']);
        }

        $query = NewsletterList::whereIn('id', $request->ids);
        $count = $query->count();
        $query->update(['is_active' => $request->action === 'activate']);

        return response()->json(['success' => true, 'message' => $count.' lista(s) procesadas.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'El nombre de la lista es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'description.max' => 'La descripción no puede superar los 500 caracteres.',
        ]);
    }
}
