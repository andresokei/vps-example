<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    // Define el nombre de la tabla si no sigue la convención
    protected $table = 'tests';

    // Define los campos que se pueden llenar
    protected $fillable = ['nombre_test', 'descripcion', 'id_profesor'];

    // Define la relación con Pregunta
    public function preguntas()
    {
        return $this->hasMany(Pregunta::class)->orderBy('orden');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'id_profesor');
    }
}