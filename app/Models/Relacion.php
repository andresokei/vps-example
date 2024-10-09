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
}
