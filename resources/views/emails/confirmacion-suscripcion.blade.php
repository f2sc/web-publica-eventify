<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirma tu suscripción</title>
</head>
<body style="margin:0;padding:0;background:#f9f5ff;font-family:'Helvetica Neue',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f5ff;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(109,0,126,.08);">

        {{-- Header --}}
        <tr>
          <td style="background:linear-gradient(135deg,#6d007e,#b12140);padding:32px 40px;text-align:center;">
            <p style="margin:0;font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;">Eventify</p>
            <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,.75);">Blog de comercio local</p>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="padding:40px 40px 32px;">
            <p style="margin:0 0 16px;font-size:16px;color:#3d0048;font-weight:700;">Hola, {{ $suscriptor->nombre }} 👋</p>
            <p style="margin:0 0 24px;font-size:15px;color:#4b5563;line-height:1.6;">
              Gracias por apuntarte al blog de Eventify. Solo necesitamos que confirmes tu email para empezar a enviarte guías, casos de éxito y tendencias sobre comercio local.
            </p>

            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td align="center" style="padding:8px 0 32px;">
                  <a href="{{ url('/newsletter/confirmar/' . $suscriptor->token_confirmacion) }}"
                     style="display:inline-block;background:linear-gradient(135deg,#6d007e,#b12140);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:10px;">
                    ✅ Confirmar mi suscripción
                  </a>
                </td>
              </tr>
            </table>

            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;">
              Si no solicitaste esta suscripción, ignora este email. No te enviaremos nada más.<br>
              El enlace caduca en 7 días.
            </p>
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#f9f5ff;padding:20px 40px;border-top:1px solid #ede9fe;">
            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
              © {{ date('Y') }} Eventify &mdash; <a href="{{ url('/privacidad') }}" style="color:#6d007e;">Privacidad</a>
              &mdash; <a href="{{ url('/newsletter/cancelar/' . $suscriptor->token_confirmacion) }}" style="color:#6d007e;">Darme de baja</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
