<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GrupoController extends Controller
{
    // Listar los grupos del profesor
    public function index()
    {
        $grupos = Grupo::where('id_profesor', Auth::user()->id)->get();  // Solo los grupos del profesor autenticado
        return view('grupos.index', compact('grupos'));
    }

    // Mostrar el formulario para crear un grupo
    public function create()
    {
        $estudiantes = Auth::user()->grupos->flatMap->estudiantes;  // Obtener estudiantes de los grupos del profesor autenticado
        return view('grupos.create', compact('estudiantes'));
    }


    // Guardar un nuevo grupo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_grupo' => ['required', 'string', 'max:255'],
            'estudiantes' => ['sometimes', 'array', 'max:1000'],
            'estudiantes.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('estudiantes', 'id')->where(function ($query) {
                    $query->whereIn('id', function ($students) {
                        $students->select('id_estudiante')
                            ->from('estudiantes_grupos')
                            ->join('grupos', 'grupos.id', '=', 'estudiantes_grupos.id_grupo')
                            ->where('grupos.id_profesor', Auth::id());
                    });
                }),
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $grupo = Grupo::create([
                'nombre_grupo' => $validated['nombre_grupo'],
                'id_profesor' => Auth::user()->id,
            ]);

            // Asignar estudiantes al grupo
            $grupo->estudiantes()->attach($validated['estudiantes'] ?? []);
        });

        return redirect()->route('grupos.index');
    }
}
