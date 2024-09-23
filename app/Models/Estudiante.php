<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Grupo;

class Estudiante extends Model
{
    protected $table = 'estudiantes';
    protected $fillable = ['nombre', 'id_profesor'];

    // Relación muchos a muchos con Grupos
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'estudiantes_grupos', 'id_estudiante', 'id_grupo');
    }
}
