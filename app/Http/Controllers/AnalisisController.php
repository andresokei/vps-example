<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AsignacionTest;
use App\Models\Grupo;
use App\Services\AnalisisGrupalService;

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

    public function exportarPdf(AsignacionTest $asignacion, AnalisisGrupalService $service)
    {
        // Solo el profesor dueño puede exportar
        abort_unless($asignacion->profesor_id === Auth::id(), 403);

        $asignacion->loadMissing(['grupo', 'test']);

        $analisis = $service->generar($asignacion->grupo_id, $asignacion->id);

        $pdf = Pdf::loadView('pdf.analisis', [
            'analisis' => $analisis,
            'grupo' => $asignacion->grupo->nombre_grupo,
            'test' => $asignacion->test->nombre_test,
        ])->setPaper('a4', 'portrait');

        $filename = 'analisis-' . str($asignacion->grupo->nombre_grupo)->slug() . '-' . $asignacion->id . '.pdf';

        return $pdf->download($filename);
    }
}
