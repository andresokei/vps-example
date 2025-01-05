<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Estudiante;
use App\Models\User;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $fillable = ['nombre_grupo', 'id_profesor'];

    // Relación muchos a muchos con Estudiantes
    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiantes_grupos', 'id_grupo', 'id_estudiante');
    }
    
    public function profesor()
    {
        return $this->belongsTo(User::class, 'id_profesor');
    }
}
