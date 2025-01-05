<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Relacion;
use App\Models\AsignacionTest;

class AsignacionController extends Controller
{
    public function analisis($id)
    {
        // Obtener la asignación de test
        $asignacionTest = AsignacionTest::findOrFail($id);

        // Obtener las relaciones asociadas
        $relaciones = Relacion::where('asignacion_test_id', $id)
            ->with(['alumnoA', 'alumnoB'])
            ->get();

        // Procesar los datos para el sociograma
        $nodes = [];
        $links = [];
        $studentIds = [];

        foreach ($relaciones as $relacion) {
            // Agregar estudiantes al array de IDs únicos
            $studentIds[$relacion->alumnoA->id] = $relacion->alumnoA->nombre;
            $studentIds[$relacion->alumnoB->id] = $relacion->alumnoB->nombre;

            // Agregar enlace
            $links[] = [
                'source' => $relacion->alumnoA->id,
                'target' => $relacion->alumnoB->id,
                'tipo_relacion' => $relacion->tipo_relacion,
                'intensidad' => $relacion->intensidad,
            ];
        }

        // Crear nodos
        foreach ($studentIds as $id => $nombre) {
            $nodes[] = [
                'id' => $id,
                'label' => $nombre,
            ];
        }

        $graphData = [
            'nodes' => $nodes,
            'links' => $links,
        ];

        $jsonData = json_encode($graphData);

        // Retornar la vista con los datos
        return view('profesor.asignaciones.analisis', compact('asignacionTest', 'jsonData'));
    }
}
