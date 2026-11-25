<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#1f2937; line-height:1.5; max-width:560px; margin:0 auto; padding:24px;">
    <p>Hola,</p>

    <p>Ya hemos solucionado un problema que impedía enviar algunos correos de verificación de Sociogram.</p>

    <p>Si creaste una cuenta, pulsa este botón para verificar tu correo. Si no has iniciado sesión, te pedirá tu contraseña antes de confirmar:</p>

    <p style="margin:24px 0;">
        <a href="{{ $verificationUrl }}" style="background:#2563eb; color:#ffffff; padding:12px 20px; border-radius:6px; text-decoration:none; display:inline-block;">Verificar mi correo</a>
    </p>

    <p>El enlace es válido durante {{ $horasValidez }} horas. Si no recuerdas tu contraseña, puedes <a href="{{ $recoverPasswordUrl }}">restablecerla aquí</a> y después volver a pulsar el botón. Si el enlace caduca, inicia sesión y pide uno nuevo desde la página de verificación.</p>

    <p style="color:#6b7280; font-size:13px;">Si no creaste esta cuenta, puedes ignorar este mensaje. No recibirás más correos como este.</p>

    <p style="color:#6b7280; font-size:13px; margin-top:24px;">Sociogram · sociogram.app</p>
</body>
</html>
