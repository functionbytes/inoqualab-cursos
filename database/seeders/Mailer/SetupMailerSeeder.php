<?php

namespace Database\Seeders\Mailer;

use App\Models\Mailer\MailerLayout;
use App\Models\Mailer\MailerVariable;
use Illuminate\Database\Seeder;

class SetupMailerSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLayouts();
        $this->seedVariables();
        $this->command->info('Mailer layouts and variables seeded.');
    }

    private function seedLayouts(): void
    {
        $layouts = [
            [
                'name' => 'Header Principal',
                'alias' => 'email_template_header',
                'type' => 'header',
                'group_name' => 'system',
                'is_protected' => true,
                'is_enabled' => true,
                'subject' => 'Header',
                'content' => <<<'HTML'
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#008bce;">
  <tr>
    <td align="center" style="padding:20px 0;">
      <a href="{SITE_URL}" style="text-decoration:none;">
        <img src="{SITE_LOGO_URL}" alt="{SITE_NAME}" height="40" style="display:block;" />
      </a>
    </td>
  </tr>
</table>
HTML,
            ],
            [
                'name' => 'Footer Principal',
                'alias' => 'email_template_footer',
                'type' => 'footer',
                'group_name' => 'system',
                'is_protected' => true,
                'is_enabled' => true,
                'subject' => 'Footer',
                'content' => <<<'HTML'
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8f9fa;border-top:1px solid #e9ecef;">
  <tr>
    <td align="center" style="padding:20px;font-family:Arial,sans-serif;font-size:12px;color:#6c757d;">
      <p style="margin:0 0 8px 0;">&copy; {CURRENT_YEAR} {SITE_NAME}. Todos los derechos reservados.</p>
      <p style="margin:0;">
        <a href="{SITE_URL}" style="color:#008bce;text-decoration:none;">{SITE_NAME}</a> &bull;
        <a href="mailto:{SUPPORT_EMAIL}" style="color:#008bce;text-decoration:none;">{SUPPORT_EMAIL}</a>
      </p>
    </td>
  </tr>
</table>
HTML,
            ],
            [
                'name' => 'Wrapper Principal',
                'alias' => 'email_template_wrapper',
                'type' => 'layout',
                'group_name' => 'system',
                'is_protected' => true,
                'is_enabled' => true,
                'subject' => 'Wrapper',
                'content' => <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{SITE_NAME}</title>
</head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td align="center" style="padding:20px 0;">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
          <tr><td>{{ header }}</td></tr>
          <tr><td style="padding:30px 40px;">{{ content }}</td></tr>
          <tr><td>{{ footer }}</td></tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML,
            ],
        ];

        foreach ($layouts as $data) {
            MailerLayout::firstOrCreate(['alias' => $data['alias']], $data);
        }
    }

    private function seedVariables(): void
    {
        $variables = [
            // User
            ['key' => 'USER_NAME', 'name' => 'Nombre del usuario', 'category' => 'user', 'module' => 'core', 'is_system' => true, 'example_value' => 'Juan García'],
            ['key' => 'USER_EMAIL', 'name' => 'Email del usuario', 'category' => 'user', 'module' => 'core', 'is_system' => true, 'example_value' => 'juan@ejemplo.com'],
            ['key' => 'USER_FIRST_NAME', 'name' => 'Nombre', 'category' => 'user', 'module' => 'core', 'is_system' => false, 'example_value' => 'Juan'],
            ['key' => 'USER_LAST_NAME', 'name' => 'Apellido', 'category' => 'user', 'module' => 'core', 'is_system' => false, 'example_value' => 'García'],
            ['key' => 'USER_PHONE', 'name' => 'Teléfono del usuario', 'category' => 'user', 'module' => 'core', 'is_system' => false, 'example_value' => '+52 555 000 0000'],
            // Site
            ['key' => 'SITE_NAME', 'name' => 'Nombre del sitio', 'category' => 'site', 'module' => 'core', 'is_system' => true, 'example_value' => config('app.name', 'Plataforma')],
            ['key' => 'SITE_URL', 'name' => 'URL del sitio', 'category' => 'site', 'module' => 'core', 'is_system' => true, 'example_value' => config('app.url', 'https://example.com')],
            ['key' => 'SITE_LOGO_URL', 'name' => 'URL del logo', 'category' => 'site', 'module' => 'core', 'is_system' => true, 'example_value' => config('app.url', 'https://example.com').'/images/logo.png'],
            ['key' => 'SUPPORT_EMAIL', 'name' => 'Email de soporte', 'category' => 'site', 'module' => 'core', 'is_system' => true, 'example_value' => config('mail.from.address', 'soporte@ejemplo.com')],
            ['key' => 'SUPPORT_PHONE', 'name' => 'Teléfono de soporte', 'category' => 'site', 'module' => 'core', 'is_system' => false, 'example_value' => '+52 555 000 0000'],
            // Company
            ['key' => 'COMPANY_NAME', 'name' => 'Nombre de la empresa', 'category' => 'company', 'module' => 'core', 'is_system' => false, 'example_value' => config('app.name')],
            ['key' => 'COMPANY_ADDRESS', 'name' => 'Dirección de la empresa', 'category' => 'company', 'module' => 'core', 'is_system' => false, 'example_value' => 'Calle Principal 123'],
            ['key' => 'COMPANY_CITY', 'name' => 'Ciudad', 'category' => 'company', 'module' => 'core', 'is_system' => false, 'example_value' => 'Ciudad de México'],
            ['key' => 'COMPANY_COUNTRY', 'name' => 'País', 'category' => 'company', 'module' => 'core', 'is_system' => false, 'example_value' => 'México'],
            // Date
            ['key' => 'CURRENT_DATE', 'name' => 'Fecha actual', 'category' => 'date', 'module' => 'core', 'is_system' => true, 'example_value' => date('d/m/Y')],
            ['key' => 'CURRENT_YEAR', 'name' => 'Año actual', 'category' => 'date', 'module' => 'core', 'is_system' => true, 'example_value' => date('Y')],
            ['key' => 'CURRENT_MONTH', 'name' => 'Mes actual', 'category' => 'date', 'module' => 'core', 'is_system' => true, 'example_value' => date('m')],
            // Links
            ['key' => 'LOGIN_URL', 'name' => 'URL de inicio de sesión', 'category' => 'links', 'module' => 'core', 'is_system' => true, 'example_value' => config('app.url').'/login'],
            ['key' => 'RESET_PASSWORD_URL', 'name' => 'URL de recuperar contraseña', 'category' => 'links', 'module' => 'core', 'is_system' => true, 'example_value' => config('app.url').'/password/reset/abc123'],
            ['key' => 'ACCOUNT_DASHBOARD_URL', 'name' => 'URL del panel', 'category' => 'links', 'module' => 'core', 'is_system' => true, 'example_value' => config('app.url').'/dashboard'],
            // Orders
            ['key' => 'ORDER_NUMBER', 'name' => 'Número de orden', 'category' => 'order', 'module' => 'orders', 'is_system' => false, 'example_value' => 'ORD-2025-001'],
            ['key' => 'ORDER_DATE', 'name' => 'Fecha de la orden', 'category' => 'order', 'module' => 'orders', 'is_system' => false, 'example_value' => date('d/m/Y')],
            ['key' => 'ORDER_TOTAL', 'name' => 'Total de la orden', 'category' => 'order', 'module' => 'orders', 'is_system' => false, 'example_value' => '$1,500.00'],
            ['key' => 'ORDER_STATUS', 'name' => 'Estado de la orden', 'category' => 'order', 'module' => 'orders', 'is_system' => false, 'example_value' => 'Completada'],
        ];

        foreach ($variables as $data) {
            $data['is_enabled'] = true;
            MailerVariable::firstOrCreate(
                ['key' => $data['key'], 'module' => $data['module']],
                $data
            );
        }
    }
}
