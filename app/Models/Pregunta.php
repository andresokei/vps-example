<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    use HasFactory;

    protected $fillable = ['test_id', 'tipo_pregunta', 'texto_pregunta', 'texto_pregunta_en', 'orden'];

    protected $appends = ['localized_texto_pregunta'];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function getLocalizedTextoPreguntaAttribute(): string
    {
        if (App::getLocale() === 'en' && $this->getAttributeFromArray('texto_pregunta_en')) {
            return $this->getAttributeFromArray('texto_pregunta_en');
        }

        return $this->getAttributeFromArray('texto_pregunta');
    }
}
