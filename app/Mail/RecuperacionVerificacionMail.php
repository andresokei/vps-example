<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Aviso de servicio para cuentas creadas mientras fallaba el envío de correos de verificación.
 * Es un mensaje transaccional (verificar una cuenta que la propia persona creó), no promocional.
 */
class RecuperacionVerificacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly int $horasValidez = 48,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verifica tu correo para acceder a Sociogram');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recuperacion-verificacion',
            with: [
                'verificationUrl' => $this->verificationUrl(),
                'recoverPasswordUrl' => route('password.request'),
                'horasValidez' => $this->horasValidez,
            ],
        );
    }

    private function verificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours($this->horasValidez),
            ['id' => $this->user->getKey(), 'hash' => sha1($this->user->getEmailForVerification())],
        );
    }
}
