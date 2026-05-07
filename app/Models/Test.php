<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    // Define el nombre de la tabla si no sigue la convención
    protected $table = 'tests';

    // Define los campos que se pueden llenar
    protected $fillable = ['nombre_test', 'nombre_test_en', 'descripcion', 'descripcion_en', 'id_profesor'];

    protected $appends = ['localized_nombre_test', 'localized_descripcion'];

    // Define la relación con Pregunta
    public function preguntas()
    {
        return $this->hasMany(Pregunta::class)->orderBy('orden');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'id_profesor');
    }

    public function getLocalizedNombreTestAttribute(): string
    {
        return $this->localizedValue('nombre_test');
    }

    public function getLocalizedDescripcionAttribute(): ?string
    {
        return $this->localizedValue('descripcion');
    }

    private function localizedValue(string $field): ?string
    {
        $localizedField = App::getLocale() === 'en' ? "{$field}_en" : $field;
        $localizedValue = $this->getAttributeFromArray($localizedField);
        $fallbackValue = $this->getAttributeFromArray($field);

        return $localizedValue ?: $fallbackValue;
    }
}
