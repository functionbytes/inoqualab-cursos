<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tu compra</title>
</head>
<body style="margin:0;padding:0;background:#f3f6f9;font-family:Arial,Helvetica,sans-serif;color:#1b2a3a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6f9;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #e7ecf1;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#0d1b2a;padding:24px 32px;color:#ffffff;">
                            <strong style="font-size:18px;">{{ $brand }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 12px;font-size:22px;color:#0d1b2a;">Tu compra quedó pendiente</h1>
                            <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#6a7888;">
                                Hola {{ $name }}, notamos que dejaste una orden sin completar. Tu cupo sigue reservado;
                                puedes finalizar el pago cuando quieras.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e7ecf1;border-radius:12px;margin:0 0 24px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="font-size:13px;color:#6a7888;">Orden</div>
                                        <div style="font-size:16px;font-weight:bold;color:#1b2a3a;">#{{ $reference }}</div>
                                    </td>
                                    <td align="right" style="padding:16px 18px;">
                                        <div style="font-size:13px;color:#6a7888;">Total</div>
                                        <div style="font-size:18px;font-weight:bold;color:#006fa3;">$ {{ number_format($total, 0, ',', '.') }} COP</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td align="center" style="border-radius:10px;background:#008bcd;">
                                        <a href="{{ $payUrl }}" target="_blank"
                                           style="display:inline-block;padding:14px 34px;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:10px;">
                                            Completar el pago
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;font-size:12.5px;line-height:1.6;color:#8d9db5;text-align:center;">
                                Si ya pagaste o no reconoces esta orden, puedes ignorar este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
