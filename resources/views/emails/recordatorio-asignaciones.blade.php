<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#1f2937; line-height:1.5; max-width:560px; margin:0 auto; padding:24px;">
    <p>Hola{{ $nombre ? ' '.$nombre : '' }},</p>

    <p>Asignaste {{ $items->count() === 1 ? 'un test' : 'varios tests' }} en Sociogram y todavía no hay ninguna respuesta del alumnado:</p>

    <ul>
        @foreach ($items as $item)
            <li>{{ ucfirst($item['test']) }}{{ $item['grupo'] ? ' · grupo '.$item['grupo'] : '' }} (asignado el {{ $item['fecha'] }})</li>
        @endforeach
    </ul>

    <p>El alumnado responde entrando en <a href="{{ $entryUrl }}">{{ $entryUrl }}</a> con la clave de acceso de cada asignación. Para verla y copiar el enlace y la clave, entra en tu panel:</p>

    <p style="margin:24px 0;">
        <a href="{{ $panelUrl }}" style="background:#2563eb; color:#ffffff; padding:12px 20px; border-radius:6px; text-decoration:none; display:inline-block;">Ver mis asignaciones</a>
    </p>

    <p style="color:#6b7280; font-size:13px;">Este es un único aviso por asignación; no recibirás más sobre estas. Si no quieres recibir este tipo de avisos, <a href="{{ $bajaUrl }}">desactívalos aquí</a>.</p>

    <p style="color:#6b7280; font-size:13px; margin-top:24px;">Sociogram · sociogram.app</p>
</body>
</html>
