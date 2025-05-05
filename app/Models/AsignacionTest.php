<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionTest extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_test';

    protected $fillable = [
        'test_id',
        'grupo_id',
        'profesor_id',
        'clave_acceso',
        'estado',
    ];

    public $timestamps = true;


    /* ─────── Relaciones ───────────────────────────────────────────── */

    public function test()      { return $this->belongsTo(Test::class); }

    public function profesor()  { return $this->belongsTo(User::class, 'profesor_id'); }

    public function grupo()     { return $this->belongsTo(Grupo::class); }

    /**  
     * Respuestas asociadas a esta asignación  
     * (ajusta el nombre del modelo si tu tabla se llama distinto).  
     */
    public function respuestas()
    {
        return $this->hasMany(Respuesta::class, 'asignacion_test_id');
    }


    /* ─────── Sincronizar estado ───────────────────────────────────── */

    /**
     * Calcula y graba el nuevo estado:
     * 'pendiente'   →  nadie contestó  
     * 'en_progreso' →  algunos contestaron  
     * 'realizado'   →  todos contestaron
     */
    public function recalcularEstado(): void
    {
        // nº alumnos del grupo
        $totalAlumnos = $this->grupo->estudiantes()->count();

        // nº alumnos que ya enviaron (distinct por alumno)
        $respondidos  = $this->respuestas()
                             ->distinct('alumno_id')      // o estudiante_id, según tu BD
                             ->count('alumno_id');

        $nuevoEstado = match (true) {
            $respondidos === 0            => 'pendiente',
            $respondidos <  $totalAlumnos => 'en progreso',
            default                       => 'aplicado',
        };

        // evita UPDATE innecesario
        if ($this->estado !== $nuevoEstado) {
            $this->estado = $nuevoEstado;
            $this->save();
        }
    }
}
