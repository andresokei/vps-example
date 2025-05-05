<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionTest;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    // Método para mostrar el formulario de ingreso de clave
    public function mostrarTestForm()
    {
        // Retorna la vista con el formulario para ingresar la clave
        return view('alumnos.ingresar-clave');
    }

    // Método para verificar la clave de acceso
    public function verificarClave(Request $request)
    {
        $request->validate(['clave_acceso' => 'required|string']);
        
        \Log::info('Verificando clave: ' . $request->clave_acceso);
        
        // Buscar la asignación que coincida con la clave
        $asignacion = AsignacionTest::where('clave_acceso', $request->clave_acceso)
            ->where('estado', 'pendiente')
            ->first();
        
        if (!$asignacion) {
            \Log::error('Clave no encontrada o test no disponible');
            return back()->withErrors(['clave_acceso' => 'Clave de acceso inválida o test no disponible.']);
        }
        
        \Log::info('Asignación encontrada. ID: ' . $asignacion->id . ', Grupo ID: ' . $asignacion->grupo_id);
        
        // Obtener estudiantes del grupo
        $estudiantes = DB::table('estudiantes_grupos as eg')
            ->join('estudiantes as e', 'eg.id_estudiante', '=', 'e.id')
            ->where('eg.id_grupo', $asignacion->grupo_id)
            ->select('e.*')
            ->get();
        
        \Log::info('Estudiantes encontrados: ' . count($estudiantes));
        
        if ($estudiantes->isEmpty()) {
            \Log::error('No hay estudiantes en el grupo ' . $asignacion->grupo_id);
            return back()->withErrors(['error' => 'Este grupo no tiene estudiantes asignados.']);
        }
        
        // Guardar en sesión
        session(['estudiantes' => $estudiantes]);
        
        // Redireccionar al test
        return redirect()->route('test.realizar', [
            'id' => $asignacion->test_id,
            'asignacion_id' => $asignacion->id,
        ])->with(['estudiantes' => $estudiantes]);
    }

    // Método para mostrar el test correspondiente
    // Método para mostrar el test correspondiente
    public function mostrarTest($id, $asignacion_id)
    {
        $test = Test::with('preguntas')->findOrFail($id);

        if ($test->preguntas->isEmpty()) {
            return back()->with('warning', 'Este test no tiene preguntas asociadas.');
        }

        // Recuperar los estudiantes de la sesión
        $estudiantes = session('estudiantes', []);

        // Pasar también el asignacion_id a la vista
        return view('alumnos.realizar-test', compact('test', 'estudiantes', 'asignacion_id'));
    }

    public function submitTest(Request $request, $id)
    {
        // 1) Datos básicos
        $asignacion_id = $request->input('asignacion_id');
        $asignacion    = AsignacionTest::findOrFail($asignacion_id);
    
        // 2) Validación
        $request->validate([
            'estudiante_id' => 'required',
            'respuesta_*'   => 'required',
        ]);
    
        // 3) Guardar cada respuesta
        foreach ($asignacion->test->preguntas as $pregunta) {
            for ($i = 1; $i <= 3; $i++) {
                $respuesta     = $request->input('respuesta_'.$pregunta->id.'_'.$i);
                $tipoRelacion  = $request->input('tipo_relacion_'.$pregunta->id);
    
                DB::table('respuestas')->insert([
                    'alumno_id'           => $request->estudiante_id,
                    'asignacion_test_id'  => $asignacion->id,
                    'pregunta_id'         => $pregunta->id,
                    'respuesta'           => $respuesta,
                    'orden_preferencia'   => $i,
                    'tipo_relacion'       => $tipoRelacion,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
    
                DB::table('relaciones')->insert([
                    'asignacion_test_id'  => $asignacion->id,
                    'alumno_a_id'         => $request->estudiante_id,
                    'alumno_b_id'         => $respuesta,
                    'tipo_relacion'       => $tipoRelacion === 'preferencia' ? 'preferido' : 'rechazado',
                    'intensidad'          => $i,
                    'estado_relacion'     => 'activa',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    
        /* ─────── LÍNEA CLAVE ─────── */
        $asignacion->recalcularEstado();   // ← actualiza la columna `estado`
        /* ──────────────────────────── */
    
        return redirect()
               ->route('test.success')
               ->with('status', 'Respuestas y relaciones guardadas exitosamente');
    }
    

}
