<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tu cita fue agendada</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

          {{-- Header --}}
          <tr>
            <td style="background:#16a34a;padding:36px 40px;text-align:center;">
              <p style="margin:0;font-size:13px;color:rgba(255,255,255,0.75);letter-spacing:0.08em;text-transform:uppercase;">Clínica del Dolor</p>
              <h1 style="margin:10px 0 4px;font-size:24px;color:#ffffff;font-weight:700;">¡Tu cita fue agendada!</h1>
              <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.85);">Hemos recibido tu solicitud correctamente.</p>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:36px 40px;">

              <p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.6;">
                Hola <strong>{{ $appointment->patient->name }}</strong>, tu cita ha sido registrada con éxito. A continuación encontrarás el resumen:
              </p>

              {{-- Reference badge --}}
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                <tr>
                  <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 20px;text-align:center;">
                    <p style="margin:0;font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;">Tu número de referencia</p>
                    <p style="margin:6px 0 0;font-size:26px;font-weight:700;color:#16a34a;letter-spacing:0.06em;">{{ $appointment->reference_id }}</p>
                    <p style="margin:4px 0 0;font-size:12px;color:#6b7280;">Guarda este código para consultar el estado de tu cita.</p>
                  </td>
                </tr>
              </table>

              {{-- Info rows --}}
              <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:28px;">
                <tr style="background:#f9fafb;">
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;width:40%;border-bottom:1px solid #e5e7eb;">Especialista</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ $appointment->specialist->name }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e5e7eb;">Especialidad</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ $appointment->specialist->specialty }}</td>
                </tr>
                <tr style="background:#f9fafb;">
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e5e7eb;">Fecha</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($appointment->date)->translatedFormat('l, j \d\e F \d\e Y') }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;">Hora</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;">{{ \Carbon\Carbon::parse($appointment->hour)->format('g:i A') }}</td>
                </tr>
              </table>

              {{-- CTA Button --}}
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                <tr>
                  <td align="center">
                    <a href="{{ url('/agendar/gracias/' . $appointment->reference_id) }}"
                       style="display:inline-block;background:#16a34a;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:8px;letter-spacing:0.02em;">
                      Ver estado de mi cita
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;text-align:center;">
                Si tienes alguna duda, no dudes en contactarnos.<br/>
                Este es un correo automático, por favor no respondas a este mensaje.
              </p>

            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 40px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#9ca3af;">© {{ date('Y') }} Clínica del Dolor — Todos los derechos reservados</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
