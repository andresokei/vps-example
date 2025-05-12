<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Respuesta;
use App\Models\Estudiante;

class Relacion extends Model
{
    use HasFactory;

    protected $table = 'relaciones';

    protected $fillable = [
        'asignacion_test_id',
        'alumno_a_id',
        'alumno_b_id',
        'tipo_relacion',
        'intensidad',
        'estado_relacion',
    ];

    /* ───────────────────────────────
     |  Relaciones Eloquent auxiliares
     ─────────────────────────────── */
    public function alumnoA()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_a_id');
    }

    public function alumnoB()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_b_id');
    }

    /* ───────────────────────────────
     |  Generar relaciones desde respuestas
     ─────────────────────────────── */
    public static function generarDesdeRespuestas(int $asignacionId): void
    {
        /* 1 ▸ Borrar cualquier análisis previo de esta asignación */
        static::where('asignacion_test_id', $asignacionId)->delete();

        /* 2 ▸ Cargar TODAS las respuestas con su pregunta para
               saber si era de preferencia o de rechazo            */
        $resps = Respuesta::with('pregunta')                     // ← imprescindible
            ->where('asignacion_test_id', $asignacionId)
            ->whereNotNull('respuesta')                          // id del compañero
            ->whereColumn('alumno_id', '<>', 'respuesta')        // sin auto‑selección
            ->get();

        /* 3 ▸ Mantendremos solo la mejor relación A‑B             */
        $best = [];

        foreach ($resps as $r) {
            // Si por algún motivo la relación con pregunta falla, la saltamos
            if (!$r->relationLoaded('pregunta') || !$r->pregunta) {
                continue;
            }

            /* ─ Tipos de pregunta y relación ─ */
            $tipoPregunta = $r->pregunta->tipo_pregunta;         // 'preferencia' | 'rechazo'
            $tipoRelacion = $tipoPregunta === 'rechazo'
                ? 'rechazado'
                : 'preferido';

            /* ─ Datos base ─ */
            $emisor     = (int) $r->alumno_id;
            $receptor   = (int) $r->respuesta;
            $intensidad = $r->orden_preferencia ?? 3;            // 1‒3 o default

            /* ─ Priorización ─
               Preferidos antes que rechazados (prio 1 < 2)
               y dentro del mismo tipo la menor intensidad gana */
            $prio = $tipoRelacion === 'preferido' ? 1 : 2;

            $key = $emisor . '-' . $receptor;

            if (
                !isset($best[$key]) ||
                $prio <  $best[$key]['prio'] ||
                ($prio === $best[$key]['prio'] && $intensidad < $best[$key]['intensidad'])
            ) {
                $best[$key] = [
                    'alumno_a_id'   => $emisor,
                    'alumno_b_id'   => $receptor,
                    'tipo_relacion' => $tipoRelacion,
                    'intensidad'    => $intensidad,
                    'prio'          => $prio,
                ];
            }
        }

        /* 4 ▸ Inserción bulk de las relaciones consolidadas       */
        if (!empty($best)) {
            static::insert(
                collect($best)->map(fn ($row) => [
                    'asignacion_test_id' => $asignacionId,
                    'alumno_a_id'        => $row['alumno_a_id'],
                    'alumno_b_id'        => $row['alumno_b_id'],
                    'tipo_relacion'      => $row['tipo_relacion'],
                    'intensidad'         => $row['intensidad'],
                    'estado_relacion'    => 'activa',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ])->values()->all()
            );
        }
    }
}
