<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relacion extends Model
{
    use HasFactory;

    protected $table = 'relaciones'; // Asegúrate de que el nombre de la tabla sea correcto

    // Atributos que se pueden asignar masivamente
    protected $fillable = [
        'asignacion_test_id',  // Agrega este campo para permitir la asignación masiva
        'alumno_a_id',
        'alumno_b_id',
        'tipo_relacion',
        'intensidad',
        'estado_relacion',
    ];

    // Si tienes relaciones con otras tablas (como `alumnoA` y `alumnoB`):
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
    // 1. Vacía las relaciones previas de este test
    static::where('asignacion_test_id', $asignacionId)->delete();

    // 2. Trae TODAS las respuestas de preferencia / rechazo,
    //    ignorando self-loops y vacíos
    $resps = Respuesta::where('asignacion_test_id', $asignacionId)
        ->whereIn('tipo_relacion', ['preferencia', 'rechazo'])
        ->whereNotNull('respuesta')
        ->whereColumn('alumno_id', '<>', 'respuesta')
        ->get();

    // 3. Colección condensada: clave "A-B"  → mejor registro
    $best = [];

    foreach ($resps as $r) {
        $emisor    = $r->alumno_id;        // A
        $receptor  = (int) $r->respuesta;  // B (id del compañero)
        $tipo      = $r->tipo_relacion === 'preferencia' ? 'preferido' : 'rechazado';
        $prio      = $tipo === 'preferido' ? 1 : 2;               // pref gana a rech
        $intensidad= $r->orden_preferencia ?? 3;                  // 1,2,3

        $key = $emisor.'-'.$receptor;

        if (
            !isset($best[$key]) ||                                 // primera vez
            $prio <  $best[$key]['prio'] ||                       // preferido > rechazado
            ($prio === $best[$key]['prio'] &&
             $intensidad < $best[$key]['intensidad'])             // mejor posición
        ) {
            // guardamos / sustituimos si tiene más prioridad
            $best[$key] = [
                'alumno_a_id'  => $emisor,
                'alumno_b_id'  => $receptor,
                'tipo_relacion'=> $tipo,        // preferido | rechazado
                'intensidad'   => $intensidad,  // 1–3
                'prio'         => $prio,
            ];
        }
    }

    // 4. Inserta los registros condensados
    static::insert(
        collect($best)->map(fn ($row) => [
            'asignacion_test_id' => $asignacionId,
            'alumno_a_id'        => $row['alumno_a_id'],
            'alumno_b_id'        => $row['alumno_b_id'],
            'tipo_relacion'      => $row['tipo_relacion'],
            'intensidad'         => $row['intensidad'],
            'created_at'         => now(),
            'updated_at'         => now(),
        ])->all()
    );
}

   








}
