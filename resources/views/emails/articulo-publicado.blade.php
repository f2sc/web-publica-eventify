<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $articulo->titulo }}</title>
</head>
<body style="margin:0;padding:0;background:#f9f5ff;font-family:'Helvetica Neue',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f5ff;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(109,0,126,.08);">

        {{-- Header con gradiente — bgcolor para Outlook, style para el resto --}}
        <tr>
          <td bgcolor="#6d007e" style="background-color:#6d007e;background:linear-gradient(135deg,#6d007e,#b12140);padding:32px 40px;text-align:center;">
            <p style="margin:0;font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;">Eventify</p>
            <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,.75);">Blog de comercio local</p>
          </td>
        </tr>

        {{-- Imagen del artículo --}}
        @if($articulo->imagen_principal)
        <tr>
          <td style="padding:0;line-height:0;">
            <img src="{{ $articulo->imagen_principal }}" alt="{{ $articulo->image_alt ?? $articulo->titulo }}"
                 width="560" style="width:100%;max-height:260px;object-fit:cover;display:block;">
          </td>
        </tr>
        @endif

        {{-- Cuerpo --}}
        <tr>
          <td style="padding:40px 40px 32px;">
            @if($articulo->categoriaBlog)
            <p style="margin:0 0 8px;font-size:11px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.08em;">
              {{ $articulo->categoriaBlog->nombre }}
            </p>
            @endif

            <h1 style="margin:0 0 16px;font-size:22px;font-weight:900;color:#1f2937;line-height:1.3;">
              {{ $articulo->titulo }}
            </h1>

            @if($articulo->extracto)
            <p style="margin:0 0 28px;font-size:15px;color:#4b5563;line-height:1.7;">
              {{ $articulo->extracto }}
            </p>
            @endif

            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td align="center" style="padding-bottom:32px;">
                  <a href="{{ url('/blog/' . $articulo->slug) }}"
                     style="display:inline-block;background-color:#6d007e;background:linear-gradient(135deg,#6d007e,#b12140);color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:10px;">
                    Leer el artículo &rarr;
                  </a>
                </td>
              </tr>
            </table>

            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;">
              Recibes este email porque te suscribiste al blog de Eventify.
            </p>
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td bgcolor="#f9f5ff" style="background-color:#f9f5ff;padding:20px 40px;border-top:1px solid #ede9fe;">
            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
              &copy; {{ date('Y') }} Eventify &mdash;
              <a href="{{ url('/privacidad') }}" style="color:#6d007e;text-decoration:none;">Privacidad</a>
              &mdash;
              <a href="{{ url('/newsletter/cancelar/' . $suscriptor->token_confirmacion) }}" style="color:#6d007e;text-decoration:none;">Darme de baja</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
