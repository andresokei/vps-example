<?php

namespace App\Observers;

use App\Models\Respuesta;
use App\Services\AnalisisGrupalService;
use Illuminate\Support\Facades\Log;

class RespuestaObserver
{
    // se dispara inmediatamente después de INSERT
    public function created(Respuesta $respuesta): void
    {
        $estadoAnterior = $respuesta->asignacion->estado;
        $respuesta->asignacion->recalcularEstado();
        app(AnalisisGrupalService::class)->invalidateForAssignmentId((int) $respuesta->asignacion_test_id);
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
        app(AnalisisGrupalService::class)->invalidateForAssignmentId((int) $respuesta->asignacion_test_id);

        Log::info('Respuesta eliminada', [
            'asignacion_test_id' => $respuesta->asignacion_test_id,
            'alumno_id' => $respuesta->alumno_id,
            'estado_anterior' => $estadoAnterior,
        ]);
    }
}
