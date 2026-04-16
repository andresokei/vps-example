<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\NotificarNuevoUsuario;
use App\Models\Respuesta;
use App\Observers\RespuestaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // cada vez que se cree o borre una Respuesta,
        // Laravel llamará a los métodos del observer
        Respuesta::observe(RespuestaObserver::class);
        Event::listen(Registered::class, NotificarNuevoUsuario::class);
    }
}
