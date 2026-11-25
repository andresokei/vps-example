<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Detecta el fallo silencioso de agosto-septiembre de 2026: altas nuevas y ninguna verificación.
 * Sale con código 1 y avisa al admin (config app.admin_email) si en la ventana hay >= --min altas y 0 verificaciones.
 * Importante: solo se ejecuta sola si el servidor lanza `php artisan schedule:run` cada minuto (cron).
 */
class VigilarVerificacion extends Command
{
    protected $signature = 'verificacion:vigilar {--hours=24 : Ventana en horas} {--min=5 : Altas mínimas para considerar el fallo} {--no-mail : No enviar aviso}';

    protected $description = 'Avisa si hay altas recientes pero ninguna verificación de correo (posible fallo de envío)';

    public function handle(): int
    {
        $desde = now()->subHours((int) $this->option('hours'));
        $altas = User::where('created_at', '>=', $desde)->count();
        $verificadas = User::where('created_at', '>=', $desde)->whereNotNull('email_verified_at')->count();

        $this->line("Ventana {$this->option('hours')} h: {$altas} altas, {$verificadas} verificadas.");

        if ($altas < (int) $this->option('min') || $verificadas > 0) {
            return self::SUCCESS;
        }

        $this->error('Posible fallo de envío de verificación: altas sin ninguna verificación.');

        $admin = config('app.admin_email');
        if ($admin && ! $this->option('no-mail')) {
            Mail::raw(
                "Sociogram: en las últimas {$this->option('hours')} h hay {$altas} altas y 0 correos verificados. Revisa el proveedor de correo (Resend) y el dominio remitente.",
                fn ($m) => $m->to($admin)->subject('Alerta: no se están verificando cuentas')
            );
        }

        return self::FAILURE;
    }
}
