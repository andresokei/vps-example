<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $table = 'respuestas';

    protected $fillable = [
        'alumno_id',
        'asignacion_test_id',
        'pregunta_id',
        'respuesta',
        'orden_preferencia',
        'tipo_relacion',
        'created_at',
        'updated_at',
    ];

    // Si tienes timestamps automáticos
    public $timestamps = true;

    // Define las relaciones si es necesario
    public function alumno()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_id');
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }

    public function asignacion()
{
    //               Modelo             clave foránea en la tabla `respuestas`
    return $this->belongsTo(AsignacionTest::class, 'asignacion_test_id');
}
}
