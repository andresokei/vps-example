<?php

namespace App\Observers;

use App\Models\Respuesta;

class RespuestaObserver
{
    // se dispara inmediatamente después de INSERT
    public function created(Respuesta $respuesta): void
    {
        $respuesta->asignacion->recalcularEstado();
    }

    // Opcional: si permites borrar o editar respuestas
    public function deleted(Respuesta $respuesta): void
    {
        $respuesta->asignacion->recalcularEstado();
    }
}
