<?php

namespace App\Console\Commands;

use App\Mail\RecuperacionVerificacionMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Reenvía el correo de verificación, en lotes pequeños, a cuentas que quedaron sin verificar
 * mientras el envío de correo estaba roto. Un solo recordatorio por cuenta.
 *
 * Seguridad operativa:
 *  - Por defecto NO envía nada: hay que pasar --send. Sin él, simula y lista cifras (nunca emails).
 *  - Registro append-only (JSONL) escrito ANTES de llamar al proveedor; una cuenta presente en el
 *    registro no vuelve a recibir envío jamás (compatible con el JSONL del piloto del 30/09/2026).
 *  - Tope por ejecución y tope diario, y solo en horario diurno de Madrid.
 *  - Se detiene tras fallos consecutivos del proveedor.
 *  - No mira rebotes (eso vive en Resend): revisa el panel entre tandas y excluye con --exclude-id.
 */
class ReenviarVerificacion extends Command
{
    protected $signature = 'verificacion:reenviar
        {--send : Envía de verdad. Sin esta opción solo simula}
        {--limit=20 : Máximo de envíos en esta ejecución}
        {--max-per-day=30 : Máximo de intentos por día UTC contando el registro}
        {--from=2026-07-19T18:42:13 : Fecha mínima de alta (UTC)}
        {--to=2026-09-29T18:41:01 : Fecha máxima de alta (UTC)}
        {--oldest-first : Empieza por las altas más antiguas (por defecto, las más recientes)}
        {--hours=48 : Validez del enlace en horas}
        {--exclude-id=* : IDs a excluir (p. ej. rebotes)}
        {--log= : Ruta del registro JSONL (por defecto storage/app/recuperacion-verificacion-20260930.jsonl)}
        {--ignore-hours : Permite enviar fuera de 09:00-20:00 Europe/Madrid}';

    protected $description = 'Reenvía en lotes pequeños la verificación de correo a cuentas afectadas por el fallo de envío';

    private const DOMINIOS_DESECHABLES = [
        'mailinator.com', 'tempmail.com', 'temp-mail.org', '10minutemail.com', 'guerrillamail.com',
        'yopmail.com', 'trashmail.com', 'sharklasers.com', 'getnada.com', 'dispostable.com',
    ];

    public function handle(): int
    {
        $enviar = (bool) $this->option('send');
        $limite = max(0, (int) $this->option('limit'));
        $maxDia = max(0, (int) $this->option('max-per-day'));
        $horas = max(1, (int) $this->option('hours'));
        $logPath = $this->option('log') ?: storage_path('app/recuperacion-verificacion-20260930.jsonl');

        if ($enviar && ! $this->option('ignore-hours') && ! $this->enHorarioDeEnvio()) {
            $this->error('Fuera de horario (09:00-20:00 Europe/Madrid). Usa --ignore-hours si es intencionado.');
            return self::FAILURE;
        }

        [$intentados, $hoy] = $this->leerRegistro($logPath);
        $plazas = min($limite, max(0, $maxDia - $hoy));

        $query = User::query()
            ->whereNull('email_verified_at')
            ->whereBetween('created_at', [Carbon::parse($this->option('from'), 'UTC'), Carbon::parse($this->option('to'), 'UTC')])
            ->when($intentados || $this->option('exclude-id'), fn ($q) => $q->whereNotIn(
                'id', array_values(array_unique(array_merge($intentados, array_map('intval', (array) $this->option('exclude-id')))))
            ))
            ->orderBy('created_at', $this->option('oldest-first') ? 'asc' : 'desc')
            ->orderBy('id', $this->option('oldest-first') ? 'asc' : 'desc');

        $descartadas = 0;
        $candidatas = [];
        $pendientesTotal = 0;

        foreach ($query->cursor() as $user) {
            if (! $this->emailAdecuado($user->email)) {
                $descartadas++;
                continue;
            }
            $pendientesTotal++;
            if (count($candidatas) < $plazas) {
                $candidatas[] = $user;
            }
        }

        $this->line(sprintf(
            'Candidatas válidas: %d · descartadas (email inválido o desechable): %d · ya intentadas: %d · plazas hoy: %d',
            $pendientesTotal, $descartadas, count($intentados), $plazas
        ));

        if (! $enviar) {
            $this->info(sprintf('Simulación: se enviarían %d correos. Usa --send para enviar.', count($candidatas)));
            return self::SUCCESS;
        }

        $enviados = 0;
        $fallosSeguidos = 0;

        foreach ($candidatas as $user) {
            // Primero el registro, después el proveedor: si algo falla no se reintenta solo.
            $this->registrar($logPath, ['id' => $user->id, 'status' => 'attempted', 'at' => now('UTC')->toIso8601String(), 'by' => 'verificacion:reenviar']);

            try {
                Mail::to($user->email)->send(new RecuperacionVerificacionMail($user, $horas));
                $enviados++;
                $fallosSeguidos = 0;
            } catch (Throwable $e) {
                $fallosSeguidos++;
                $this->registrar($logPath, ['id' => $user->id, 'status' => 'failed', 'at' => now('UTC')->toIso8601String(), 'error' => Str::limit(class_basename($e), 60, '')]);
                $this->warn("Fallo de envío (id {$user->id}): " . class_basename($e));

                if ($fallosSeguidos >= 3) {
                    $this->error('3 fallos seguidos del proveedor: parada de seguridad.');
                    return self::FAILURE;
                }
            }
        }

        $this->info("Enviados: {$enviados}. Revisa rebotes en Resend antes de la siguiente tanda.");

        return self::SUCCESS;
    }

    private function enHorarioDeEnvio(): bool
    {
        $hora = (int) now('Europe/Madrid')->format('G');

        return $hora >= 9 && $hora < 20;
    }

    private function emailAdecuado(?string $email): bool
    {
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $dominio = Str::lower(Str::after($email, '@'));

        return ! in_array($dominio, self::DOMINIOS_DESECHABLES, true);
    }

    /** @return array{0: int[], 1: int} IDs ya intentados e intentos de hoy (UTC). */
    private function leerRegistro(string $path): array
    {
        if (! is_file($path)) {
            return [[], 0];
        }

        $ids = [];
        $hoy = 0;
        $hoyUtc = now('UTC')->toDateString();

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $fila = json_decode($linea, true);
            if (! is_array($fila) || ! isset($fila['id'])) {
                continue;
            }
            $ids[] = (int) $fila['id'];
            if (($fila['status'] ?? '') === 'attempted' && str_starts_with((string) ($fila['at'] ?? ''), $hoyUtc)) {
                $hoy++;
            }
        }

        return [array_values(array_unique($ids)), $hoy];
    }

    private function registrar(string $path, array $fila): void
    {
        file_put_contents($path, json_encode($fila, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
