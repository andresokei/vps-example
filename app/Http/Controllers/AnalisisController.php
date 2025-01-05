<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AsignacionTest;
use App\Models\Grupo; // Asegúrate de importar el modelo Grupo

class AnalisisController extends Controller
{
    public function index()
{
    // Obtener los grupos asociados al profesor autenticado
    $grupos = Grupo::where('id_profesor', Auth::user()->id)->get();

    // Obtener la asignación de test para el profesor autenticado
    $asignacionTest = AsignacionTest::where('profesor_id', Auth::user()->id)->first();
    $asignacionTestId = $asignacionTest ? $asignacionTest->id : null;

    return view('analisis.index', compact('grupos', 'asignacionTestId'));
}

}
