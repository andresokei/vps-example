<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    use HasFactory;

    protected $fillable = ['test_id', 'tipo_pregunta', 'texto_pregunta'];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}