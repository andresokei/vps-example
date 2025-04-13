<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relacion extends Model
{
    use HasFactory;

    protected $table = 'relaciones'; // Asegúrate de que el nombre de la tabla sea correcto

    // Atributos que se pueden asignar masivamente
    protected $fillable = [
        'asignacion_test_id',  // Agrega este campo para permitir la asignación masiva
        'alumno_a_id',
        'alumno_b_id',
        'tipo_relacion',
        'intensidad',
        'estado_relacion',
    ];

    // Si tienes relaciones con otras tablas (como `alumnoA` y `alumnoB`):
    public function alumnoA()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_a_id');
    }

    public function alumnoB()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_b_id');
    }





    public static function generarDesdeRespuestas($asignacionTestId)
    {
        \Log::info("🔄 Procesando asignación con ID: {$asignacionTestId}");
        \Log::info('💡 Se está ejecutando el método generarDesdeRespuestas');
    
        // Eliminar relaciones anteriores para esa asignación
        self::where('asignacion_test_id', $asignacionTestId)->delete();
    
        $respuestas = \App\Models\Respuesta::where('asignacion_test_id', $asignacionTestId)->get();
        
        \Log::info("🔍 Total de respuestas encontradas: " . count($respuestas));
        $contadorAutoSeleccion = 0;
        $contadorTipoInvalido = 0;
        $contadorIdInvalido = 0;
        $contador = 0;
    
        // Obtener todos los IDs de estudiantes válidos
        $estudiantesValidos = \App\Models\Estudiante::pluck('id')->toArray();
        \Log::info("📊 IDs de estudiantes válidos: " . implode(', ', $estudiantesValidos));
    
        foreach ($respuestas as $respuesta) {
            \Log::info("📝 Procesando respuesta ID={$respuesta->id}: alumno_id={$respuesta->alumno_id}, respuesta={$respuesta->respuesta}, tipo={$respuesta->tipo_relacion}");
            
            // Verificar que ambos IDs de estudiantes existen
            if (!in_array((int) $respuesta->alumno_id, $estudiantesValidos)) {
                \Log::warning("⚠️ ID de alumno_a inválido: {$respuesta->alumno_id}");
                $contadorIdInvalido++;
                continue;
            }
            
            if (!in_array((int) $respuesta->respuesta, $estudiantesValidos)) {
                \Log::warning("⚠️ ID de alumno_b (respuesta) inválido: {$respuesta->respuesta}");
                $contadorIdInvalido++;
                continue;
            }
            
            // Saltar si el alumno se eligió a sí mismo
            if ((int) $respuesta->alumno_id === (int) $respuesta->respuesta) {
                \Log::info("⚠️ Se detectó auto-selección: alumno_id={$respuesta->alumno_id}, respuesta={$respuesta->respuesta}");
                $contadorAutoSeleccion++;
                continue;
            }
    
            // Normalizar tipo de relación
            $tipoOriginal = strtolower(trim($respuesta->tipo_relacion));
    
            $tipoRelacion = match ($tipoOriginal) {
                'preferencia' => 'preferido',
                'rechazo' => 'rechazado',
                default => null,
            };
    
            \Log::info("🔎 Tipo relación bruta: '{$respuesta->tipo_relacion}' → '{$tipoRelacion}'");
    
            if (!$tipoRelacion) {
                \Log::info("⚠️ Tipo de relación no válido: '{$respuesta->tipo_relacion}'");
                $contadorTipoInvalido++;
                continue;
            }
    
            try {
                self::create([
                    'asignacion_test_id' => $asignacionTestId,
                    'alumno_a_id' => $respuesta->alumno_id,
                    'alumno_b_id' => $respuesta->respuesta,
                    'tipo_relacion' => $tipoRelacion,
                    'intensidad' => $respuesta->orden_preferencia ?? 1,
                    'estado_relacion' => 'activa',
                ]);
    
                \Log::info("🧩 Creando relación: {$respuesta->alumno_id} → {$respuesta->respuesta} como {$tipoRelacion}");
                $contador++;
            } catch (\Exception $e) {
                \Log::error("❌ Error al crear relación: " . $e->getMessage());
            }
        }
    
        \Log::info("ℹ️ Auto-selecciones detectadas: {$contadorAutoSeleccion}");
        \Log::info("ℹ️ Tipos de relación no válidos: {$contadorTipoInvalido}");
        \Log::info("ℹ️ IDs de estudiantes inválidos: {$contadorIdInvalido}");
        \Log::info("✅ Total de relaciones generadas: {$contador}");
    }
   








}
