<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionTest extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_test'; // Asegúrate de que el nombre de la tabla sea correcto

    protected $fillable = [
        'test_id',
        'profesor_id',
        'grupo_id',
        'clave_acceso',
        'estado',
        'created_at',
        'updated_at',
    ];

    // Definir relaciones si es necesario
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }
}
