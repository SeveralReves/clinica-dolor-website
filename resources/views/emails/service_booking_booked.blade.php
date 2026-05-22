<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Nueva reserva de servicio</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

          {{-- Header --}}
          <tr>
            <td style="background:#16a34a;padding:32px 40px;text-align:center;">
              <p style="margin:0;font-size:13px;color:rgba(255,255,255,0.75);letter-spacing:0.08em;text-transform:uppercase;">Clínica del Dolor</p>
              <h1 style="margin:8px 0 0;font-size:22px;color:#ffffff;font-weight:700;">Nueva reserva de servicio</h1>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:36px 40px;">

              <p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.6;">
                Se ha registrado una nueva reserva. A continuación encontrarás los detalles:
              </p>

              {{-- Reference badge --}}
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                <tr>
                  <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 20px;">
                    <p style="margin:0;font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;">Referencia</p>
                    <p style="margin:4px 0 0;font-size:20px;font-weight:700;color:#16a34a;letter-spacing:0.05em;">{{ $booking->reference_id }}</p>
                  </td>
                </tr>
              </table>

              {{-- Info rows --}}
              <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:28px;">
                <tr style="background:#f9fafb;">
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;width:40%;border-bottom:1px solid #e5e7eb;">Paciente</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ $booking->patient->name }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e5e7eb;">Correo</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ $booking->patient->email }}</td>
                </tr>
                <tr style="background:#f9fafb;">
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e5e7eb;">Teléfono</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ $booking->patient->phone }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e5e7eb;">Servicio</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ $booking->service->title }}</td>
                </tr>
                <tr style="background:#f9fafb;">
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e5e7eb;">Fecha</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;border-bottom:1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($booking->date)->translatedFormat('l, j \d\e F \d\e Y') }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;{{ $booking->notes ? 'border-bottom:1px solid #e5e7eb;' : '' }}">Hora</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;{{ $booking->notes ? 'border-bottom:1px solid #e5e7eb;' : '' }}">{{ \Carbon\Carbon::parse($booking->hour)->format('g:i A') }}</td>
                </tr>
                @if($booking->notes)
                <tr style="background:#f9fafb;">
                  <td style="padding:12px 20px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;">Notas</td>
                  <td style="padding:12px 20px;font-size:14px;color:#111827;">{{ $booking->notes }}</td>
                </tr>
                @endif
              </table>

              <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;">
                Este es un correo automático generado por el sistema de Clínica del Dolor.
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
