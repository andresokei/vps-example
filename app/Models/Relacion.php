<?php

namespace App\Models;

use App\Services\AnalisisGrupalService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'pregunta_id',
    ];

    public function alumnoA()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_a_id');
    }

    public function alumnoB()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_b_id');
    }

    public static function generarDesdeRespuestas(int $asignacionId): void
    {
        static::where('asignacion_test_id', $asignacionId)->delete();

        $respuestas = Respuesta::with('pregunta')
            ->where('asignacion_test_id', $asignacionId)
            ->whereNotNull('respuesta')
            ->whereColumn('alumno_id', '<>', 'respuesta')
            ->get();

        $best = [];

        foreach ($respuestas as $respuesta) {
            if (! $respuesta->relationLoaded('pregunta') || ! $respuesta->pregunta) {
                continue;
            }

            $tipoRelacion = $respuesta->pregunta->tipo_pregunta === 'rechazo'
                ? 'rechazado'
                : 'preferido';

            $emisor = (int) $respuesta->alumno_id;
            $receptor = (int) $respuesta->respuesta;
            $intensidad = (int) ($respuesta->orden_preferencia ?? 3);
            $preguntaId = (int) $respuesta->pregunta_id;
            $key = $emisor.'-'.$receptor.'-'.$tipoRelacion;

            if (
                ! isset($best[$key]) ||
                $intensidad < $best[$key]['intensidad']
            ) {
                $best[$key] = [
                    'alumno_a_id' => $emisor,
                    'alumno_b_id' => $receptor,
                    'tipo_relacion' => $tipoRelacion,
                    'intensidad' => $intensidad,
                    'pregunta_id' => $preguntaId,
                ];
            }
        }

        if ($best !== []) {
            static::insert(
                collect($best)->map(fn ($row) => [
                    'asignacion_test_id' => $asignacionId,
                    'alumno_a_id' => $row['alumno_a_id'],
                    'alumno_b_id' => $row['alumno_b_id'],
                    'tipo_relacion' => $row['tipo_relacion'],
                    'intensidad' => $row['intensidad'],
                    'estado_relacion' => 'activa',
                    'pregunta_id' => $row['pregunta_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->values()->all()
            );
        }

        app(AnalisisGrupalService::class)->invalidateForAssignmentId($asignacionId);
    }
}
