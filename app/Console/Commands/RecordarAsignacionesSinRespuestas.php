<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioAsignacionesSinRespuestasMail;
use App\Models\AsignacionTest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Avisa a cada docente (verificado) de sus asignaciones abiertas, sin ninguna respuesta,
 * con más de N días y menos de M. Un solo aviso por asignación (columna recordatorio_enviado_at).
 * Un correo por docente aunque tenga varias. Simula por defecto; --send para enviar.
 */
class RecordarAsignacionesSinRespuestas extends Command
{
    protected $signature = 'asignaciones:recordar-sin-respuestas
        {--send : Envía de verdad. Sin esta opción solo simula}
        {--min-days=3 : Antigüedad mínima de la asignación}
        {--max-days=30 : Antigüedad máxima de la asignación}
        {--limit=25 : Máximo de docentes avisados por ejecución}
        {--ignore-hours : Permite enviar fuera de 09:00-20:00 Europe/Madrid}';

    protected $description = 'Recuerda a los docentes sus asignaciones sin respuestas del alumnado (un aviso por asignación)';

    public function handle(): int
    {
        $enviar = (bool) $this->option('send');

        if ($enviar && ! $this->option('ignore-hours')) {
            $hora = (int) now('Europe/Madrid')->format('G');
            if ($hora < 9 || $hora >= 20) {
                $this->error('Fuera de horario (09:00-20:00 Europe/Madrid). Usa --ignore-hours si es intencionado.');
                return self::FAILURE;
            }
        }

        $porDocente = AsignacionTest::query()
            ->with(['test', 'grupo', 'profesor'])
            ->whereNull('recordatorio_enviado_at')
            ->where('estado', '!=', 'aplicado')
            ->where('created_at', '<=', now()->subDays((int) $this->option('min-days')))
            ->where('created_at', '>=', now()->subDays((int) $this->option('max-days')))
            ->whereDoesntHave('respuestas')
            ->whereHas('profesor', fn ($q) => $q->whereNotNull('email_verified_at')->where('rol', 'profesor'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('profesor_id')
            ->take(max(0, (int) $this->option('limit')));

        $this->line(sprintf('Docentes a avisar: %d · asignaciones: %d', $porDocente->count(), $porDocente->flatten(1)->count()));

        if (! $enviar) {
            $this->info('Simulación: no se envía nada. Usa --send para enviar.');
            return self::SUCCESS;
        }

        $avisados = 0;
        $fallos = 0;

        foreach ($porDocente as $asignaciones) {
            $docente = $asignaciones->first()->profesor;

            try {
                Mail::to($docente->email)->send(new RecordatorioAsignacionesSinRespuestasMail($docente, $asignaciones));
                AsignacionTest::whereKey($asignaciones->modelKeys())->update(['recordatorio_enviado_at' => now()]);
                $avisados++;
                $fallos = 0;
            } catch (Throwable $e) {
                $this->warn('Fallo de envío (docente '.$docente->id.'): '.class_basename($e));
                if (++$fallos >= 3) {
                    $this->error('3 fallos seguidos del proveedor: parada de seguridad.');
                    return self::FAILURE;
                }
            }
        }

        $this->info("Docentes avisados: {$avisados}.");

        return self::SUCCESS;
    }
}
