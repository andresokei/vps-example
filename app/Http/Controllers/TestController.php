<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionesTest;
use App\Models\Test;

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

        return redirect()->route('test.realizar', ['id' => $asignacion->test_id]);
    }

    // Método para mostrar el test correspondiente
    // Método para mostrar el test correspondiente
    public function mostrarTest($id)
    {
        $test = Test::with('preguntas')->findOrFail($id);
    
        if ($test->preguntas->isEmpty()) {
            return back()->with('warning', 'Este test no tiene preguntas asociadas.');
        }
    
        return view('alumnos.realizar-test', compact('test'));
    }
}
