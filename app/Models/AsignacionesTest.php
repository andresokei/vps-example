<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionesTest extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_test'; // Asegúrate de especificar el nombre correcto de la tabla.
    
    protected $fillable = [
        'test_id', 
        'profesor_id', 
        'grupo_id', 
        'clave_acceso', 
        'estado'
    ];

    // Relación con el modelo Test
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    // Relación con el modelo Grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
