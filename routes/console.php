<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Requiere cron en el servidor: * * * * * cd /var/www/vps-example && php artisan schedule:run
Schedule::command('verificacion:vigilar')->dailyAt('10:00')->timezone('Europe/Madrid');

// Avisos a docentes: apagado por defecto. Se activa con RECORDATORIOS_ASIGNACIONES=true en .env (tras migrar y probar en simulación).
Schedule::command('asignaciones:recordar-sin-respuestas --send')
    ->weekdays()->at('10:30')->timezone('Europe/Madrid')
    ->withoutOverlapping()
    ->when(fn () => (bool) config('app.recordatorios_asignaciones'));
