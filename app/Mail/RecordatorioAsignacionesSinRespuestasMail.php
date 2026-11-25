<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/**
 * Aviso de servicio al docente sobre SUS asignaciones sin respuestas.
 * Privacidad: solo incluye asignaciones cuyo profesor_id es el destinatario; nombra el test o el grupo
 * únicamente si también son suyos (un test de plantilla de otro docente aparece como "un test").
 * Nunca incluye claves de acceso, nombres ni cifras de alumnado.
 */
class RecordatorioAsignacionesSinRespuestasMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var Collection<int, array{test: string, grupo: ?string, fecha: string}> */
    public readonly Collection $items;

    /** @param Collection<int, \App\Models\AsignacionTest> $asignaciones */
    public function __construct(public readonly User $docente, Collection $asignaciones)
    {
        $this->items = $asignaciones
            ->filter(fn ($a) => (int) $a->profesor_id === (int) $docente->id)
            ->map(fn ($a) => [
                'test' => ($a->test && (int) $a->test->id_profesor === (int) $docente->id) ? (string) $a->test->nombre_test : 'un test',
                'grupo' => ($a->grupo && (int) $a->grupo->id_profesor === (int) $docente->id) ? (string) $a->grupo->nombre_grupo : null,
                'fecha' => $a->created_at->format('d/m/Y'),
            ])
            ->values();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu test de Sociogram aún no tiene respuestas');
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->bajaUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-asignaciones',
            with: [
                'nombre' => trim(explode(' ', (string) $this->docente->name)[0]) ?: null,
                'items' => $this->items,
                'panelUrl' => route('dashboard'),
                'entryUrl' => route('test.ingresar'),
                'bajaUrl' => $this->bajaUrl(),
            ],
        );
    }

    private function bajaUrl(): string
    {
        return URL::signedRoute('avisos.baja', ['user' => $this->docente->id]);
    }
}
