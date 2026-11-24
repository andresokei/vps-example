<?php

namespace App\Http\Controllers;

use App\Models\AsignacionTest;
use App\Services\AnalisisGrupalService;
use App\Services\VistaSociograma;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AnalisisController extends Controller
{
    public function index()
    {
        return view('analisis.index');
    }

    public function exportarPdf(AsignacionTest $asignacion, AnalisisGrupalService $service)
    {
        abort_unless($asignacion->profesor_id === Auth::id(), 403);

        $asignacion->loadMissing(['grupo', 'test']);

        $analisis = $service->generar($asignacion->grupo_id, $asignacion->id);

        $pdf = Pdf::loadView('pdf.analisis', [
            'analisis' => $analisis,
            'grupo' => $asignacion->grupo->nombre_grupo,
            'test' => $asignacion->test->localized_nombre_test,
        ])->setPaper('a4', 'landscape');

        $filename = 'analisis-' . str($asignacion->grupo->nombre_grupo)->slug() . '-' . $asignacion->id . '.pdf';

        return $pdf->download($filename);
    }

    public function exportarSociograma(Request $request, AsignacionTest $asignacion, AnalisisGrupalService $service)
    {
        abort_unless($asignacion->profesor_id === Auth::id(), 403);
        $filters = $request->validate([
            'alumnos' => ['sometimes', 'array'], 'alumnos.*' => ['integer'],
            'preguntas' => ['sometimes', 'array'], 'preguntas.*' => ['integer'],
            'mutuas' => ['sometimes', 'boolean'], 'marcar' => ['sometimes', 'boolean'],
        ]);
        $report = $service->generar($asignacion->grupo_id, $asignacion->id);
        $view = VistaSociograma::filtrar($report['sociograma'], $filters);

        return Pdf::loadView('pdf.vista-sociograma', $view + [
            'grupo' => $asignacion->grupo->nombre_grupo, 'test' => $asignacion->test->localized_nombre_test,
        ])->setPaper('a4', 'landscape')->download('sociograma-'.$asignacion->id.'.pdf');
    }
}
