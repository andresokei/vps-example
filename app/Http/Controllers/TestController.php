<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionesTest;
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
        $request->validate([
            'clave_acceso' => 'required|string',
        ]);

        // Buscar la asignación que coincida con la clave de acceso
        $asignacion = AsignacionesTest::where('clave_acceso', $request->clave_acceso)
            ->where('estado', 'pendiente')
            ->first();

        if (!$asignacion) {
            return back()->withErrors(['clave_acceso' => 'Clave de acceso inválida o test no disponible.']);
        }

        // Obtener el grupo al que está asignado el test
        $grupo = $asignacion->grupo;
        $estudiantes = $grupo->students; // Obtener los estudiantes del grupo

        // Almacenar los estudiantes en la sesión o pasarlos directamente
        session(['estudiantes' => $estudiantes]);

        // Redirigir a la vista de realizar el test con el test y el asignacion_id
        return redirect()->route('test.realizar', [
            'id' => $asignacion->test_id, // Pasamos el test_id
            'asignacion_id' => $asignacion->id, // También pasamos el asignacion_id
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
    // Usar el asignacion_id enviado desde el formulario
    $asignacion_id = $request->input('asignacion_id');

    // Buscar la asignación por su ID
    $asignacion = AsignacionesTest::findOrFail($asignacion_id);

    // Validar el input
    $request->validate([
        'estudiante_id' => 'required',
        'respuesta_*' => 'required'
    ]);

    // Iterar sobre las preguntas del test y guardar las respuestas
    foreach ($asignacion->test->preguntas as $pregunta) {
        for ($i = 1; $i <= 3; $i++) {
            $respuesta = $request->input('respuesta_' . $pregunta->id . '_' . $i);
            $tipoRelacion = $request->input('tipo_relacion_' . $pregunta->id);  // Obtener tipo de relación

            // Guardar la respuesta en la base de datos
            DB::table('respuestas')->insert([
                'alumno_id' => $request->estudiante_id,
                'asignacion_test_id' => $asignacion->id,
                'pregunta_id' => $pregunta->id,
                'respuesta' => $respuesta, // ID del alumno relacionado
                'orden_preferencia' => $i,
                'tipo_relacion' => $tipoRelacion,  // Guardar preferencia o rechazo
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Aquí también se guardan las relaciones
            $tipoRelacionGuardada = $tipoRelacion == 'preferencia' ? 'preferido' : 'rechazado';

            // Guardar la relación en la tabla 'relaciones'
            DB::table('relaciones')->insert([
                'asignacion_test_id' => $asignacion->id,
                'alumno_a_id' => $request->estudiante_id, // Estudiante que responde
                'alumno_b_id' => $respuesta, // Estudiante al que se refiere en la respuesta
                'tipo_relacion' => $tipoRelacionGuardada, // preferido o rechazado
                'intensidad' => $i, // Orden de preferencia
                'estado_relacion' => 'activa',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    // Redirigir a la página de éxito
    return redirect()->route('test.success')->with('status', 'Respuestas y relaciones guardadas exitosamente');
}

}
