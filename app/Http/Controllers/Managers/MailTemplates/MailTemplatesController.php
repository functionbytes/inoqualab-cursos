<?php

namespace App\Http\Controllers\Managers\MailTemplates;

use App\Http\Controllers\Controller;
use App\Models\MailTemplate;
use App\Services\MailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTemplatesController extends Controller
{
    public function index()
    {
        $templates = MailTemplate::orderBy('name')->get();

        return view('managers.views.mail_templates.index', compact('templates'));
    }

    public function edit($id)
    {
        $template = MailTemplate::findOrFail($id);

        return view('managers.views.mail_templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = MailTemplate::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $template->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
        ]);

        return redirect()->route('manager.mail_templates.edit', $template->id)
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function preview($id)
    {
        $template = MailTemplate::findOrFail($id);

        $data = app(MailTemplateService::class)->render($template->key);

        return response($data['html']);
    }

    public function previewAjax(Request $request, $id)
    {
        $template = MailTemplate::findOrFail($id);

        $content = $request->input('content', $template->content);
        $html = app(MailTemplateService::class)->renderRaw($content);

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function sendTest(Request $request, $id)
    {
        $template = MailTemplate::findOrFail($id);

        $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $service = app(MailTemplateService::class);
        $content = $request->input('content', $template->content);
        $subject = $request->input('subject', $template->subject);

        $html = $service->renderRaw($content);
        $renderedSubject = $service->renderRaw($subject);

        try {
            Mail::html($html, function ($msg) use ($request, $renderedSubject) {
                $msg->to($request->test_email)
                    ->subject('[PRUEBA] '.$renderedSubject);
            });

            return response()->json([
                'success' => true,
                'message' => 'Correo de prueba enviado a '.$request->test_email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar: '.$e->getMessage(),
            ], 500);
        }
    }
}
