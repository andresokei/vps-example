<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/** Aviso de servicio al docente sobre sus propias asignaciones sin respuestas. No incluye claves de acceso. */
class RecordatorioAsignacionesSinRespuestasMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param Collection<int, \App\Models\AsignacionTest> $asignaciones */
    public function __construct(public readonly User $docente, public readonly Collection $asignaciones) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu test de Sociogram aún no tiene respuestas');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-asignaciones',
            with: [
                'nombre' => trim(explode(' ', (string) $this->docente->name)[0]) ?: null,
                'asignaciones' => $this->asignaciones,
                'panelUrl' => route('dashboard'),
                'entryUrl' => route('test.ingresar'),
            ],
        );
    }
}
