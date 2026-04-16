<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; color: #333; max-width: 500px; margin: 0 auto; padding: 24px; }
        .label { font-size: 12px; color: #888; text-transform: uppercase; margin-top: 16px; }
        .value { font-size: 16px; font-weight: 600; margin: 4px 0 0; }
        .footer { margin-top: 32px; font-size: 12px; color: #aaa; border-top: 1px solid #eee; padding-top: 16px; }
    </style>
</head>
<body>
    <h2>Nuevo usuario registrado</h2>

    <p class="label">Nombre</p>
    <p class="value">{{ $user->name }}</p>

    <p class="label">Email</p>
    <p class="value">{{ $user->email }}</p>

    <p class="label">Fecha de registro</p>
    <p class="value">{{ $user->created_at->format('d/m/Y H:i') }}</p>

    <div class="footer">
        {{ config('app.name') }} · {{ config('app.url') }}
    </div>
</body>
</html>
