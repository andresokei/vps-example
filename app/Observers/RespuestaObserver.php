<?php

namespace App\Observers;

use App\Models\Respuesta;
use Illuminate\Support\Facades\Log;

class RespuestaObserver
{
    // se dispara inmediatamente después de INSERT
    public function created(Respuesta $respuesta): void
    {
        $estadoAnterior = $respuesta->asignacion->estado;
        $respuesta->asignacion->recalcularEstado();
        $estadoNuevo = $respuesta->asignacion->fresh()->estado;

        Log::info('Respuesta registrada', [
            'asignacion_test_id' => $respuesta->asignacion_test_id,
            'alumno_id' => $respuesta->alumno_id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
        ]);
    }

    // Opcional: si permites borrar o editar respuestas
    public function deleted(Respuesta $respuesta): void
    {
        $estadoAnterior = $respuesta->asignacion->estado;
        $respuesta->asignacion->recalcularEstado();

        Log::info('Respuesta eliminada', [
            'asignacion_test_id' => $respuesta->asignacion_test_id,
            'alumno_id' => $respuesta->alumno_id,
            'estado_anterior' => $estadoAnterior,
        ]);
    }
}
