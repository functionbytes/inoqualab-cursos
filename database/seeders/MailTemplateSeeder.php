<?php

namespace Database\Seeders;

use App\Models\MailTemplate;
use Illuminate\Database\Seeder;

class MailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $data) {
            MailTemplate::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }

    private function wrap(string $innerHtml): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#F9F9F9;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#F9F9F9;">
<tr><td align="center" style="padding:20px 10px;">
<table border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;">
<tr><td height="3" style="background-color:#081A28;font-size:1px;line-height:3px;">&nbsp;</td></tr>
<tr><td align="center" style="padding:30px 20px 20px;background:#FFFFFF;">
  <a href="{SITE_URL}" style="text-decoration:none;">
    <img src="{LOGO_URL}" alt="{SITE_NAME}" style="max-width:200px;height:auto;display:block;margin:0 auto;">
  </a>
</td></tr>
{$innerHtml}
<tr><td style="padding:15px 20px;background:#F5F5F5;text-align:center;border-top:1px solid #E0E0E0;">
  <p style="margin:0;color:#999999;font-size:12px;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
    &copy; {CURRENT_YEAR} {SITE_NAME}. Todos los derechos reservados.
  </p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    private function templates(): array
    {
        return [

            /* ------------------------------------------------------------------ */
            /* AUTH — RECUPERAR CONTRASEÑA */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'auth.forgot_password',
                'name' => 'Recuperar contraseña',
                'subject' => 'Restablece tu contraseña',
                'description' => 'Se envía al usuario cuando solicita recuperar su contraseña.',
                'variables' => [
                    'CUSTOMER_EMAIL' => 'Correo del usuario',
                    'RESET_URL' => 'Enlace para restablecer la contraseña (expira en 24h)',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px 10px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 10px;">
    RECUPERAR CONTRASEÑA
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Hemos recibido una solicitud para restablecer la contraseña de la cuenta <strong>{CUSTOMER_EMAIL}</strong>.
    Haz clic en el botón a continuación. El enlace es válido durante <strong>24 horas</strong>.
  </p>
  <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin:20px auto;">
    <tr>
      <td style="background-color:#081A28;padding:12px 30px;border-radius:4px;">
        <a href="{RESET_URL}" style="color:#FFFFFF;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
          RESTABLECER CONTRASEÑA
        </a>
      </td>
    </tr>
  </table>
  <p style="color:#888888;font-size:12px;margin:20px 0 0;">
    Si no solicitaste este cambio, puedes ignorar este mensaje.<br>
    O copia este enlace en tu navegador: <a href="{RESET_URL}" style="color:#081A28;">{RESET_URL}</a>
  </p>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* AUTH — CONTRASEÑA ACTUALIZADA */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'auth.reset_password_success',
                'name' => 'Contraseña actualizada',
                'subject' => 'Tu contraseña ha sido actualizada',
                'description' => 'Confirmación de que la contraseña fue cambiada exitosamente.',
                'variables' => [
                    'CUSTOMER_EMAIL' => 'Correo del usuario',
                    'LOGIN_URL' => 'Enlace al login',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 10px;">
    CONTRASEÑA ACTUALIZADA
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    La contraseña de la cuenta <strong>{CUSTOMER_EMAIL}</strong> ha sido actualizada correctamente.
    Si no realizaste este cambio, contacta a soporte inmediatamente.
  </p>
  <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin:10px auto;">
    <tr>
      <td style="background-color:#081A28;padding:12px 30px;border-radius:4px;">
        <a href="{LOGIN_URL}" style="color:#FFFFFF;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
          INICIAR SESIÓN
        </a>
      </td>
    </tr>
  </table>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* ÓRDENES — PAGO APROBADO */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'orders.approved',
                'name' => 'Pago aprobado',
                'subject' => '¡Pago aprobado! - Orden #{ORDER_NUMBER}',
                'description' => 'Se envía al usuario/distribuidor cuando su pago es aprobado.',
                'variables' => [
                    'CUSTOMER_FIRSTNAME' => 'Nombre del cliente',
                    'CUSTOMER_LASTNAME' => 'Apellido del cliente',
                    'ORDER_NUMBER' => 'Número/referencia de la orden',
                    'PAYMENT_METHOD' => 'Método de pago',
                    'PAYMENT_DATE' => 'Fecha del pago',
                    'ORDER_TOTAL' => 'Total pagado',
                    'COURSES_URL' => 'URL de mis cursos',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px 10px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 6px;">
    ¡PAGO APROBADO!
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Hola <strong>{CUSTOMER_FIRSTNAME} {CUSTOMER_LASTNAME}</strong>,<br>
    tu pago fue procesado exitosamente. Ya puedes acceder a tu contenido.
  </p>
</td></tr>
<tr><td style="padding:0 20px 20px;background:#FFFFFF;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F5F5F5;border-radius:6px;padding:20px;">
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Orden:</strong> #{ORDER_NUMBER}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Método de pago:</strong> {PAYMENT_METHOD}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Fecha:</strong> {PAYMENT_DATE}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#081A28;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Total pagado:</strong> <strong>{ORDER_TOTAL}</strong>
    </td></tr>
  </table>
</td></tr>
<tr><td style="padding:10px 20px 30px;background:#FFFFFF;text-align:center;">
  <table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background-color:#081A28;padding:12px 30px;border-radius:4px;">
        <a href="{COURSES_URL}" style="color:#FFFFFF;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
          IR A MIS CURSOS
        </a>
      </td>
    </tr>
  </table>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* ÓRDENES — PAGO PENDIENTE */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'orders.pending',
                'name' => 'Pago en proceso',
                'subject' => 'Pago en proceso - Orden #{ORDER_NUMBER}',
                'description' => 'Se envía cuando el pago está siendo verificado (ej. PSE).',
                'variables' => [
                    'CUSTOMER_FIRSTNAME' => 'Nombre del cliente',
                    'CUSTOMER_LASTNAME' => 'Apellido del cliente',
                    'ORDER_NUMBER' => 'Número/referencia de la orden',
                    'PAYMENT_METHOD' => 'Método de pago',
                    'ORDER_TOTAL' => 'Total de la orden',
                    'ORDERS_URL' => 'URL de mis órdenes',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px 10px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 6px;">
    PAGO EN PROCESO
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Hola <strong>{CUSTOMER_FIRSTNAME} {CUSTOMER_LASTNAME}</strong>,<br>
    recibimos tu orden y tu pago está siendo verificado. Algunos medios de pago pueden tardar unos minutos.<br>
    Te notificaremos en cuanto se confirme.
  </p>
</td></tr>
<tr><td style="padding:0 20px 20px;background:#FFFFFF;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F5F5F5;border-radius:6px;padding:20px;">
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Orden:</strong> #{ORDER_NUMBER}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Método de pago:</strong> {PAYMENT_METHOD}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Total:</strong> {ORDER_TOTAL}
    </td></tr>
  </table>
</td></tr>
<tr><td style="padding:10px 20px 30px;background:#FFFFFF;text-align:center;">
  <p style="color:#888888;font-size:13px;margin:0 0 15px;">No es necesario volver a pagar.</p>
  <table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background-color:#081A28;padding:12px 30px;border-radius:4px;">
        <a href="{ORDERS_URL}" style="color:#FFFFFF;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
          VER MIS ÓRDENES
        </a>
      </td>
    </tr>
  </table>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* ÓRDENES — PAGO RECHAZADO */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'orders.voided',
                'name' => 'Pago rechazado',
                'subject' => 'Pago rechazado - Orden #{ORDER_NUMBER}',
                'description' => 'Se envía cuando el pago es rechazado o anulado.',
                'variables' => [
                    'CUSTOMER_FIRSTNAME' => 'Nombre del cliente',
                    'CUSTOMER_LASTNAME' => 'Apellido del cliente',
                    'ORDER_NUMBER' => 'Número/referencia de la orden',
                    'PAYMENT_METHOD' => 'Método de pago',
                    'ORDER_TOTAL' => 'Total de la orden',
                    'CHECKOUT_URL' => 'URL para reintentar el pago',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px 10px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#c0392b;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 6px;">
    PAGO RECHAZADO
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Hola <strong>{CUSTOMER_FIRSTNAME} {CUSTOMER_LASTNAME}</strong>,<br>
    lamentablemente tu pago para la orden <strong>#{ORDER_NUMBER}</strong> fue rechazado.
    Puedes intentarlo nuevamente con otro método de pago.
  </p>
</td></tr>
<tr><td style="padding:0 20px 20px;background:#FFFFFF;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFF5F5;border-left:4px solid #c0392b;border-radius:4px;padding:16px;">
    <tr><td style="padding:4px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Orden:</strong> #{ORDER_NUMBER}
    </td></tr>
    <tr><td style="padding:4px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Método:</strong> {PAYMENT_METHOD}
    </td></tr>
    <tr><td style="padding:4px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Total:</strong> {ORDER_TOTAL}
    </td></tr>
  </table>
</td></tr>
<tr><td style="padding:10px 20px 30px;background:#FFFFFF;text-align:center;">
  <p style="color:#555555;font-size:13px;margin:0 0 15px;">
    Si necesitas ayuda escríbenos a <a href="mailto:{SUPPORT_EMAIL}" style="color:#081A28;">{SUPPORT_EMAIL}</a>
  </p>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* CERTIFICADOS — RENOVACIÓN */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'certificates.renewal',
                'name' => 'Renovación de certificado',
                'subject' => 'Tu certificado de {COURSE_TITLE} está por vencer',
                'description' => 'Se envía al usuario 30 días antes de que venza su certificado.',
                'variables' => [
                    'CUSTOMER_FIRSTNAME' => 'Nombre del usuario',
                    'COURSE_TITLE' => 'Título del curso',
                    'EXPIRY_DATE' => 'Fecha de vencimiento (dd/mm/aaaa)',
                    'RENEW_URL' => 'Enlace para renovar',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px 10px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 6px;">
    TU CERTIFICADO ESTÁ POR VENCER
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 10px;">
    Hola <strong>{CUSTOMER_FIRSTNAME}</strong>,
  </p>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Tu certificado del curso <strong>{COURSE_TITLE}</strong> vence el <strong>{EXPIRY_DATE}</strong>.
    Para mantener tu certificación vigente, renueva tu inscripción al curso.
    Al completarla, tu certificado quedará válido por un año más.
  </p>
</td></tr>
<tr><td style="padding:0 20px 30px;background:#FFFFFF;text-align:center;">
  <table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background-color:#081A28;padding:12px 30px;border-radius:4px;">
        <a href="{RENEW_URL}" style="color:#FFFFFF;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
          RENOVAR MI CERTIFICADO
        </a>
      </td>
    </tr>
  </table>
  <p style="color:#888888;font-size:12px;margin:20px 0 0;">
    Si ya renovaste, puedes ignorar este mensaje.
  </p>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* INSCRIPCIONES — NOTIFICACIÓN AL ESTUDIANTE */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'inscriptions.notification',
                'name' => 'Notificación de inscripción',
                'subject' => 'Inscripción al curso {COURSE_NAME}',
                'description' => 'Se envía al estudiante cuando es inscrito en un curso.',
                'variables' => [
                    'CUSTOMER_FIRSTNAME' => 'Nombre del estudiante',
                    'CUSTOMER_LASTNAME' => 'Apellido del estudiante',
                    'COURSE_NAME' => 'Nombre del curso',
                    'START_DATE' => 'Fecha de inicio del curso',
                    'EXPIRE_DATE' => 'Fecha de vencimiento',
                    'LOGIN_URL' => 'URL de acceso a la plataforma',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px 10px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 6px;">
    NOTIFICACIÓN DE INSCRIPCIÓN
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 10px;">
    Hola <strong>{CUSTOMER_FIRSTNAME} {CUSTOMER_LASTNAME}</strong>,
  </p>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 10px;">
    Nos complace informarte que has sido inscrito en el curso <strong>{COURSE_NAME}</strong>.
  </p>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    El curso inicia el <strong>{START_DATE}</strong> y finaliza el <strong>{EXPIRE_DATE}</strong>.
    Durante este periodo deberás completar todas las actividades y evaluaciones.
  </p>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Para acceder usa tu correo electrónico o número de cédula. Si no has cambiado tu contraseña,
    esta coincidirá con tu número de cédula.
  </p>
  <p style="color:#555555;font-size:13px;margin:0 0 20px;">
    ¿Dudas? Escríbenos por
    <a href="https://api.whatsapp.com/send?phone={SUPPORT_PHONE}" target="_blank" style="color:#081A28;">WhatsApp</a>
  </p>
</td></tr>
<tr><td style="padding:0 20px 30px;background:#FFFFFF;text-align:center;">
  <table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background-color:#081A28;padding:12px 30px;border-radius:4px;">
        <a href="{LOGIN_URL}" style="color:#FFFFFF;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
          ACCEDER A LA PLATAFORMA
        </a>
      </td>
    </tr>
  </table>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* INSCRIPCIONES — REPORTE A LA EMPRESA */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'inscriptions.report',
                'name' => 'Reporte de inscripción (empresa)',
                'subject' => 'Reporte de inscripción - {COURSE_NAME}',
                'description' => 'Se envía a la empresa cuando uno de sus colaboradores es inscrito.',
                'variables' => [
                    'ENTERPRISE_NAME' => 'Nombre de la empresa',
                    'CUSTOMER_NAMES' => 'Nombre completo del estudiante',
                    'CUSTOMER_IDENTIFICATION' => 'Cédula del estudiante',
                    'COURSE_NAME' => 'Nombre del curso',
                    'DATE' => 'Fecha de inscripción',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 16px;">
    REPORTE DE INSCRIPCIÓN
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Estimada empresa <strong>{ENTERPRISE_NAME}</strong>,<br>
    se ha registrado la siguiente inscripción en su cuenta:
  </p>
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F5F5F5;border-radius:6px;padding:20px;text-align:left;">
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Estudiante:</strong> {CUSTOMER_NAMES}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Cédula:</strong> {CUSTOMER_IDENTIFICATION}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Curso:</strong> {COURSE_NAME}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Fecha:</strong> {DATE}
    </td></tr>
  </table>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* FACTURAS — NOTIFICACIÓN AL DISTRIBUIDOR */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'invoices.notification',
                'name' => 'Notificación de factura (distribuidor)',
                'subject' => 'Nueva factura generada - {DISTRIBUTOR_NAME}',
                'description' => 'Se envía al distribuidor cuando se genera una nueva factura.',
                'variables' => [
                    'DISTRIBUTOR_NAME' => 'Nombre del distribuidor',
                    'NIT' => 'NIT del distribuidor',
                    'PERIOD_FROM' => 'Inicio del período facturado',
                    'PERIOD_TO' => 'Fin del período facturado',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 16px;">
    NOTIFICACIÓN DE FACTURACIÓN
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Estimado distribuidor <strong>{DISTRIBUTOR_NAME}</strong>,<br>
    se ha generado una nueva factura para su cuenta.
  </p>
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F5F5F5;border-radius:6px;padding:20px;text-align:left;">
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Distribuidor:</strong> {DISTRIBUTOR_NAME}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>NIT:</strong> {NIT}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Período:</strong> {PERIOD_FROM} — {PERIOD_TO}
    </td></tr>
  </table>
  <p style="color:#555555;font-size:13px;margin:20px 0 0;">
    Para más información comunícate con nosotros a <a href="mailto:{SUPPORT_EMAIL}" style="color:#081A28;">{SUPPORT_EMAIL}</a>
  </p>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* FACTURAS — REPORTE A CONTABILIDAD */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'invoices.report',
                'name' => 'Reporte de factura (contabilidad)',
                'subject' => 'Reporte de facturación #{REFERENCE}',
                'description' => 'Se envía al usuario de contabilidad cuando se cierra una factura de distribuidor.',
                'variables' => [
                    'REFERENCE' => 'Referencia de la factura',
                    'DISTRIBUTOR_NAME' => 'Nombre del distribuidor',
                    'NIT' => 'NIT del distribuidor',
                    'PERIOD_FROM' => 'Inicio del período',
                    'PERIOD_TO' => 'Fin del período',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 16px;">
    REPORTE DE FACTURACIÓN
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Se ha generado el siguiente reporte de facturación:
  </p>
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F5F5F5;border-radius:6px;padding:20px;text-align:left;">
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Referencia:</strong> #{REFERENCE}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Distribuidor:</strong> {DISTRIBUTOR_NAME}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>NIT:</strong> {NIT}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Período:</strong> {PERIOD_FROM} — {PERIOD_TO}
    </td></tr>
  </table>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* CONTACTO — ALERTA AL ADMINISTRADOR */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'contacts.alert',
                'name' => 'Alerta de contacto (admin)',
                'subject' => 'Nueva solicitud de contacto #{CONTACT_ID}',
                'description' => 'Se envía al administrador cuando alguien llena el formulario de contacto.',
                'variables' => [
                    'CONTACT_ID' => 'ID/referencia del contacto',
                    'CONTACT_DATE' => 'Fecha de la solicitud',
                    'CONTACT_NAMES' => 'Nombre completo del contacto',
                    'CONTACT_EMAIL' => 'Correo del contacto',
                    'CONTACT_URL' => 'URL para ver la solicitud en el panel',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 6px;">
    NUEVA SOLICITUD DE CONTACTO
  </h2>
  <p style="color:#888888;font-size:14px;margin:0 0 20px;">
    ID #{CONTACT_ID} &nbsp;|&nbsp; {CONTACT_DATE}
  </p>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Se ha recibido una nueva solicitud de contacto. Accede al panel para ver los detalles.
  </p>
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F5F5F5;border-radius:6px;padding:20px;text-align:left;">
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Nombre:</strong> {CONTACT_NAMES}
    </td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#333333;font-family:'Open Sans',Helvetica,Arial,sans-serif;">
      <strong>Correo:</strong> {CONTACT_EMAIL}
    </td></tr>
  </table>
</td></tr>
<tr><td style="padding:10px 20px 30px;background:#FFFFFF;text-align:center;">
  <table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background-color:#081A28;padding:12px 30px;border-radius:4px;">
        <a href="{CONTACT_URL}" style="color:#FFFFFF;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
          VER EN EL PANEL
        </a>
      </td>
    </tr>
  </table>
</td></tr>
INNER),
            ],

            /* ------------------------------------------------------------------ */
            /* CONTACTO — RESPUESTA AL USUARIO */
            /* ------------------------------------------------------------------ */
            [
                'key' => 'contacts.response',
                'name' => 'Respuesta de contacto (usuario)',
                'subject' => 'Hemos recibido tu mensaje',
                'description' => 'Confirmación automática al usuario que envió el formulario de contacto.',
                'variables' => [
                    'CUSTOMER_FIRSTNAME' => 'Nombre del usuario',
                    'CONTACT_DATE' => 'Fecha del mensaje',
                ],
                'content' => $this->wrap(<<<'INNER'
<tr><td style="padding:30px 20px;background:#FFFFFF;text-align:center;">
  <h2 style="color:#081A28;font-family:'Poppins',Helvetica,Arial,sans-serif;font-size:24px;font-weight:700;margin:0 0 10px;">
    HEMOS RECIBIDO TU MENSAJE
  </h2>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 10px;">
    Hola <strong>{CUSTOMER_FIRSTNAME}</strong>,
  </p>
  <p style="color:#555555;font-size:14px;line-height:22px;margin:0 0 20px;">
    Tu mensaje fue recibido el <strong>{CONTACT_DATE}</strong>.
    Nuestro equipo lo revisará y te responderá a la brevedad posible.
  </p>
  <p style="color:#555555;font-size:13px;margin:0;">
    Si tienes una consulta urgente, puedes escribirnos directamente a
    <a href="mailto:{SUPPORT_EMAIL}" style="color:#081A28;">{SUPPORT_EMAIL}</a>
  </p>
</td></tr>
INNER),
            ],

        ];
    }
}
