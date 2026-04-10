<?php

namespace App\Http\Controllers;

use App\Models\AsignacionTest;
use App\Services\AnalisisGrupalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

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
            'test' => $asignacion->test->nombre_test,
        ])->setPaper('a4', 'portrait');

        $filename = 'analisis-' . str($asignacion->grupo->nombre_grupo)->slug() . '-' . $asignacion->id . '.pdf';

        return $pdf->download($filename);
    }
}
