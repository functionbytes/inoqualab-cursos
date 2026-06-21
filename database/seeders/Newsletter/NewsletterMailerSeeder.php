<?php

namespace Database\Seeders\Newsletter;

use App\Models\Mailer\MailerLayout;
use App\Models\Mailer\MailerTemplate;
use App\Models\Mailer\MailerVariable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsletterMailerSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedVariables();
        $this->seedTemplates();
        $this->command->info('Newsletter Mailer templates and variables seeded.');
    }

    private function seedVariables(): void
    {
        $variables = [
            [
                'key' => 'SUBSCRIBER_SOURCE',
                'name' => 'Origen del suscriptor',
                'category' => 'newsletter',
                'module' => 'newsletter',
                'is_system' => false,
                'example_value' => 'Formulario web',
            ],
            [
                'key' => 'SUBSCRIBER_DATE',
                'name' => 'Fecha de suscripción',
                'category' => 'newsletter',
                'module' => 'newsletter',
                'is_system' => false,
                'example_value' => date('d/m/Y H:i'),
            ],
            [
                'key' => 'SUBSCRIBER_EMAIL',
                'name' => 'Email del suscriptor',
                'category' => 'newsletter',
                'module' => 'newsletter',
                'is_system' => true,
                'example_value' => 'suscriptor@ejemplo.com',
            ],
            [
                'key' => 'SUBSCRIBER_NAME',
                'name' => 'Nombre del suscriptor',
                'category' => 'newsletter',
                'module' => 'newsletter',
                'is_system' => true,
                'example_value' => 'Juan García',
            ],
            [
                'key' => 'UNSUBSCRIBE_URL',
                'name' => 'URL para darse de baja',
                'category' => 'newsletter',
                'module' => 'newsletter',
                'is_system' => true,
                'example_value' => config('app.url').'/newsletters/unsubscribe/ejemplo-token',
            ],
            [
                'key' => 'CONFIRM_URL',
                'name' => 'URL de confirmación de suscripción',
                'category' => 'newsletter',
                'module' => 'newsletter',
                'is_system' => true,
                'example_value' => config('app.url').'/newsletters/confirm/ejemplo-token',
            ],
        ];

        foreach ($variables as $data) {
            $data['is_enabled'] = true;
            MailerVariable::firstOrCreate(
                ['key' => $data['key'], 'module' => $data['module']],
                $data
            );
        }
    }

    private function seedTemplates(): void
    {
        $wrapper = MailerLayout::where('alias', 'email_template_wrapper')->first();

        if (! $wrapper) {
            $this->command->warn('Layout email_template_wrapper not found. Run SetupMailerSeeder first.');

            return;
        }

        $templates = [
            [
                'key' => 'newsletter.subscribed',
                'name' => 'Newsletter — Bienvenida al suscriptor',
                'module' => 'newsletter',
                'description' => 'Se envía al correo del suscriptor cuando se registra exitosamente.',
                'subject' => '¡Gracias por suscribirte a {SITE_NAME}!',
                'preheader' => 'Ya formas parte de nuestra comunidad.',
                'variables' => ['SITE_NAME', 'SITE_URL', 'SUBSCRIBER_EMAIL', 'SUBSCRIBER_NAME', 'UNSUBSCRIBE_URL'],
                'content' => <<<'HTML'
<h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;margin:0 0 16px;text-align:center;">¡Ya eres parte de nuestra comunidad!</h2>
<p style="color:#444;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;margin:0 0 12px;">
  Hola{SUBSCRIBER_NAME}, el correo <strong>{SUBSCRIBER_EMAIL}</strong> ha sido registrado correctamente en el newsletter de <strong>{SITE_NAME}</strong>.
</p>
<p style="color:#444;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;margin:0 0 24px;">
  A partir de ahora recibirás nuestras novedades, cursos y contenido exclusivo directamente en tu bandeja de entrada.
</p>
<div style="text-align:center;margin:24px 0;">
  <a href="{SITE_URL}" style="background:#081A28;color:#ffffff;text-decoration:none;padding:13px 32px;border-radius:4px;font-size:13px;font-weight:700;letter-spacing:1px;font-family:'Poppins',Helvetica,Arial,sans-serif;text-transform:uppercase;display:inline-block;">
    VISITAR EL SITIO
  </a>
</div>
<p style="color:#aaa;font-family:Arial,Helvetica,sans-serif;font-size:11px;text-align:center;margin:20px 0 0;line-height:1.5;">
  Si no solicitaste esta suscripción o deseas cancelarla,
  <a href="{UNSUBSCRIBE_URL}" style="color:#555;text-decoration:underline;">date de baja aquí</a>.
</p>
HTML,
            ],
            [
                'key' => 'newsletter.unsubscribed',
                'name' => 'Newsletter — Baja del suscriptor',
                'module' => 'newsletter',
                'description' => 'Se envía al suscriptor cuando se da de baja del newsletter.',
                'subject' => 'Te has dado de baja del newsletter de {SITE_NAME}',
                'preheader' => 'Tu baja ha sido procesada correctamente.',
                'variables' => ['SITE_NAME', 'SITE_URL', 'SUBSCRIBER_EMAIL'],
                'content' => <<<'HTML'
<h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;margin:0 0 16px;text-align:center;">Has sido dado de baja</h2>
<p style="color:#444;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;margin:0 0 12px;">
  El correo <strong>{SUBSCRIBER_EMAIL}</strong> ha sido eliminado de la lista de distribución del newsletter de <strong>{SITE_NAME}</strong>.
</p>
<p style="color:#444;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;margin:0 0 24px;">
  No recibirás más correos de nuestra parte. Si fue un error, puedes volver a suscribirte en cualquier momento.
</p>
<div style="text-align:center;margin:24px 0;">
  <a href="{SITE_URL}" style="background:#081A28;color:#ffffff;text-decoration:none;padding:13px 32px;border-radius:4px;font-size:13px;font-weight:700;letter-spacing:1px;font-family:'Poppins',Helvetica,Arial,sans-serif;text-transform:uppercase;display:inline-block;">
    VOLVER AL SITIO
  </a>
</div>
HTML,
            ],
            [
                'key' => 'newsletter.admin_notification',
                'name' => 'Newsletter — Notificación al administrador',
                'module' => 'newsletter',
                'description' => 'Se envía al administrador cuando llega un nuevo suscriptor.',
                'subject' => 'Nuevo suscriptor en {SITE_NAME}: {SUBSCRIBER_EMAIL}',
                'preheader' => 'Tienes un nuevo suscriptor en el newsletter.',
                'variables' => ['SITE_NAME', 'SITE_URL', 'SUBSCRIBER_EMAIL', 'SUBSCRIBER_NAME', 'SUBSCRIBER_SOURCE', 'SUBSCRIBER_DATE'],
                'content' => <<<'HTML'
<h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:20px;font-weight:700;margin:0 0 16px;">Nuevo suscriptor al newsletter</h2>
<p style="color:#444;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;margin:0 0 20px;">
  Se ha registrado un nuevo suscriptor en <strong>{SITE_NAME}</strong>.
</p>
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e9ecef;border-radius:6px;overflow:hidden;margin-bottom:24px;">
  <tr style="background:#f8f9fa;">
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:#081A28;width:140px;border-bottom:1px solid #e9ecef;">Correo</td>
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#444;border-bottom:1px solid #e9ecef;">{SUBSCRIBER_EMAIL}</td>
  </tr>
  <tr>
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:#081A28;border-bottom:1px solid #e9ecef;">Nombre</td>
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#444;border-bottom:1px solid #e9ecef;">{SUBSCRIBER_NAME}</td>
  </tr>
  <tr style="background:#f8f9fa;">
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:#081A28;border-bottom:1px solid #e9ecef;">Origen</td>
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#444;border-bottom:1px solid #e9ecef;">{SUBSCRIBER_SOURCE}</td>
  </tr>
  <tr>
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:#081A28;">Fecha</td>
    <td style="padding:10px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#444;">{SUBSCRIBER_DATE}</td>
  </tr>
</table>
<div style="text-align:center;margin:0;">
  <a href="{SITE_URL}/panel/newsletter" style="background:#081A28;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:4px;font-size:13px;font-weight:700;letter-spacing:1px;font-family:'Poppins',Helvetica,Arial,sans-serif;text-transform:uppercase;display:inline-block;">
    VER SUSCRIPTORES
  </a>
</div>
HTML,
            ],
            [
                'key' => 'newsletter.double_optin',
                'name' => 'Newsletter — Confirmar suscripción (doble opt-in)',
                'module' => 'newsletter',
                'description' => 'Se envía cuando el doble opt-in está activo. El usuario debe hacer clic para confirmar.',
                'subject' => 'Confirma tu suscripción a {SITE_NAME}',
                'preheader' => 'Un solo clic y quedas suscrito.',
                'variables' => ['SITE_NAME', 'SITE_URL', 'SUBSCRIBER_EMAIL', 'SUBSCRIBER_NAME', 'CONFIRM_URL'],
                'content' => <<<'HTML'
<h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:22px;font-weight:700;margin:0 0 16px;text-align:center;">Confirma tu suscripción</h2>
<p style="color:#444;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;margin:0 0 12px;">
  Hola{SUBSCRIBER_NAME}, gracias por registrarte en el newsletter de <strong>{SITE_NAME}</strong>.
</p>
<p style="color:#444;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;margin:0 0 24px;">
  Para completar tu suscripción y empezar a recibir nuestro contenido, confirma tu correo haciendo clic en el botón:
</p>
<div style="text-align:center;margin:28px 0;">
  <a href="{CONFIRM_URL}" style="background:#008bce;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:4px;font-size:14px;font-weight:700;letter-spacing:1px;font-family:'Poppins',Helvetica,Arial,sans-serif;text-transform:uppercase;display:inline-block;">
    CONFIRMAR SUSCRIPCIÓN
  </a>
</div>
<p style="color:#888;font-family:Arial,Helvetica,sans-serif;font-size:12px;text-align:center;margin:20px 0 0;line-height:1.6;">
  Si no solicitaste esta suscripción, puedes ignorar este correo con seguridad.<br>
  El enlace expira en 7 días.
</p>
HTML,
            ],
        ];

        foreach ($templates as $data) {
            MailerTemplate::firstOrCreate(
                ['key' => $data['key']],
                [
                    'uid' => Str::uuid(),
                    'name' => $data['name'],
                    'module' => $data['module'],
                    'description' => $data['description'],
                    'layout_id' => $wrapper->id,
                    'subject' => $data['subject'],
                    'preheader' => $data['preheader'],
                    'content' => $data['content'],
                    'variables' => $data['variables'],
                    'is_enabled' => true,
                    'is_protected' => false,
                ]
            );
        }
    }
}
