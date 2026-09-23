<?php

namespace App\Http\Controllers\Managers\Mailer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mailer\BulkActionMailerTemplateRequest;
use App\Http\Requests\Mailer\SendMailerTemplateTestRequest;
use App\Http\Requests\Mailer\StoreMailerTemplateRequest;
use App\Http\Requests\Mailer\UpdateMailerTemplateRequest;
use App\Models\Mailer\MailerLayout;
use App\Models\Mailer\MailerTemplate;
use App\Models\Mailer\MailerTemplateVersion;
use App\Services\Mailer\MailerTemplateRendererService;
use App\Services\Mailer\MailerVariableReplacementService;
use App\Services\Mailer\MailerVariableService;
use App\Traits\BuildsJsonResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MailerTemplateController extends Controller
{
    use BuildsJsonResponses;

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $module = $request->input('module');

        $query = MailerTemplate::query()->orderByDesc('updated_at');

        if ($search) {
            $query->search($search);
        }
        if ($module) {
            $query->module($module);
        }

        $templates = $query->paginate(paginationNumber(20));
        $modules = MailerTemplate::distinct('module')->pluck('module')->filter()->toArray();

        $view = request()->ajax() ? 'managers.views.mailer.templates._table' : 'managers.views.mailer.templates.index';

        return view($view, compact('templates', 'search', 'module', 'modules'));
    }

    public function create(Request $request): View
    {
        $module = $request->input('module', 'core');
        $layouts = MailerLayout::where('type', 'layout')->enabled()->orderBy('alias')->get();
        $baseContent = MailerTemplate::getStructureForModule($module);

        return view('managers.views.mailer.templates.create', compact('module', 'layouts', 'baseContent'));
    }

    public function store(StoreMailerTemplateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            if (MailerTemplate::where('key', $validated['key'])->exists()) {
                return back()->withInput()->with('error', 'Ya existe un template con esta clave (key).');
            }

            $template = MailerTemplate::create([
                'key' => $validated['key'],
                'name' => $validated['name'],
                'layout_id' => $validated['layout_id'] ?? null,
                'module' => $validated['module'],
                'description' => $validated['description'] ?? null,
                'subject' => $validated['subject'],
                'preheader' => $validated['preheader'] ?? null,
                'content' => $validated['content'],
                'is_enabled' => true,
                'is_protected' => false,
            ]);

            return redirect()->route('mailers.templates.edit', $template->uid)
                ->with('success', "Template '{$template->name}' creado exitosamente.");
        } catch (\Exception $e) {
            Log::error('Error creating mailer template', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Error al crear el template.');
        }
    }

    public function edit(Request $request, string $uid): View
    {
        $template = MailerTemplate::where('uid', $uid)->with('layout')->firstOrFail();
        $layouts = MailerLayout::where('type', 'layout')->enabled()->orderBy('alias')->get();

        return view('managers.views.mailer.templates.edit', compact('template', 'layouts'));
    }

    public function update(UpdateMailerTemplateRequest $request, string $uid): RedirectResponse
    {
        $template = MailerTemplate::where('uid', $uid)->firstOrFail();
        $validated = $request->validated();

        try {
            // Save version before overwriting
            MailerTemplateVersion::create([
                'mailer_template_id' => $template->id,
                'created_by' => auth()->id(),
                'subject' => $template->subject,
                'content' => $template->content,
                'change_note' => $request->input('change_note'),
            ]);

            // Trim versions to max allowed. `skip()` sin `take()` compila a un
            // `OFFSET` sin `LIMIT`, sintaxis inválida en MySQL/MariaDB (rompía
            // el guardado de CUALQUIER plantilla, siempre) — en cambio, se
            // conservan los $maxVersions más recientes por `id` y se borra el resto.
            $maxVersions = config('mailer-module.retention.versions_per_template', 50);
            $idsToKeep = MailerTemplateVersion::where('mailer_template_id', $template->id)
                ->latest()
                ->take($maxVersions)
                ->pluck('id');
            MailerTemplateVersion::where('mailer_template_id', $template->id)
                ->whereNotIn('id', $idsToKeep)
                ->delete();

            $template->update([
                'layout_id' => $validated['layout_id'] ?? null,
                'is_enabled' => $validated['is_enabled'] ?? true,
                'is_protected' => $validated['is_protected'] ?? false,
                'description' => $validated['description'] ?? null,
                'subject' => $validated['subject'],
                'preheader' => $validated['preheader'] ?? null,
                'content' => $validated['content'],
            ]);

            return redirect()->route('mailers.templates.edit', $template->uid)
                ->with('success', 'Template actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error updating mailer template', ['uid' => $uid, 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Error al actualizar el template.');
        }
    }

    public function preview(Request $request, string $uid)
    {
        $template = MailerTemplate::where('uid', $uid)->with('layout')->firstOrFail();
        $variables = MailerVariableReplacementService::getPreviewVariablesForTemplate($template);
        $html = MailerTemplateRendererService::renderEmailTemplate($template, $variables);

        return response($html)->header('Content-Type', 'text/html');
    }

    public function previewAjax(Request $request, string $uid): JsonResponse
    {
        // findOrFail() fuera del try/catch: si el uid no existe (plantilla
        // borrada, URL inválida), debe ser un 404 normal de Laravel, no un
        // 500 -- el catch de abajo es demasiado amplio y también atrapaba
        // ModelNotFoundException.
        $template = MailerTemplate::where('uid', $uid)->with('layout')->firstOrFail();

        try {
            $overrideLayoutId = $request->input('layout_id');
            $customContent = $request->input('content');
            $customSubject = $request->input('subject');

            if ($overrideLayoutId !== null) {
                $template->layout_id = $overrideLayoutId;
                $template->setRelation('layout', $overrideLayoutId ? MailerLayout::find($overrideLayoutId) : null);
            }
            if ($customContent !== null) {
                $template->content = $customContent;
            }
            if ($customSubject !== null) {
                $template->subject = $customSubject;
            }

            $variables = MailerVariableReplacementService::getPreviewVariablesForTemplate($template);
            $html = MailerTemplateRendererService::renderEmailTemplate($template, $variables);

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('previewAjax error: '.$e->getMessage());

            return response()->json(['success' => false, 'html' => '<div class="p-3 text-danger">Error al generar preview.</div>'], 500);
        }
    }

    public function versions(Request $request, string $uid): View
    {
        $template = MailerTemplate::where('uid', $uid)->firstOrFail();
        $versions = MailerTemplateVersion::where('mailer_template_id', $template->id)
            ->with('author:id,name,email')
            ->latest()
            ->paginate(paginationNumber(20));

        $view = $request->ajax() ? 'managers.views.mailer.templates._versions' : 'managers.views.mailer.templates.versions';

        return view($view, compact('template', 'versions'));
    }

    public function restoreVersion(Request $request, string $uid, MailerTemplateVersion $version): RedirectResponse
    {
        $template = MailerTemplate::where('uid', $uid)->firstOrFail();

        // Save current state
        MailerTemplateVersion::create([
            'mailer_template_id' => $template->id,
            'created_by' => auth()->id(),
            'subject' => $template->subject,
            'content' => $template->content,
            'change_note' => 'Auto-guardado antes de restaurar versión #'.$version->id,
        ]);

        $template->update([
            'subject' => $version->subject,
            'content' => $version->content,
        ]);

        return redirect()->route('mailers.templates.edit', $template->uid)
            ->with('success', 'Versión #'.$version->id.' restaurada.');
    }

    public function destroy(string $uid): RedirectResponse
    {
        abort_unless(auth()->user()->can('newsletters.delete'), 403);

        $template = MailerTemplate::where('uid', $uid)->firstOrFail();

        if ($template->is_protected) {
            return back()->with('error', 'No se puede eliminar un template protegido.');
        }

        try {
            $name = $template->name;
            $template->delete();

            return redirect()->route('mailers.templates.index')
                ->with('success', "Template '{$name}' eliminado.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el template.');
        }
    }

    public function toggleStatus(string $uid): RedirectResponse
    {
        $template = MailerTemplate::where('uid', $uid)->firstOrFail();
        $template->is_enabled = ! $template->is_enabled;
        $template->save();

        $status = $template->is_enabled ? 'habilitado' : 'deshabilitado';

        return back()->with('success', "Template '{$template->name}' {$status}.");
    }

    public function sendTest(SendMailerTemplateTestRequest $request, string $uid): RedirectResponse
    {
        $template = MailerTemplate::where('uid', $uid)->with('layout')->firstOrFail();

        try {
            $variables = MailerVariableReplacementService::getPreviewVariablesForTemplate($template);
            $html = MailerTemplateRendererService::renderEmailTemplate($template, $variables);
            $subject = MailerTemplateRendererService::replaceVariables($template->subject ?? 'Test', $variables);
            $plainText = MailerTemplateRendererService::htmlToPlainText($html);

            Mail::html($html, function ($message) use ($request, $subject, $plainText) {
                $message->to($request->test_email)->subject('[Test] '.$subject)->text($plainText);
            });

            return back()->with('success', 'Email de prueba enviado a '.$request->test_email);
        } catch (\Exception $e) {
            Log::error('sendTest error', ['uid' => $uid, 'error' => $e->getMessage()]);

            return back()->with('error', 'Error al enviar: '.$e->getMessage());
        }
    }

    public function bulkAction(BulkActionMailerTemplateRequest $request): JsonResponse
    {
        $templates = MailerTemplate::whereIn('id', $request->ids)->get();
        $count = $templates->count();

        match ($request->action) {
            'activate' => $templates->each->update(['is_enabled' => true]),
            'deactivate' => $templates->each->update(['is_enabled' => false]),
            'delete' => $templates->each(function ($t) {
                if (! $t->is_protected) {
                    $t->delete();
                }
            }),
        };

        $label = match ($request->action) {
            'activate' => 'activada(s)', 'deactivate' => 'desactivada(s)', 'delete' => 'eliminada(s)',
        };

        return response()->json(['success' => true, 'message' => "{$count} plantilla(s) {$label}."]);
    }

    public function variables(string $uid): JsonResponse
    {
        // firstOrFail() fuera del try/catch: un uid inexistente debe ser un
        // 404 normal, no un 500 -- el catch de abajo era demasiado amplio y
        // también atrapaba ModelNotFoundException (mismo bug de previewAjax()).
        $template = MailerTemplate::where('uid', $uid)->firstOrFail();

        try {
            return response()->json([
                'success' => true,
                'variables' => MailerVariableService::getGroupedForModule($template->module),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'variables' => []], 500);
        }
    }

    public function variablesByModule(Request $request): JsonResponse
    {
        $module = $request->query('module');
        if (! $module) {
            return response()->json(['success' => false, 'message' => 'Module required', 'variables' => []], 400);
        }

        return response()->json([
            'success' => true,
            'variables' => MailerVariableService::getGroupedForModule($module),
        ]);
    }
}
