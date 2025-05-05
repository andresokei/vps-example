<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionTest extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_test';

    // Sólo los campos que RELLENAS tú manualmente
    protected $fillable = [
        'test_id',
        'grupo_id',
        'profesor_id',    // si vas a guardar aquí Auth::id()
        'clave_acceso',
        'estado',
    ];

    // timestamps quedan gestionados automáticamente
    public $timestamps = true;

    // Relaciones
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
