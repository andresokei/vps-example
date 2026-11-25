<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioAsignacionesSinRespuestasMail;
use App\Models\AsignacionTest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Avisa a cada docente de SUS asignaciones abiertas, sin ninguna respuesta, de entre N y M días.
 *
 * Protección de reputación del remitente:
 *  - Solo docentes con correo verificado (la dirección ya ha recibido y abierto correo nuestro).
 *  - Escalado automático: el primer día se avisa como mucho a 10 docentes; el tope sube 5 por día
 *    desde el primer envío hasta --max-daily (40 por defecto).
 *  - Pausa entre correos, parada tras 3 fallos seguidos del proveedor, solo en horario diurno de Madrid.
 *  - Un aviso por asignación y descanso de 14 días por docente.
 *  - Baja con un clic (cabecera List-Unsubscribe + enlace firmado); quien se da de baja no vuelve a recibirlo.
 *
 * Simula por defecto; hay que pasar --send.
 */
class RecordarAsignacionesSinRespuestas extends Command
{
    protected $signature = 'asignaciones:recordar-sin-respuestas
        {--send : Envía de verdad. Sin esta opción solo simula}
        {--min-days=3 : Antigüedad mínima de la asignación}
        {--max-days=30 : Antigüedad máxima de la asignación}
        {--max-daily=40 : Techo del tope diario de docentes avisados}
        {--pause=2 : Segundos de pausa entre correos}
        {--ignore-hours : Permite enviar fuera de 09:00-20:00 Europe/Madrid}';

    protected $description = 'Recuerda a los docentes sus asignaciones sin respuestas del alumnado (un aviso por asignación)';

    private const INICIO_DIARIO = 10;
    private const SUBIDA_POR_DIA = 5;
    private const DESCANSO_DIAS = 14;

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

        $plazas = $this->plazasHoy((int) $this->option('max-daily'));

        $candidatas = AsignacionTest::query()
            ->with(['test', 'grupo', 'profesor'])
            ->whereNull('recordatorio_enviado_at')
            ->where('estado', '!=', 'aplicado')
            ->where('created_at', '<=', now()->subDays((int) $this->option('min-days')))
            ->where('created_at', '>=', now()->subDays((int) $this->option('max-days')))
            ->whereDoesntHave('respuestas')
            ->whereHas('profesor', fn ($q) => $q
                ->whereNotNull('email_verified_at')
                ->whereNull('recordatorios_desactivados_at')
                ->where('rol', 'profesor'))
            // Descanso: ningún aviso a este docente en los últimos N días.
            ->whereNotIn('profesor_id', AsignacionTest::query()
                ->select('profesor_id')
                ->where('recordatorio_enviado_at', '>=', now()->subDays(self::DESCANSO_DIAS)))
            ->orderBy('created_at')
            ->get();

        $porDocente = $candidatas->groupBy('profesor_id');
        $totalDocentes = $porDocente->count();
        $porDocente = $porDocente->take($plazas);

        $this->line(sprintf(
            'Docentes elegibles: %d · plazas hoy: %d · a avisar ahora: %d',
            $totalDocentes, $plazas, $porDocente->count()
        ));

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

            if ((int) $this->option('pause') > 0) {
                sleep((int) $this->option('pause'));
            }
        }

        $this->info("Docentes avisados: {$avisados}.");

        return self::SUCCESS;
    }

    /** Tope de docentes avisables hoy: sube con los días desde el primer envío y descuenta lo ya enviado hoy. */
    private function plazasHoy(int $techo): int
    {
        $primero = AsignacionTest::whereNotNull('recordatorio_enviado_at')->min('recordatorio_enviado_at');
        $dias = $primero ? (int) now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($primero)->startOfDay()) : 0;
        $tope = min($techo, self::INICIO_DIARIO + self::SUBIDA_POR_DIA * $dias);

        $hoy = AsignacionTest::where('recordatorio_enviado_at', '>=', now()->startOfDay())
            ->distinct('profesor_id')->count('profesor_id');

        return max(0, $tope - $hoy);
    }
}
